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

        // Generate INBOUND transactions (penerimaan dari supplier)
        $inboundNotes = [
            'Penerimaan sarana sekolah dari supplier',
            'Penerimaan barang baru untuk distribusi',
            'Stock masuk dari Kemensos',
            'Penerimaan peralatan sekolah',
        ];

        for ($i = 0; $i < 12; $i++) {
            $item = $items->random();
            $toLocation = $locations->random();
            $user = $users->random();
            $date = fake()->dateTimeBetween('-2 months', 'now');

            DB::table('transactions')->insert([
                'transaction_code' => 'IN-' . $date->format('Ymd') . '-' . str_pad($i + 1, 3, '0', STR_PAD_LEFT),
                'type' => TransactionType::INBOUND->value,
                'item_id' => $item->id,
                'from_location_id' => null,
                'to_location_id' => $toLocation->id,
                'quantity' => fake()->numberBetween(20, 100),
                'batch' => 'SR-' . $date->format('Ymd') . '-' . strtoupper(fake()->lexify('???')),
                'user_id' => $user->id,
                'notes' => fake()->randomElement($inboundNotes) . ' - ' . $item->name,
                'created_at' => $date,
                'updated_at' => $date,
            ]);
        }

        // Generate OUTBOUND transactions (pengiriman ke sekolah)
        $outboundNotes = [
            'Pengiriman sarana ke sekolah',
            'Distribusi peralatan sekolah',
            'Pengiriman ke Sekolah Rakyat',
        ];

        for ($i = 0; $i < 10; $i++) {
            $item = $items->random();
            $fromLocation = $locations->random();
            $user = $users->random();
            $date = fake()->dateTimeBetween('-1 month', 'now');

            DB::table('transactions')->insert([
                'transaction_code' => 'OUT-' . $date->format('Ymd') . '-' . str_pad($i + 1, 3, '0', STR_PAD_LEFT),
                'type' => TransactionType::OUTBOUND->value,
                'item_id' => $item->id,
                'from_location_id' => $fromLocation->id,
                'to_location_id' => null,
                'quantity' => fake()->numberBetween(5, 30),
                'batch' => null,
                'user_id' => $user->id,
                'notes' => fake()->randomElement($outboundNotes) . ' - ' . $item->name,
                'created_at' => $date,
                'updated_at' => $date,
            ]);
        }

        // Generate TRANSFER transactions (pemindahan antar lokasi)
        $transferNotes = [
            'Pemindahan stock ke zona lain',
            'Reorganisasi lokasi penyimpanan',
            'Transfer untuk optimasi ruang',
        ];

        for ($i = 0; $i < 5; $i++) {
            $item = $items->random();
            $fromLocation = $locations->random();
            $availableLocations = $locations->where('id', '!=', $fromLocation->id);
            $toLocation = $availableLocations->isNotEmpty() ? $availableLocations->random() : $fromLocation;
            $user = $users->random();
            $date = fake()->dateTimeBetween('-3 weeks', 'now');

            DB::table('transactions')->insert([
                'transaction_code' => 'TRF-' . $date->format('Ymd') . '-' . str_pad($i + 1, 3, '0', STR_PAD_LEFT),
                'type' => TransactionType::TRANSFER->value,
                'item_id' => $item->id,
                'from_location_id' => $fromLocation->id,
                'to_location_id' => $toLocation->id,
                'quantity' => fake()->numberBetween(5, 20),
                'batch' => null,
                'user_id' => $user->id,
                'notes' => fake()->randomElement($transferNotes) . ' - ' . $item->name,
                'created_at' => $date,
                'updated_at' => $date,
            ]);
        }

        $this->command->info('Transaksi sarana sekolah seeded successfully!');
    }
}
