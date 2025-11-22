<?php

namespace App\Services;

use App\Enums\LocationType;
use App\Models\Location;
use Illuminate\Support\Facades\DB;

class LocationService
{
    /**
     * Generate location code automatically based on type and parent
     */
    public function generateCode(int $warehouseId, LocationType $type, ?int $parentId = null): string
    {
        if ($type === LocationType::ZONE) {
            // Zone code: Z01, Z02, etc.
            $lastZone = Location::where('warehouse_id', $warehouseId)
                ->where('type', LocationType::ZONE)
                ->orderBy('code', 'desc')
                ->first();
            
            if ($lastZone) {
                // Extract number from code (e.g., "Z01" -> 1)
                preg_match('/Z(\d+)/', $lastZone->code, $matches);
                $nextNumber = isset($matches[1]) ? (int)$matches[1] + 1 : 1;
            } else {
                $nextNumber = 1;
            }
            
            return 'Z' . str_pad($nextNumber, 2, '0', STR_PAD_LEFT);
        } elseif ($type === LocationType::RACK) {
            // Rack code: R01, R02, etc. (within a zone)
            if (!$parentId) {
                throw new \InvalidArgumentException('Rak harus memiliki Area sebagai parent. Silakan pilih Area terlebih dahulu sebelum membuat Rak.');
            }
            
            $parentZone = Location::findOrFail($parentId);
            $lastRack = Location::where('parent_id', $parentId)
                ->where('type', LocationType::RACK)
                ->orderBy('code', 'desc')
                ->first();
            
            if ($lastRack) {
                // Extract number from code (e.g., "R01" -> 1)
                preg_match('/R(\d+)/', $lastRack->code, $matches);
                $nextNumber = isset($matches[1]) ? (int)$matches[1] + 1 : 1;
            } else {
                $nextNumber = 1;
            }
            
            return 'R' . str_pad($nextNumber, 2, '0', STR_PAD_LEFT);
        } elseif ($type === LocationType::SLOT) {
            // Slot code: R01-S01, R01-S02, etc. (within a rack)
            if (!$parentId) {
                throw new \InvalidArgumentException('Tempat harus memiliki Rak sebagai parent. Silakan pilih Rak terlebih dahulu sebelum membuat Tempat.');
            }
            
            $parentRack = Location::findOrFail($parentId);
            if ($parentRack->type !== LocationType::RACK) {
                throw new \InvalidArgumentException('Parent untuk Tempat harus berupa Rak. Silakan pilih Rak yang valid.');
            }
            
            $lastSlot = Location::where('parent_id', $parentId)
                ->where('type', LocationType::SLOT)
                ->orderBy('code', 'desc')
                ->first();
            
            // Get rack code (e.g., "R01")
            $rackCode = $parentRack->code;
            
            if ($lastSlot) {
                // Extract slot number from code (e.g., "R01-S01" -> 1)
                preg_match('/-S(\d+)/', $lastSlot->code, $matches);
                $nextNumber = isset($matches[1]) ? (int)$matches[1] + 1 : 1;
            } else {
                $nextNumber = 1;
            }
            
            return $rackCode . '-S' . str_pad($nextNumber, 2, '0', STR_PAD_LEFT);
        }
        
        throw new \InvalidArgumentException('Tipe lokasi tidak valid. Pilih Area, Rak, atau Tempat.');
    }

    /**
     * Create a new location
     */
    public function create(array $data): Location
    {
        $type = LocationType::from($data['type']);
        
        // Generate code if not provided
        if (empty($data['code'])) {
            $data['code'] = $this->generateCode(
                $data['warehouse_id'],
                $type,
                $data['parent_id'] ?? null
            );
        }
        
        // Validate capacity
        $this->validateCapacity($data['capacity'] ?? 0);
        
        // Validate parent relationship
        if ($type === LocationType::RACK && empty($data['parent_id'])) {
            throw new \InvalidArgumentException('Rak harus memiliki Area sebagai parent. Silakan pilih Area di form sebelum menyimpan.');
        }
        
        if ($type === LocationType::SLOT && empty($data['parent_id'])) {
            throw new \InvalidArgumentException('Tempat harus memiliki Rak sebagai parent. Silakan pilih Rak di form sebelum menyimpan.');
        }
        
        if (!empty($data['parent_id'])) {
            $parent = Location::findOrFail($data['parent_id']);
            
            if ($type === LocationType::RACK && $parent->type !== LocationType::ZONE) {
                throw new \InvalidArgumentException('Parent untuk Rak harus berupa Area. Parent yang dipilih bukan Area.');
            }
            
            if ($type === LocationType::SLOT && $parent->type !== LocationType::RACK) {
                throw new \InvalidArgumentException('Parent untuk Tempat harus berupa Rak. Parent yang dipilih bukan Rak.');
            }
            
            // Ensure parent belongs to same warehouse
            if ($parent->warehouse_id != $data['warehouse_id']) {
                throw new \InvalidArgumentException('Parent lokasi harus berada di Sekolah yang sama. Silakan pilih parent dari Sekolah yang sama.');
            }
        }
        
        return Location::create([
            'warehouse_id' => $data['warehouse_id'],
            'parent_id' => $data['parent_id'] ?? null,
            'code' => $data['code'],
            'type' => $type,
            'capacity' => $data['capacity'] ?? 0,
            'description' => $data['description'] ?? null,
        ]);
    }

    /**
     * Update location
     */
    public function update(Location $location, array $data): Location
    {
        $type = $location->type;
        
        // If type is being changed, regenerate code
        if (isset($data['type']) && $data['type'] !== $location->type->value) {
            $type = LocationType::from($data['type']);
            $data['code'] = $this->generateCode(
                $data['warehouse_id'] ?? $location->warehouse_id,
                $type,
                $data['parent_id'] ?? $location->parent_id
            );
        }
        
        // Validate capacity
        if (isset($data['capacity'])) {
            $this->validateCapacity($data['capacity']);
        }
        
        // Validate parent relationship if changed
        if (isset($data['parent_id']) && $data['parent_id'] != $location->parent_id) {
            if ($type === LocationType::RACK && empty($data['parent_id'])) {
                throw new \InvalidArgumentException('Rak harus memiliki Area sebagai parent. Silakan pilih Area di form sebelum menyimpan.');
            }
            
            if ($type === LocationType::SLOT && empty($data['parent_id'])) {
                throw new \InvalidArgumentException('Tempat harus memiliki Rak sebagai parent. Silakan pilih Rak di form sebelum menyimpan.');
            }
            
            if (!empty($data['parent_id'])) {
                $parent = Location::findOrFail($data['parent_id']);
                
                if ($type === LocationType::RACK && $parent->type !== LocationType::ZONE) {
                    throw new \InvalidArgumentException('Parent untuk Rak harus berupa Area. Parent yang dipilih bukan Area.');
                }
                
                if ($type === LocationType::SLOT && $parent->type !== LocationType::RACK) {
                    throw new \InvalidArgumentException('Parent untuk Tempat harus berupa Rak. Parent yang dipilih bukan Rak.');
                }
                
                // Prevent circular reference
                if ($this->wouldCreateCircularReference($location, $data['parent_id'])) {
                    throw new \InvalidArgumentException('Tidak dapat mengatur parent: akan menciptakan referensi melingkar. Lokasi tidak dapat menjadi parent dari dirinya sendiri atau parent-nya.');
                }
            }
        }
        
        $location->update([
            'warehouse_id' => $data['warehouse_id'] ?? $location->warehouse_id,
            'parent_id' => $data['parent_id'] ?? $location->parent_id,
            'code' => $data['code'] ?? $location->code,
            'type' => $type,
            'capacity' => $data['capacity'] ?? $location->capacity,
            'description' => $data['description'] ?? $location->description,
        ]);
        
        return $location->fresh();
    }

    /**
     * Delete location
     */
    public function delete(Location $location): bool
    {
        // Check if location has children
        if ($location->children()->count() > 0) {
            throw new \InvalidArgumentException('Tidak dapat menghapus lokasi yang memiliki lokasi child. Hapus semua lokasi child terlebih dahulu.');
        }
        
        return $location->delete();
    }

    /**
     * Validate capacity
     */
    protected function validateCapacity(int $capacity): void
    {
        if ($capacity < 0) {
            throw new \InvalidArgumentException('Jumlah siswa penerima tidak boleh negatif. Silakan masukkan angka 0 atau lebih besar.');
        }
    }

    /**
     * Check if setting parent would create circular reference
     */
    protected function wouldCreateCircularReference(Location $location, int $parentId): bool
    {
        $parent = Location::findOrFail($parentId);
        
        // Check if the new parent is a descendant of this location
        $current = $parent;
        while ($current->parent_id) {
            if ($current->parent_id === $location->id) {
                return true;
            }
            $current = $current->parent;
        }
        
        return false;
    }

    /**
     * Get validation rules for create
     */
    public static function getCreateRules(): array
    {
        return [
            'warehouse_id' => 'required|exists:warehouses,id',
            'parent_id' => 'nullable|exists:locations,id',
            'code' => 'nullable|string|max:255',
            'type' => 'required|in:' . implode(',', LocationType::values()),
            'capacity' => 'required|integer|min:0',
            'description' => 'nullable|string',
        ];
    }

    /**
     * Get validation rules for update
     */
    public static function getUpdateRules(Location $location): array
    {
        return [
            'warehouse_id' => 'required|exists:warehouses,id',
            'parent_id' => 'nullable|exists:locations,id',
            'code' => 'nullable|string|max:255',
            'type' => 'required|in:' . implode(',', LocationType::values()),
            'capacity' => 'required|integer|min:0',
            'description' => 'nullable|string',
        ];
    }
}

