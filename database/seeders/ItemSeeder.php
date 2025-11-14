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
        $items = [
            ['sku' => 'PRD-001', 'name' => 'Laptop ASUS ROG', 'barcode' => '1234567890123', 'unit' => 'Unit', 'minimum_stock' => 10],
            ['sku' => 'PRD-002', 'name' => 'Mouse Logitech MX Master', 'barcode' => '1234567890124', 'unit' => 'Unit', 'minimum_stock' => 50],
            ['sku' => 'PRD-003', 'name' => 'Keyboard Mechanical RGB', 'barcode' => '1234567890125', 'unit' => 'Unit', 'minimum_stock' => 30],
            ['sku' => 'PRD-004', 'name' => 'Monitor LG 27 inch', 'barcode' => '1234567890126', 'unit' => 'Unit', 'minimum_stock' => 15],
            ['sku' => 'PRD-005', 'name' => 'Webcam Logitech C920', 'barcode' => '1234567890127', 'unit' => 'Unit', 'minimum_stock' => 25],
            ['sku' => 'PRD-006', 'name' => 'Headset Gaming RGB', 'barcode' => '1234567890128', 'unit' => 'Unit', 'minimum_stock' => 40],
            ['sku' => 'PRD-007', 'name' => 'SSD Samsung 1TB', 'barcode' => '1234567890129', 'unit' => 'Unit', 'minimum_stock' => 20],
            ['sku' => 'PRD-008', 'name' => 'RAM DDR4 16GB', 'barcode' => '1234567890130', 'unit' => 'Unit', 'minimum_stock' => 35],
            ['sku' => 'PRD-009', 'name' => 'Power Supply 750W', 'barcode' => '1234567890131', 'unit' => 'Unit', 'minimum_stock' => 12],
            ['sku' => 'PRD-010', 'name' => 'Motherboard ASUS B550', 'barcode' => '1234567890132', 'unit' => 'Unit', 'minimum_stock' => 8],
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

        // Generate additional random items
        for ($i = 11; $i <= 30; $i++) {
            $productName = fake()->words(3, true);
            DB::table('items')->insert([
                'sku' => 'PRD-' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'name' => ucwords($productName),
                'barcode' => fake()->ean13(),
                'unit' => fake()->randomElement(['Unit', 'Box', 'Pack', 'Set', 'Pcs']),
                'image' => null,
                'minimum_stock' => fake()->numberBetween(5, 50),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->command->info('Items seeded successfully!');
    }
}
