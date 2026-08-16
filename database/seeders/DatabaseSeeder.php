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
            HolidaySeeder::class,
            TherapySeeder::class,
            DemoDataSeeder::class,
            PatientSeeder::class,
        ]);
    }
}
