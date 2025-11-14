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

        foreach ($items as $item) {
            // Assign stock to random slots (3-5 slots per item, or all if less available)
            $numSlots = min(fake()->numberBetween(3, 5), max(1, $slots->count()));
            $selectedSlots = $slots->count() > 0 ? $slots->random($numSlots) : collect();

            foreach ($selectedSlots as $slot) {
                $quantity = fake()->numberBetween(5, 50);
                $batch = 'BATCH-' . fake()->date('Ymd') . '-' . strtoupper(fake()->lexify('???'));
                $expiredAt = fake()->optional(0.3)->dateTimeBetween('+1 month', '+2 years');

                DB::table('stocks')->insert([
                    'item_id' => $item->id,
                    'location_id' => $slot->id,
                    'quantity' => $quantity,
                    'batch' => $batch,
                    'expired_at' => $expiredAt ? $expiredAt->format('Y-m-d') : null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        $this->command->info('Stocks seeded successfully!');
    }
}
