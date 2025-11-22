<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SupplierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Supplier untuk sarana sekolah
        $suppliers = [
            ['name' => 'PT. Supplier Sarana Sekolah Indonesia', 'phone' => '081234567890', 'address' => 'Jl. Raya Industri No. 123, Jakarta'],
            ['name' => 'CV. Distributor Alat Pendidikan', 'phone' => '081234567891', 'address' => 'Jl. Gatot Subroto No. 456, Bandung'],
            ['name' => 'PT. Pemasok Peralatan Sekolah', 'phone' => '081234567892', 'address' => 'Jl. Sudirman No. 789, Surabaya'],
            ['name' => 'Kemensos - Kementerian Sosial', 'phone' => '081234567893', 'address' => 'Jl. Salemba Raya No. 28, Jakarta Pusat'],
        ];

        foreach ($suppliers as $supplier) {
            DB::table('suppliers')->insert([
                'name' => $supplier['name'],
                'phone' => $supplier['phone'],
                'address' => $supplier['address'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Generate additional suppliers untuk sarana sekolah
        $additionalSuppliers = [
            'PT. Produsen Perabot Sekolah',
            'CV. Distributor Sarana Pendidikan',
            'PT. Pemasok Alat Tulis Sekolah',
            'CV. Supplier Peralatan Kelas',
            'PT. Distributor Furniture Sekolah',
        ];

        foreach ($additionalSuppliers as $supplierName) {
            DB::table('suppliers')->insert([
                'name' => $supplierName,
                'phone' => fake()->phoneNumber(),
                'address' => fake()->address(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->command->info('Suppliers untuk sarana sekolah seeded successfully!');
    }
}
