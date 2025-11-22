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
        $statuses = [OutboundStatus::PENDING->value, OutboundStatus::COMPLETED->value, OutboundStatus::COMPLETED->value];
        
        for ($i = 0; $i < 3; $i++) {
            $customer = $customers->random();
            $user = $users->random();
            $status = fake()->randomElement($statuses);
            $date = fake()->dateTimeBetween('-1 month', 'now');

            $updatedDate = clone $date;
            if ($status === OutboundStatus::COMPLETED->value) {
                $updatedDate->modify('+1 day');
            }

            DB::table('outbound')->insert([
                'outbound_code' => 'OUTB-' . $date->format('Ymd') . '-' . str_pad($i + 1, 3, '0', STR_PAD_LEFT),
                'customer_id' => $customer->id,
                'created_by' => $user->id,
                'status' => $status,
                'created_at' => $date,
                'updated_at' => $updatedDate,
            ]);
        }

        $this->command->info('✓ Data outbound berhasil dimuat');
    }
}
