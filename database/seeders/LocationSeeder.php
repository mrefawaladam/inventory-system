<?php

namespace Database\Seeders;

use App\Enums\LocationType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $warehouses = DB::table('warehouses')->get();

        foreach ($warehouses as $warehouse) {
            // Create Zones (3 zones per warehouse)
            for ($z = 1; $z <= 3; $z++) {
                $zoneId = DB::table('locations')->insertGetId([
                    'warehouse_id' => $warehouse->id,
                    'parent_id' => null,
                    'code' => 'ZONE-' . str_pad($z, 2, '0', STR_PAD_LEFT),
                    'type' => LocationType::ZONE->value,
                    'capacity' => 1000,
                    'description' => 'Zone ' . $z . ' - ' . $warehouse->name,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Create Racks per Zone (5 racks per zone)
                for ($r = 1; $r <= 5; $r++) {
                    $rackId = DB::table('locations')->insertGetId([
                        'warehouse_id' => $warehouse->id,
                        'parent_id' => $zoneId,
                        'code' => 'ZONE-' . str_pad($z, 2, '0', STR_PAD_LEFT) . '-RACK-' . str_pad($r, 2, '0', STR_PAD_LEFT),
                        'type' => LocationType::RACK->value,
                        'capacity' => 200,
                        'description' => 'Rack ' . $r . ' di Zone ' . $z,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    // Create Slots per Rack (10 slots per rack)
                    for ($s = 1; $s <= 10; $s++) {
                        DB::table('locations')->insert([
                            'warehouse_id' => $warehouse->id,
                            'parent_id' => $rackId,
                            'code' => 'ZONE-' . str_pad($z, 2, '0', STR_PAD_LEFT) . '-RACK-' . str_pad($r, 2, '0', STR_PAD_LEFT) . '-SLOT-' . str_pad($s, 2, '0', STR_PAD_LEFT),
                            'type' => LocationType::SLOT->value,
                            'capacity' => 20,
                            'description' => 'Slot ' . $s . ' di Rack ' . $r . ' Zone ' . $z,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }
        }

        $this->command->info('Locations seeded successfully!');
    }
}
