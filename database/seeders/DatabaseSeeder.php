<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
            // Every login account (Superadmin … Klien) lives in AccountSeeder.
            AccountSeeder::class,
            MindOsSeeder::class,
        ]);
    }
}
