<?php

namespace App\Services;

use App\Models\Customer;
use Illuminate\Support\Facades\DB;

class CustomerService
{
    /**
     * Create a new customer
     */
    public function create(array $data): Customer
    {
        return Customer::create([
            'name' => $data['name'],
            'phone' => $data['phone'] ?? null,
            'address' => $data['address'] ?? null,
        ]);
    }

    /**
     * Update customer
     */
    public function update(Customer $customer, array $data): Customer
    {
        $customer->update([
            'name' => $data['name'],
            'phone' => $data['phone'] ?? null,
            'address' => $data['address'] ?? null,
        ]);

        return $customer->fresh();
    }

    /**
     * Delete customer
     */
    public function delete(Customer $customer): bool
    {
        // Check if customer has outbound transactions
        if ($customer->outbounds()->count() > 0) {
            throw new \InvalidArgumentException('Tidak dapat menghapus customer yang sudah memiliki transaksi outbound. Silakan hapus atau ubah transaksi terkait terlebih dahulu.');
        }

        return $customer->delete();
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
    public static function getUpdateRules(Customer $customer): array
    {
        return [
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:255',
            'address' => 'nullable|string',
        ];
    }
}

