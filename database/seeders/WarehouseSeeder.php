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
                'name' => 'Gudang Pusat Jakarta',
                'address' => 'Jl. Raya Bekasi Km 25, Cakung, Jakarta Timur',
                'latitude' => -6.2088,
                'longitude' => 106.8456,
                'description' => 'Gudang pusat untuk distribusi sarana sekolah',
            ],
            [
                'name' => 'Gudang Bandung',
                'address' => 'Jl. Soekarno Hatta No. 500, Bandung',
                'latitude' => -6.9175,
                'longitude' => 107.6191,
                'description' => 'Gudang distribusi sarana sekolah wilayah Jawa Barat',
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

        $this->command->info('✓ Data gudang berhasil dimuat');
    }
}
