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

        $zoneDescriptions = [
            'A' => 'Zona Perabot Kelas (Meja, Kursi, Papan Tulis)',
            'B' => 'Zona Peralatan Dapur (Teko, Termos, Tempat Hidangan)',
        ];

        foreach ($warehouses as $warehouse) {
            // Buat 2 zona per gudang (lebih sederhana)
            foreach (['A', 'B'] as $zoneName) {
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

                // Buat 2 rak per zona
                for ($r = 1; $r <= 2; $r++) {
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

                    // Buat 3 slot per rak
                    for ($s = 1; $s <= 3; $s++) {
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

        $this->command->info('✓ Data lokasi berhasil dimuat');
    }
}
