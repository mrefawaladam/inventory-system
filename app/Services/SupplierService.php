<?php

namespace App\Services;

use App\Models\Supplier;
use Illuminate\Support\Facades\DB;

class SupplierService
{
    /**
     * Create a new supplier
     */
    public function create(array $data): Supplier
    {
        return Supplier::create([
            'name' => $data['name'],
            'phone' => $data['phone'] ?? null,
            'address' => $data['address'] ?? null,
        ]);
    }

    /**
     * Update supplier
     */
    public function update(Supplier $supplier, array $data): Supplier
    {
        $supplier->update([
            'name' => $data['name'],
            'phone' => $data['phone'] ?? null,
            'address' => $data['address'] ?? null,
        ]);

        return $supplier->fresh();
    }

    /**
     * Delete supplier
     */
    public function delete(Supplier $supplier): bool
    {
        // Check if supplier has inbound transactions
        if ($supplier->inbounds()->count() > 0) {
            throw new \InvalidArgumentException('Tidak dapat menghapus supplier yang sudah memiliki transaksi inbound. Silakan hapus atau ubah transaksi terkait terlebih dahulu.');
        }

        return $supplier->delete();
    }

    /**
     * Get validation rules for create
     */
    public static function getCreateRules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:255',
            'address' => 'nullable|string',
        ];
    }

    /**
     * Get validation rules for update
     */
    public static function getUpdateRules(Supplier $supplier): array
    {
        return [
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:255',
            'address' => 'nullable|string',
        ];
    }
}

