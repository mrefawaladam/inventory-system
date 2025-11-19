<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Item extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'sku',
        'name',
        'barcode',
        'unit',
        'image',
        'minimum_stock',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'minimum_stock' => 'integer',
    ];

    /**
     * Get stocks for this item
     */
    public function stocks(): HasMany
    {
        return $this->hasMany(Stock::class);
    }

    /**
     * Get total stock across all locations
     */
    public function getTotalStockAttribute(): int
    {
        return $this->stocks()->sum('quantity');
    }

    /**
     * Get stock by warehouse
     */
    public function getStockByWarehouse()
    {
        return $this->stocks()
            ->join('locations', 'stocks.location_id', '=', 'locations.id')
            ->join('warehouses', 'locations.warehouse_id', '=', 'warehouses.id')
            ->selectRaw('warehouses.id, warehouses.name, SUM(stocks.quantity) as total_quantity')
            ->groupBy('warehouses.id', 'warehouses.name')
            ->get();
    }

    /**
     * Get stock by location
     */
    public function getStockByLocation()
    {
        return $this->stocks()
            ->with(['location.warehouse', 'location.parent'])
            ->get();
    }

    /**
     * Check if item is low stock
     */
    public function isLowStock(): bool
    {
        return $this->total_stock < $this->minimum_stock;
    }
}

