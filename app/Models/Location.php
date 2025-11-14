<?php

namespace App\Models;

use App\Enums\LocationType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Location extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'warehouse_id',
        'parent_id',
        'code',
        'type',
        'capacity',
        'description',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'type' => LocationType::class,
        'capacity' => 'integer',
    ];

    /**
     * Get the warehouse that owns the location.
     */
    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    /**
     * Get the parent location (for hierarchical structure).
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'parent_id');
    }

    /**
     * Get child locations.
     */
    public function children(): HasMany
    {
        return $this->hasMany(Location::class, 'parent_id');
    }

    /**
     * Get all zones for a warehouse.
     */
    public static function getZonesForWarehouse(int $warehouseId)
    {
        return self::where('warehouse_id', $warehouseId)
            ->where('type', LocationType::ZONE)
            ->get();
    }

    /**
     * Get all racks for a zone.
     */
    public static function getRacksForZone(int $zoneId)
    {
        return self::where('parent_id', $zoneId)
            ->where('type', LocationType::RACK)
            ->get();
    }

    /**
     * Get all slots for a rack.
     */
    public static function getSlotsForRack(int $rackId)
    {
        return self::where('parent_id', $rackId)
            ->where('type', LocationType::SLOT)
            ->get();
    }

    /**
     * Get the full path of the location (e.g., "Zone 01 > Rack 01 > Slot 01").
     */
    public function getFullPathAttribute(): string
    {
        $path = [$this->code];
        $parent = $this->parent;
        
        while ($parent) {
            array_unshift($path, $parent->code);
            $parent = $parent->parent;
        }
        
        return implode(' > ', $path);
    }

    /**
     * Check if location has available capacity.
     */
    public function hasAvailableCapacity(int $requiredCapacity = 1): bool
    {
        // For now, we'll check if capacity is greater than 0
        // Later this can be enhanced to check actual usage
        return $this->capacity >= $requiredCapacity;
    }
}

