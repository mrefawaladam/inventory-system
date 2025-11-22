<?php

namespace App\Models;

use App\Enums\TransactionType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_code',
        'type',
        'item_id',
        'from_location_id',
        'to_location_id',
        'quantity',
        'batch',
        'user_id',
        'supplier_id',
        'customer_id',
        'notes',
    ];

    protected $casts = [
        'type' => TransactionType::class,
        'quantity' => 'integer',
    ];

    /**
     * Get the item that owns the transaction.
     */
    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    /**
     * Get the from location.
     */
    public function fromLocation(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'from_location_id');
    }

    /**
     * Get the to location.
     */
    public function toLocation(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'to_location_id');
    }

    /**
     * Get the user that created the transaction.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the supplier for this transaction.
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * Get the customer for this transaction.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Generate transaction code
     */
    public static function generateCode(TransactionType $type): string
    {
        $prefix = match($type) {
            TransactionType::INBOUND => 'IN',
            TransactionType::OUTBOUND => 'OUT',
            TransactionType::TRANSFER => 'TRF',
        };

        $date = now()->format('Ymd');
        $lastTransaction = self::where('type', $type->value)
            ->whereDate('created_at', today())
            ->orderBy('id', 'desc')
            ->first();

        if ($lastTransaction) {
            $lastNumber = (int) substr($lastTransaction->transaction_code, -4);
            $number = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $number = '0001';
        }

        return $prefix . '-' . $date . '-' . $number;
    }
}

