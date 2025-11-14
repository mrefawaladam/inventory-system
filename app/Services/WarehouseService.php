<?php

namespace App\Services;

use App\Models\Warehouse;
use Illuminate\Support\Facades\DB;

class WarehouseService
{
    /**
     * Create a new warehouse
     */
    public function create(array $data): Warehouse
    {
        return Warehouse::create([
            'name' => $data['name'],
            'address' => $data['address'] ?? null,
            'latitude' => $data['latitude'] ?? null,
            'longitude' => $data['longitude'] ?? null,
            'description' => $data['description'] ?? null,
        ]);
    }

    /**
     * Update warehouse
     */
    public function update(Warehouse $warehouse, array $data): Warehouse
    {
        $warehouse->update([
            'name' => $data['name'],
            'address' => $data['address'] ?? null,
            'latitude' => $data['latitude'] ?? null,
            'longitude' => $data['longitude'] ?? null,
            'description' => $data['description'] ?? null,
        ]);

        return $warehouse->fresh();
    }

    /**
     * Delete warehouse
     */
    public function delete(Warehouse $warehouse): bool
    {
        return $warehouse->delete();
    }

    /**
     * Get validation rules for create
     */
    public static function getCreateRules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'address' => 'nullable|string',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'description' => 'nullable|string',
        ];
    }

    /**
     * Get validation rules for update
     */
    public static function getUpdateRules(Warehouse $warehouse): array
    {
        return [
            'name' => 'required|string|max:255',
            'address' => 'nullable|string',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'description' => 'nullable|string',
        ];
    }
}

