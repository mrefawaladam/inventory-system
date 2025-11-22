<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Barang sarana sekolah yang representatif
        $items = [
            ['sku' => 'SR-001', 'name' => 'Foto Bingkai Presiden & Wapres', 'barcode' => '1234567890101', 'unit' => 'Unit', 'minimum_stock' => 20],
            ['sku' => 'SR-002', 'name' => 'Jam Dinding', 'barcode' => '1234567890102', 'unit' => 'Unit', 'minimum_stock' => 30],
            ['sku' => 'SR-003', 'name' => 'Lambang Garuda', 'barcode' => '1234567890103', 'unit' => 'Unit', 'minimum_stock' => 15],
            ['sku' => 'SR-004', 'name' => 'Teko Air Minum', 'barcode' => '1234567890104', 'unit' => 'Unit', 'minimum_stock' => 25],
            ['sku' => 'SR-005', 'name' => 'Tempat Sampah Ruangan', 'barcode' => '1234567890105', 'unit' => 'Unit', 'minimum_stock' => 40],
            ['sku' => 'SR-006', 'name' => 'Termos Nasi', 'barcode' => '1234567890106', 'unit' => 'Unit', 'minimum_stock' => 35],
            ['sku' => 'SR-007', 'name' => 'Tempat Hidangan Lauk', 'barcode' => '1234567890107', 'unit' => 'Unit', 'minimum_stock' => 30],
            ['sku' => 'SR-008', 'name' => 'Tempat Tisu', 'barcode' => '1234567890108', 'unit' => 'Unit', 'minimum_stock' => 50],
        ];

        foreach ($items as $item) {
            DB::table('items')->insert([
                'sku' => $item['sku'],
                'name' => $item['name'],
                'barcode' => $item['barcode'],
                'unit' => $item['unit'],
                'image' => null,
                'minimum_stock' => $item['minimum_stock'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->command->info('✓ Data barang berhasil dimuat');
    }
}
