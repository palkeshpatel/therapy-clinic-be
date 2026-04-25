<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolesSeeder::class,
            PermissionsSeeder::class,
            RolePermissionsSeeder::class,
            AdminUserSeeder::class,
            ClinicSeeder::class,
            TimeSlotsSeeder::class,
            DemoDataSeeder::class,
        ]);
    }
}
