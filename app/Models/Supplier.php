<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supplier extends Model
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
     * Get inbound transactions for this supplier
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(\App\Models\Transaction::class, 'supplier_id');
    }

    /**
     * Get inbound transactions for this supplier
     */
    public function inbounds(): HasMany
    {
        return $this->hasMany(\App\Models\Transaction::class, 'supplier_id')
            ->where('type', \App\Enums\TransactionType::INBOUND);
    }
}

