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

        $zoneNames = ['A', 'B', 'C'];
        $zoneDescriptions = [
            'A' => 'Zona Perabot Kelas (Meja, Kursi, Papan Tulis)',
            'B' => 'Zona Peralatan Dapur (Teko, Termos, Tempat Hidangan)',
            'C' => 'Zona Dekorasi & Perlengkapan (Foto, Lambang, Jam Dinding)'
        ];

        foreach ($warehouses as $warehouse) {
            // Create Zones (3 zones per warehouse dengan nama jelas)
            foreach ($zoneNames as $zoneName) {
                $zoneId = DB::table('locations')->insertGetId([
                    'warehouse_id' => $warehouse->id,
                    'parent_id' => null,
                    'code' => $warehouse->name . '-ZONE-' . $zoneName,
                    'type' => LocationType::ZONE->value,
                    'capacity' => 1000,
                    'description' => $zoneDescriptions[$zoneName] . ' - ' . $warehouse->name,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Create Racks per Zone (3 racks per zone untuk demo yang mudah)
                for ($r = 1; $r <= 3; $r++) {
                    $rackId = DB::table('locations')->insertGetId([
                        'warehouse_id' => $warehouse->id,
                        'parent_id' => $zoneId,
                        'code' => $warehouse->name . '-ZONE-' . $zoneName . '-RACK-' . str_pad($r, 2, '0', STR_PAD_LEFT),
                        'type' => LocationType::RACK->value,
                        'capacity' => 200,
                        'description' => 'Rak ' . $r . ' di Zona ' . $zoneName . ' - ' . $warehouse->name,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    // Create Slots per Rack (5 slots per rack untuk demo yang mudah)
                    for ($s = 1; $s <= 5; $s++) {
                        DB::table('locations')->insert([
                            'warehouse_id' => $warehouse->id,
                            'parent_id' => $rackId,
                            'code' => $warehouse->name . '-ZONE-' . $zoneName . '-RACK-' . str_pad($r, 2, '0', STR_PAD_LEFT) . '-SLOT-' . str_pad($s, 2, '0', STR_PAD_LEFT),
                            'type' => LocationType::SLOT->value,
                            'capacity' => 20,
                            'description' => 'Slot ' . $s . ' di Rak ' . $r . ' Zona ' . $zoneName,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }
        }

        $this->command->info('Lokasi gudang sekolah seeded successfully!');
    }
}
