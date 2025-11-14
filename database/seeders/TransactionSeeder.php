<?php

namespace Database\Seeders;

use App\Enums\TransactionType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TransactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $items = DB::table('items')->get();
        $locations = DB::table('locations')->where('type', 'SLOT')->get();
        $users = DB::table('users')->get();

        if ($items->isEmpty()) {
            $this->command->warn('No items found. Please run ItemSeeder first.');
            return;
        }

        if ($locations->isEmpty()) {
            $this->command->warn('No locations found. Please run LocationSeeder first.');
            return;
        }

        if ($users->isEmpty()) {
            $this->command->warn('No users found. Please run UserSeeder first.');
            return;
        }

        // Generate INBOUND transactions
        for ($i = 0; $i < 20; $i++) {
            $item = $items->random();
            $toLocation = $locations->random();
            $user = $users->random();

            DB::table('transactions')->insert([
                'transaction_code' => 'IN-' . now()->format('Ymd') . '-' . str_pad($i + 1, 4, '0', STR_PAD_LEFT),
                'type' => TransactionType::INBOUND->value,
                'item_id' => $item->id,
                'from_location_id' => null,
                'to_location_id' => $toLocation->id,
                'quantity' => fake()->numberBetween(10, 100),
                'batch' => 'BATCH-' . fake()->date('Ymd') . '-' . strtoupper(fake()->lexify('???')),
                'user_id' => $user->id,
                'notes' => fake()->optional(0.7)->sentence(),
                'created_at' => fake()->dateTimeBetween('-30 days', 'now'),
                'updated_at' => now(),
            ]);
        }

        // Generate OUTBOUND transactions
        for ($i = 0; $i < 15; $i++) {
            $item = $items->random();
            $fromLocation = $locations->random();
            $user = $users->random();

            DB::table('transactions')->insert([
                'transaction_code' => 'OUT-' . now()->format('Ymd') . '-' . str_pad($i + 1, 4, '0', STR_PAD_LEFT),
                'type' => TransactionType::OUTBOUND->value,
                'item_id' => $item->id,
                'from_location_id' => $fromLocation->id,
                'to_location_id' => null,
                'quantity' => fake()->numberBetween(5, 50),
                'batch' => null,
                'user_id' => $user->id,
                'notes' => fake()->optional(0.7)->sentence(),
                'created_at' => fake()->dateTimeBetween('-30 days', 'now'),
                'updated_at' => now(),
            ]);
        }

        // Generate TRANSFER transactions
        for ($i = 0; $i < 10; $i++) {
            $item = $items->random();
            $fromLocation = $locations->random();
            $availableLocations = $locations->where('id', '!=', $fromLocation->id);
            $toLocation = $availableLocations->isNotEmpty() ? $availableLocations->random() : $fromLocation;
            $user = $users->random();

            DB::table('transactions')->insert([
                'transaction_code' => 'TRF-' . now()->format('Ymd') . '-' . str_pad($i + 1, 4, '0', STR_PAD_LEFT),
                'type' => TransactionType::TRANSFER->value,
                'item_id' => $item->id,
                'from_location_id' => $fromLocation->id,
                'to_location_id' => $toLocation->id,
                'quantity' => fake()->numberBetween(5, 30),
                'batch' => null,
                'user_id' => $user->id,
                'notes' => fake()->optional(0.7)->sentence(),
                'created_at' => fake()->dateTimeBetween('-30 days', 'now'),
                'updated_at' => now(),
            ]);
        }

        $this->command->info('Transactions seeded successfully!');
    }
}
