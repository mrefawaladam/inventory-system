<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Warehouse extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'address',
        'latitude',
        'longitude',
        'description',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
    ];

    /**
     * Get locations for this warehouse
     */
    public function locations()
    {
        return $this->hasMany(Location::class);
    }

    /**
     * Get zones for this warehouse
     */
    public function zones()
    {
        return $this->hasMany(Location::class)->where('type', \App\Enums\LocationType::ZONE);
    }
}
