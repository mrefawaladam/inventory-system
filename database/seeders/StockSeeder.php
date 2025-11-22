<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StockSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $items = DB::table('items')->get();
        $slots = DB::table('locations')
            ->where('type', 'SLOT')
            ->get();

        if ($items->isEmpty() || $slots->isEmpty()) {
            $this->command->warn('Items or Slots not found. Please run ItemSeeder and LocationSeeder first.');
            return;
        }

        // Mapping item ke zona yang sesuai untuk demo yang mudah dipahami
        $itemZoneMapping = [
            'Foto Bingkai Presiden & Wapres' => 'C',
            'Jam Dinding' => 'C',
            'Lambang Garuda' => 'C',
            'Teko Air Minum' => 'B',
            'Tempat Sampah Ruangan' => 'A',
            'Termos Nasi' => 'B',
            'Tempat Hidangan Lauk' => 'B',
            'Tempat Tisu' => 'A',
        ];

        foreach ($items as $item) {
            // Tentukan zona berdasarkan mapping atau random
            $preferredZone = $itemZoneMapping[$item->name] ?? null;
            
            // Pilih slot dari zona yang sesuai jika ada
            $availableSlots = $preferredZone 
                ? $slots->filter(function($slot) use ($preferredZone) {
                    return strpos($slot->code, 'ZONE-' . $preferredZone) !== false;
                })
                : $slots;

            if ($availableSlots->isEmpty()) {
                $availableSlots = $slots;
            }

            // Assign stock ke 1-2 slot per item untuk demo yang jelas
            $numSlots = min(2, $availableSlots->count());
            $selectedSlots = $availableSlots->random($numSlots);

            foreach ($selectedSlots as $slot) {
                // Quantity yang realistis untuk sarana sekolah
                $quantity = fake()->numberBetween(10, 50);
                
                // Batch dengan format yang jelas: SR-YYYYMMDD-XXX
                $batchDate = fake()->dateTimeBetween('-6 months', 'now')->format('Ymd');
                $batch = 'SR-' . $batchDate . '-' . strtoupper(fake()->lexify('???'));

                // Hanya beberapa item yang punya expired date (seperti makanan/peralatan tertentu)
                $expiredAt = null;
                if (in_array($item->name, ['Termos Nasi', 'Tempat Hidangan Lauk'])) {
                    $expiredAt = fake()->optional(0.5)->dateTimeBetween('+6 months', '+2 years');
                }

                DB::table('stocks')->insert([
                    'item_id' => $item->id,
                    'location_id' => $slot->id,
                    'quantity' => $quantity,
                    'batch' => $batch,
                    'expired_at' => $expiredAt ? $expiredAt->format('Y-m-d') : null,
                    'created_at' => fake()->dateTimeBetween('-6 months', 'now'),
                    'updated_at' => now(),
                ]);
            }
        }

        $this->command->info('Stock sarana sekolah seeded successfully!');
    }
}
