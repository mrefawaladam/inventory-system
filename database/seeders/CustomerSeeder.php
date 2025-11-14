<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $customers = [
            ['name' => 'PT. Toko Ritel Indonesia', 'phone' => '082134567890', 'address' => 'Jl. Thamrin No. 100, Jakarta'],
            ['name' => 'CV. Grosir Makmur', 'phone' => '082134567891', 'address' => 'Jl. Asia Afrika No. 200, Bandung'],
            ['name' => 'PT. Distributor Nusantara', 'phone' => '082134567892', 'address' => 'Jl. Pemuda No. 300, Surabaya'],
        ];

        foreach ($customers as $customer) {
            DB::table('customers')->insert([
                'name' => $customer['name'],
                'phone' => $customer['phone'],
                'address' => $customer['address'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Generate additional random customers
        for ($i = 0; $i < 7; $i++) {
            DB::table('customers')->insert([
                'name' => fake()->company(),
                'phone' => fake()->phoneNumber(),
                'address' => fake()->address(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->command->info('Customers seeded successfully!');
    }
}
