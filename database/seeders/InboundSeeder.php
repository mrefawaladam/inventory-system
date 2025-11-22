<?php

namespace Database\Seeders;

use App\Enums\InboundStatus;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InboundSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $suppliers = DB::table('suppliers')->get();
        $users = DB::table('users')->get();
        $items = DB::table('items')->get();

        if ($suppliers->isEmpty() || $users->isEmpty()) {
            $this->command->warn('Suppliers or Users not found. Please run SupplierSeeder and UserSeeder first.');
            return;
        }

        // Generate inbound records dengan data yang jelas untuk demo
        $statuses = [InboundStatus::PENDING->value, InboundStatus::COMPLETED->value, InboundStatus::COMPLETED->value]; // Lebih banyak completed untuk demo
        
        for ($i = 0; $i < 10; $i++) {
            $supplier = $suppliers->random();
            $user = $users->random();
            $status = fake()->randomElement($statuses);
            $date = fake()->dateTimeBetween('-2 months', 'now');

            $updatedDate = clone $date;
            if ($status === InboundStatus::COMPLETED->value) {
                $updatedDate->modify('+1 day');
            }

            DB::table('inbound')->insert([
                'inbound_code' => 'INB-' . $date->format('Ymd') . '-' . str_pad($i + 1, 3, '0', STR_PAD_LEFT),
                'supplier_id' => $supplier->id,
                'received_by' => $user->id,
                'status' => $status,
                'created_at' => $date,
                'updated_at' => $updatedDate,
            ]);
        }

        $this->command->info('Inbound sarana sekolah seeded successfully!');
    }
}
