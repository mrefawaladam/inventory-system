<?php

namespace Database\Seeders;

use App\Enums\OutboundStatus;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OutboundSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $customers = DB::table('customers')->get();
        $users = DB::table('users')->get();

        if ($customers->isEmpty() || $users->isEmpty()) {
            $this->command->warn('Customers or Users not found. Please run CustomerSeeder and UserSeeder first.');
            return;
        }

        // Generate outbound records
        for ($i = 0; $i < 12; $i++) {
            $customer = $customers->random();
            $user = $users->random();
            $status = fake()->randomElement([OutboundStatus::PENDING->value, OutboundStatus::COMPLETED->value]);

            DB::table('outbound')->insert([
                'outbound_code' => 'OUTB-' . now()->format('Ymd') . '-' . str_pad($i + 1, 4, '0', STR_PAD_LEFT),
                'customer_id' => $customer->id,
                'created_by' => $user->id,
                'status' => $status,
                'created_at' => fake()->dateTimeBetween('-30 days', 'now'),
                'updated_at' => now(),
            ]);
        }

        $this->command->info('Outbound records seeded successfully!');
    }
}
