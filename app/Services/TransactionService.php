<?php

namespace App\Services;

use App\Enums\TransactionType;
use App\Models\Transaction;
use App\Services\StockService;
use Illuminate\Support\Facades\DB;

class TransactionService
{
    protected $stockService;

    public function __construct(StockService $stockService)
    {
        $this->stockService = $stockService;
    }

    /**
     * Create inbound transaction
     */
    public function createInbound(
        int $itemId,
        int $toLocationId,
        int $quantity,
        ?string $batch = null,
        ?string $expiredAt = null,
        ?string $notes = null
    ): Transaction {
        return DB::transaction(function () use ($itemId, $toLocationId, $quantity, $batch, $expiredAt, $notes) {
            // Increase stock
            $this->stockService->increaseStock($itemId, $toLocationId, $quantity, $batch, $expiredAt);

            // Create transaction log
            return Transaction::create([
                'transaction_code' => Transaction::generateCode(TransactionType::INBOUND),
                'type' => TransactionType::INBOUND,
                'item_id' => $itemId,
                'from_location_id' => null,
                'to_location_id' => $toLocationId,
                'quantity' => $quantity,
                'batch' => $batch,
                'user_id' => auth()->id(),
                'notes' => $notes,
            ]);
        });
    }

    /**
     * Create outbound transaction
     */
    public function createOutbound(
        int $itemId,
        int $fromLocationId,
        int $quantity,
        ?string $notes = null
    ): Transaction {
        return DB::transaction(function () use ($itemId, $fromLocationId, $quantity, $notes) {
            // Decrease stock
            $this->stockService->decreaseStock($itemId, $fromLocationId, $quantity);

            // Create transaction log
            return Transaction::create([
                'transaction_code' => Transaction::generateCode(TransactionType::OUTBOUND),
                'type' => TransactionType::OUTBOUND,
                'item_id' => $itemId,
                'from_location_id' => $fromLocationId,
                'to_location_id' => null,
                'quantity' => $quantity,
                'batch' => null,
                'user_id' => auth()->id(),
                'notes' => $notes,
            ]);
        });
    }

    /**
     * Create transfer transaction
     */
    public function createTransfer(
        int $itemId,
        int $fromLocationId,
        int $toLocationId,
        int $quantity,
        ?string $notes = null
    ): Transaction {
        return DB::transaction(function () use ($itemId, $fromLocationId, $toLocationId, $quantity, $notes) {
            // Transfer stock
            $this->stockService->transferStock($itemId, $fromLocationId, $toLocationId, $quantity);

            // Create transaction log
            return Transaction::create([
                'transaction_code' => Transaction::generateCode(TransactionType::TRANSFER),
                'type' => TransactionType::TRANSFER,
                'item_id' => $itemId,
                'from_location_id' => $fromLocationId,
                'to_location_id' => $toLocationId,
                'quantity' => $quantity,
                'batch' => null,
                'user_id' => auth()->id(),
                'notes' => $notes,
            ]);
        });
    }
}

