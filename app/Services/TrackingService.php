<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\Warehouse;
use App\Models\Location;
use App\Models\Item;
use App\Enums\TransactionType;
use Illuminate\Support\Facades\DB;

class TrackingService
{
    /**
     * Get transactions with route data for map visualization
     */
    public function getTransactionsWithRoutes(array $filters = []): array
    {
        $query = Transaction::with([
            'item',
            'fromLocation.warehouse',
            'toLocation.warehouse',
            'user'
        ]);

        // Apply filters
        if (isset($filters['type']) && $filters['type']) {
            $query->where('type', $filters['type']);
        }

        if (isset($filters['item_id']) && $filters['item_id']) {
            $query->where('item_id', $filters['item_id']);
        }

        if (isset($filters['warehouse_id']) && $filters['warehouse_id']) {
            $query->where(function($q) use ($filters) {
                $q->whereHas('fromLocation', function($q) use ($filters) {
                    $q->where('warehouse_id', $filters['warehouse_id']);
                })->orWhereHas('toLocation', function($q) use ($filters) {
                    $q->where('warehouse_id', $filters['warehouse_id']);
                });
            });
        }

        if (isset($filters['date_from']) && $filters['date_from']) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to']) && $filters['date_to']) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        $transactions = $query->orderBy('created_at', 'desc')
            ->limit(100) // Limit untuk performa
            ->get();

        $routes = [];
        foreach ($transactions as $transaction) {
            $routeData = $this->prepareRouteData($transaction);
            if ($routeData) {
                $routes[] = $routeData;
            }
        }

        return $routes;
    }

    /**
     * Prepare route data for a single transaction
     */
    public function prepareRouteData(Transaction $transaction): ?array
    {
        $fromWarehouse = null;
        $toWarehouse = null;
        $fromLocation = null;
        $toLocation = null;

        // Get warehouse coordinates from location
        if ($transaction->fromLocation) {
            $fromLocation = $transaction->fromLocation;
            $fromWarehouse = $fromLocation->warehouse;
        }

        if ($transaction->toLocation) {
            $toLocation = $transaction->toLocation;
            $toWarehouse = $toLocation->warehouse;
        }

        // Skip if no valid coordinates
        if (!$fromWarehouse && !$toWarehouse) {
            return null;
        }

        // Determine coordinates
        $fromLat = $fromWarehouse?->latitude;
        $fromLng = $fromWarehouse?->longitude;
        $toLat = $toWarehouse?->latitude;
        $toLng = $toWarehouse?->longitude;

        $deliveryStatus = match ($transaction->type) {
            TransactionType::OUTBOUND => 'pending',
            default => 'delivered',
        };

        // For INBOUND: only to_location
        // For OUTBOUND: only from_location
        // For TRANSFER: both locations
        if ($transaction->type === TransactionType::INBOUND) {
            if (!$toLat || !$toLng) {
                return null;
            }
            // Use warehouse center as origin for inbound
            $fromLat = $toLat;
            $fromLng = $toLng;
        } elseif ($transaction->type === TransactionType::OUTBOUND) {
            if (!$fromLat || !$fromLng) {
                return null;
            }
            // Use warehouse center as destination for outbound
            $toLat = $fromLat;
            $toLng = $fromLng;
        } else { // TRANSFER
            if (!$fromLat || !$fromLng || !$toLat || !$toLng) {
                return null;
            }
        }

        // Calculate distance
        $distance = $this->calculateDistance($fromLat, $fromLng, $toLat, $toLng);

        return [
            'id' => $transaction->id,
            'transaction_code' => $transaction->transaction_code,
            'type' => $transaction->type->value,
            'type_label' => $transaction->type->label(),
            'item' => [
                'id' => $transaction->item->id,
                'name' => $transaction->item->name,
                'sku' => $transaction->item->sku,
            ],
            'quantity' => $transaction->quantity,
            'batch' => $transaction->batch,
            'user' => [
                'id' => $transaction->user->id ?? null,
                'name' => $transaction->user->name ?? 'N/A',
            ],
            'created_at' => $transaction->created_at->format('Y-m-d H:i:s'),
            'created_at_formatted' => $transaction->created_at->format('d M Y H:i'),
            'notes' => $transaction->notes,
            'delivery_status' => $deliveryStatus,
            'delivery_status_label' => $deliveryStatus === 'delivered' ? 'Sudah Dikirim' : 'Belum Dikirim',
            'from' => [
                'warehouse_id' => $fromWarehouse?->id,
                'warehouse_name' => $fromWarehouse?->name,
                'location_id' => $fromLocation?->id,
                'location_code' => $fromLocation?->code,
                'location_path' => $fromLocation?->full_path,
                'latitude' => (float) $fromLat,
                'longitude' => (float) $fromLng,
            ],
            'to' => [
                'warehouse_id' => $toWarehouse?->id,
                'warehouse_name' => $toWarehouse?->name,
                'location_id' => $toLocation?->id,
                'location_code' => $toLocation?->code,
                'location_path' => $toLocation?->full_path,
                'latitude' => (float) $toLat,
                'longitude' => (float) $toLng,
            ],
            'distance_km' => round($distance, 2),
        ];
    }

    /**
     * Calculate distance between two coordinates (Haversine formula)
     */
    public function calculateDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371; // Radius in kilometers

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    /**
     * Get all warehouses with coordinates for map
     */
    public function getWarehousesForMap(): array
    {
        $warehouses = Warehouse::whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get();

        return $warehouses->map(function($warehouse) {
            return [
                'id' => $warehouse->id,
                'name' => $warehouse->name,
                'address' => $warehouse->address,
                'latitude' => (float) $warehouse->latitude,
                'longitude' => (float) $warehouse->longitude,
            ];
        })->toArray();
    }

    /**
     * Get item movement history with timeline
     */
    public function getItemMovementHistory(int $itemId, array $filters = []): array
    {
        $query = Transaction::with([
            'item',
            'fromLocation.warehouse',
            'toLocation.warehouse',
            'user'
        ])->where('item_id', $itemId);

        // Apply filters
        if (isset($filters['type']) && $filters['type']) {
            $query->where('type', $filters['type']);
        }

        if (isset($filters['warehouse_id']) && $filters['warehouse_id']) {
            $query->where(function($q) use ($filters) {
                $q->whereHas('fromLocation', function($q) use ($filters) {
                    $q->where('warehouse_id', $filters['warehouse_id']);
                })->orWhereHas('toLocation', function($q) use ($filters) {
                    $q->where('warehouse_id', $filters['warehouse_id']);
                });
            });
        }

        if (isset($filters['date_from']) && $filters['date_from']) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to']) && $filters['date_to']) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        $transactions = $query->orderBy('created_at', 'asc')->get();

        $history = [];
        $sequence = 0;

        foreach ($transactions as $transaction) {
            $routeData = $this->prepareRouteData($transaction);
            if ($routeData) {
                $routeData['sequence'] = ++$sequence;
                $routeData['timestamp'] = $transaction->created_at->timestamp;
                $history[] = $routeData;
            }
        }

        return $history;
    }

    /**
     * Get item details for history view
     */
    public function getItemDetails(int $itemId): ?array
    {
        $item = Item::with('stocks.location.warehouse')->find($itemId);

        if (!$item) {
            return null;
        }

        $totalStock = $item->stocks->sum('quantity');
        $locations = $item->stocks->groupBy('location_id')->map(function($stocks, $locationId) {
            $location = $stocks->first()->location;
            return [
                'location_id' => $location->id,
                'location_code' => $location->code,
                'location_path' => $location->full_path,
                'warehouse_name' => $location->warehouse->name ?? 'N/A',
                'quantity' => $stocks->sum('quantity'),
            ];
        })->values();

        return [
            'id' => $item->id,
            'name' => $item->name,
            'sku' => $item->sku,
            'barcode' => $item->barcode,
            'unit' => $item->unit,
            'total_stock' => $totalStock,
            'minimum_stock' => $item->minimum_stock,
            'is_low_stock' => $item->isLowStock(),
            'current_locations' => $locations,
        ];
    }
}

