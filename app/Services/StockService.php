<?php

namespace App\Services;

use App\Models\Item;
use App\Models\Location;
use App\Models\Stock;
use Illuminate\Support\Facades\DB;

class StockService
{
    /**
     * Increase stock at a location
     *
     * @param int $itemId
     * @param int $locationId
     * @param int $quantity
     * @param string|null $batch
     * @param string|null $expiredAt
     * @return Stock
     * @throws \Exception
     */
    public function increaseStock(
        int $itemId,
        int $locationId,
        int $quantity,
        ?string $batch = null,
        ?string $expiredAt = null
    ): Stock {
        if ($quantity <= 0) {
            throw new \InvalidArgumentException('Quantity harus lebih besar dari 0');
        }

        $item = Item::findOrFail($itemId);
        $location = Location::findOrFail($locationId);

        // Check location capacity
        $this->validateLocationCapacity($location, $quantity);

        // Check if stock with same batch already exists
        $existingStock = Stock::where('item_id', $itemId)
            ->where('location_id', $locationId)
            ->where('batch', $batch)
            ->first();

        if ($existingStock) {
            // Update existing stock
            $existingStock->quantity += $quantity;

            // Update expired_at if provided and earlier than current
            if ($expiredAt && (!$existingStock->expired_at || $expiredAt < $existingStock->expired_at)) {
                $existingStock->expired_at = $expiredAt;
            }

            $existingStock->save();
            return $existingStock->fresh();
        }

        // Create new stock record
        return Stock::create([
            'item_id' => $itemId,
            'location_id' => $locationId,
            'quantity' => $quantity,
            'batch' => $batch,
            'expired_at' => $expiredAt ? \Carbon\Carbon::parse($expiredAt) : null,
        ]);
    }

    /**
     * Decrease stock at a location (FIFO/FEFO)
     *
     * @param int $itemId
     * @param int $locationId
     * @param int $quantity
     * @return array Array of affected stocks
     * @throws \Exception
     */
    public function decreaseStock(int $itemId, int $locationId, int $quantity): array
    {
        if ($quantity <= 0) {
            throw new \InvalidArgumentException('Quantity must be greater than 0');
        }

        $item = Item::findOrFail($itemId);
        $location = Location::findOrFail($locationId);

        // Get total available stock
        $totalStock = Stock::where('item_id', $itemId)
            ->where('location_id', $locationId)
            ->sum('quantity');

        if ($totalStock < $quantity) {
            throw new \Exception("Stok tidak mencukupi. Tersedia: {$totalStock}, Diminta: {$quantity}");
        }

        // Get stocks ordered by FEFO (First Expired First Out)
        // If no expiration date, use FIFO (First In First Out)
        $stocks = Stock::where('item_id', $itemId)
            ->where('location_id', $locationId)
            ->where('quantity', '>', 0)
            ->orderByRaw('COALESCE(expired_at, "9999-12-31") ASC')
            ->orderBy('created_at', 'ASC')
            ->get();

        $remainingQuantity = $quantity;
        $affectedStocks = [];

        foreach ($stocks as $stock) {
            if ($remainingQuantity <= 0) {
                break;
            }

            $deductQuantity = min($stock->quantity, $remainingQuantity);
            $stockId = $stock->id;
            $batch = $stock->batch;
            $expiredAt = $stock->expired_at ? $stock->expired_at->format('Y-m-d') : null;

            $stock->quantity -= $deductQuantity;
            $remainingQuantity -= $deductQuantity;

            if ($stock->quantity > 0) {
                $stock->save();
            } else {
                $stock->delete();
            }

            $affectedStocks[] = [
                'stock_id' => $stockId,
                'batch' => $batch,
                'expired_at' => $expiredAt,
                'deducted_quantity' => $deductQuantity,
            ];
        }

        return $affectedStocks;
    }

    /**
     * Transfer stock from one location to another
     *
     * @param int $itemId
     * @param int $fromLocationId
     * @param int $toLocationId
     * @param int $quantity
     * @param string|null $batch
     * @return array
     * @throws \Exception
     */
    public function transferStock(
        int $itemId,
        int $fromLocationId,
        int $toLocationId,
        int $quantity,
        ?string $batch = null
    ): array {
        if ($quantity <= 0) {
            throw new \InvalidArgumentException('Quantity harus lebih besar dari 0');
        }

        if ($fromLocationId === $toLocationId) {
            throw new \InvalidArgumentException('Lokasi sumber dan tujuan tidak boleh sama');
        }

        $item = Item::findOrFail($itemId);
        $fromLocation = Location::findOrFail($fromLocationId);
        $toLocation = Location::findOrFail($toLocationId);

        // Check if source has enough stock
        $totalStock = Stock::where('item_id', $itemId)
            ->where('location_id', $fromLocationId)
            ->sum('quantity');

        if ($totalStock < $quantity) {
            throw new \Exception("Stok tidak mencukupi di lokasi sumber. Tersedia: {$totalStock}, Diminta: {$quantity}");
        }

        // Check destination capacity
        $this->validateLocationCapacity($toLocation, $quantity);

        // Use database transaction
        return DB::transaction(function () use ($itemId, $fromLocationId, $toLocationId, $quantity, $batch) {
            // Decrease from source (FEFO)
            $decreasedStocks = $this->decreaseStock($itemId, $fromLocationId, $quantity);

            // Get batch and expired_at from first decreased stock if not provided
            if (!$batch && !empty($decreasedStocks)) {
                $batch = $decreasedStocks[0]['batch'];
            }

            $expiredAt = null;
            if (!empty($decreasedStocks)) {
                $expiredAt = $decreasedStocks[0]['expired_at'];
            }

            // Increase to destination
            $newStock = $this->increaseStock($itemId, $toLocationId, $quantity, $batch, $expiredAt);

            return [
                'from_location' => $fromLocationId,
                'to_location' => $toLocationId,
                'quantity' => $quantity,
                'decreased_stocks' => $decreasedStocks,
                'new_stock' => $newStock,
            ];
        });
    }

    /**
     * Validate location capacity
     *
     * @param Location $location
     * @param int $additionalQuantity
     * @throws \Exception
     */
    protected function validateLocationCapacity(Location $location, int $additionalQuantity): void
    {
        // Get current total quantity at this location
        $currentQuantity = Stock::where('location_id', $location->id)
            ->sum('quantity');

        $newTotalQuantity = $currentQuantity + $additionalQuantity;

        // Check if location capacity is exceeded
        if ($location->capacity > 0 && $newTotalQuantity > $location->capacity) {
            $available = $location->capacity - $currentQuantity;
            throw new \Exception(
                "Kapasitas lokasi terlampaui. Kapasitas: {$location->capacity}, " .
                "Saat ini: {$currentQuantity}, Tersedia: {$available}, " .
                "Diminta: {$additionalQuantity}"
            );
        }
    }

    /**
     * Get stock summary for an item at a location
     *
     * @param int $itemId
     * @param int $locationId
     * @return array
     */
    public function getStockSummary(int $itemId, int $locationId): array
    {
        $stocks = Stock::where('item_id', $itemId)
            ->where('location_id', $locationId)
            ->get();

        $totalQuantity = $stocks->sum('quantity');
        $batches = $stocks->count();
        $expiredItems = $stocks->filter(function ($stock) {
            return $stock->expired_at && $stock->expired_at->isPast();
        })->sum('quantity');

        $expiringSoon = $stocks->filter(function ($stock) {
            return $stock->expired_at &&
                   $stock->expired_at->isFuture() &&
                   $stock->expired_at->diffInDays(now()) <= 30;
        })->sum('quantity');

        return [
            'total_quantity' => $totalQuantity,
            'batches' => $batches,
            'expired_items' => $expiredItems,
            'expiring_soon' => $expiringSoon,
            'stocks' => $stocks,
        ];
    }

    /**
     * Get validation rules for stock operations
     */
    public static function getValidationRules(): array
    {
        return [
            'item_id' => 'required|exists:items,id',
            'location_id' => 'required|exists:locations,id',
            'quantity' => 'required|integer|min:1',
            'batch' => 'nullable|string|max:255',
            'expired_at' => 'nullable|date|after_or_equal:today',
        ];
    }

    /**
     * Get validation rules for transfer
     */
    public static function getTransferValidationRules(): array
    {
        return [
            'item_id' => 'required|exists:items,id',
            'from_location_id' => 'required|exists:locations,id',
            'to_location_id' => 'required|exists:locations,id|different:from_location_id',
            'quantity' => 'required|integer|min:1',
        ];
    }
}

