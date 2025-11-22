<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Sekolah-sekolah sebagai customer (penerima sarana)
        $customers = [
            ['name' => 'Sekolah Rakyat Menengah Pertama 1 Deli Serdang', 'phone' => '082134567890', 'address' => 'Sentra Insyaf Medan, JL. Bedikari No. 37 Lau Bakeri Deli Serdang, Suka Rende, Kec. Kutalimbaru, Medan, Sumatera Utara'],
            ['name' => 'Sekolah Rakyat Menengah Pertama 19 Kupang', 'phone' => '082134567891', 'address' => 'Sentra Efata Kupang, Jl. Terusan Timor Raya No.KM. 36, Naibonat, Kec. Kupang Tim., Kabupaten Kupang, Nusa Tenggara Timur'],
            ['name' => 'Sekolah Rakyat Menengah Atas 7 Palembang', 'phone' => '082134567892', 'address' => 'Sentra Budi Perkasa Palembang, Jl. Sosial No.441 Km. 5, Suka Bangun, Kec. Sukarami, Kota Palembang, Sumatera Selatan'],
            ['name' => 'Sekolah Rakyat Terintegrasi 1 Cirebon', 'phone' => '082134567893', 'address' => 'SMP Negeri 18 Cirebon, Jl. Pronggol No.19, Pegambiran, Kec. Lemahwungkuk, Kota Cirebon, Jawa Barat'],
            ['name' => 'Sekolah Rakyat Menengah Atas 43 Magelang', 'phone' => '082134567894', 'address' => 'Sentra Antasena Magelang, Jl. Raya Magelang - Purworejo Km. 14, Salaman Kabupaten Magelang, Jawa Tengah'],
        ];

        foreach ($customers as $customer) {
            DB::table('customers')->insert([
                'name' => $customer['name'],
                'phone' => $customer['phone'],
                'address' => $customer['address'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->command->info('✓ Data customer berhasil dimuat');
    }
}
