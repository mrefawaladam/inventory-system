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
        $suppliers = [
            ['name' => 'PT. Supplier Utama', 'phone' => '081234567890', 'address' => 'Jl. Raya Industri No. 123, Jakarta'],
            ['name' => 'CV. Distributor Sejahtera', 'phone' => '081234567891', 'address' => 'Jl. Gatot Subroto No. 456, Bandung'],
            ['name' => 'PT. Pemasok Terpercaya', 'phone' => '081234567892', 'address' => 'Jl. Sudirman No. 789, Surabaya'],
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

        // Generate additional random suppliers
        for ($i = 0; $i < 7; $i++) {
            DB::table('suppliers')->insert([
                'name' => fake()->company(),
                'phone' => fake()->phoneNumber(),
                'address' => fake()->address(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->command->info('Suppliers seeded successfully!');
    }
}
