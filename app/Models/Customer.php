<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'phone',
        'address',
    ];

    /**
     * Get outbound transactions for this customer
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(\App\Models\Transaction::class, 'customer_id');
    }

    /**
     * Get outbound transactions for this customer
     */
    public function outbounds(): HasMany
    {
        return $this->hasMany(\App\Models\Transaction::class, 'customer_id')
            ->where('type', \App\Enums\TransactionType::OUTBOUND);
    }
}

