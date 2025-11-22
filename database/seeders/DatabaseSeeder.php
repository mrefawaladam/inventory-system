<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed Roles and Permissions first
        $this->call([
            RolePermissionSeeder::class,
        ]);

        // Create Admin User
        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'password' => bcrypt('password'),
            ]
        );
        $admin->assignRole('admin');

        // Create Manager User
        $manager = User::firstOrCreate(
            ['email' => 'manager@example.com'],
            [
                'name' => 'Manager User',
                'password' => bcrypt('password'),
            ]
        );
        $manager->assignRole('manager');

        // Create Regular User
        $user = User::firstOrCreate(
            ['email' => 'user@example.com'],
            [
                'name' => 'Regular User',
                'password' => bcrypt('password'),
            ]
        );
        $user->assignRole('user');

        // Create Test User (if not exists)
        $testUser = User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
            'name' => 'Test User',
                'password' => bcrypt('password'),
            ]
        );
        if (!$testUser->hasRole('user')) {
            $testUser->assignRole('user');
        }

        $this->command->info('✓ Pengguna default berhasil dibuat');
        $this->command->info('  Admin: admin@example.com / password');
        $this->command->info('  Manager: manager@example.com / password');
        $this->command->info('  User: user@example.com / password');

        // Seed data master (dalam urutan yang benar)
        $this->command->info('Memuat data master...');
        $this->call([
            SupplierSeeder::class,
            CustomerSeeder::class,
            WarehouseSeeder::class,
            ItemSeeder::class,
            LocationSeeder::class,
            StockSeeder::class,
        ]);

        // Seed data transaksi
        $this->command->info('Memuat data transaksi...');
        $this->call([
            TransactionSeeder::class,
            InboundSeeder::class,
            OutboundSeeder::class,
        ]);

        $this->command->info('');
        $this->command->info('✓ Semua data berhasil dimuat!');
    }
}
