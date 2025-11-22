<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WarehouseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $warehouses = [
            [
                'name' => 'Sekolah Pusat Jakarta',
                'address' => 'Jl. Raya Bekasi Km 25, Cakung, Jakarta Timur',
                'latitude' => -6.2088,
                'longitude' => 106.8456,
                'description' => 'Gudang pusat untuk distribusi sarana sekolah wilayah Jakarta dan sekitarnya',
            ],
            [
                'name' => 'Sekolah Bandung',
                'address' => 'Jl. Soekarno Hatta No. 500, Bandung',
                'latitude' => -6.9175,
                'longitude' => 107.6191,
                'description' => 'Gudang untuk distribusi sarana sekolah wilayah Jawa Barat',
            ],
            [
                'name' => 'Sekolah Surabaya',
                'address' => 'Jl. Raya Gresik Km 10, Surabaya',
                'latitude' => -7.2575,
                'longitude' => 112.7521,
                'description' => 'Gudang untuk distribusi sarana sekolah wilayah Jawa Timur',
            ],
        ];

        foreach ($warehouses as $warehouse) {
            DB::table('warehouses')->insert([
                'name' => $warehouse['name'],
                'address' => $warehouse['address'],
                'latitude' => $warehouse['latitude'],
                'longitude' => $warehouse['longitude'],
                'description' => $warehouse['description'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Generate additional random warehouses untuk sarana sekolah
        for ($i = 0; $i < 2; $i++) {
            DB::table('warehouses')->insert([
                'name' => 'Sekolah ' . fake()->city(),
                'address' => fake()->address(),
                'latitude' => fake()->latitude(-6.5, -7.5),
                'longitude' => fake()->longitude(106.0, 112.0),
                'description' => 'Gudang untuk distribusi sarana sekolah',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->command->info('Gudang sekolah seeded successfully!');
    }
}
