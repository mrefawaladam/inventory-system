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

        if ($suppliers->isEmpty() || $users->isEmpty()) {
            $this->command->warn('Suppliers or Users not found. Please run SupplierSeeder and UserSeeder first.');
            return;
        }

        // Generate inbound records
        for ($i = 0; $i < 15; $i++) {
            $supplier = $suppliers->random();
            $user = $users->random();
            $status = fake()->randomElement([InboundStatus::PENDING->value, InboundStatus::COMPLETED->value]);

            DB::table('inbound')->insert([
                'inbound_code' => 'INB-' . now()->format('Ymd') . '-' . str_pad($i + 1, 4, '0', STR_PAD_LEFT),
                'supplier_id' => $supplier->id,
                'received_by' => $user->id,
                'status' => $status,
                'created_at' => fake()->dateTimeBetween('-30 days', 'now'),
                'updated_at' => now(),
            ]);
        }

        $this->command->info('Inbound records seeded successfully!');
    }
}
