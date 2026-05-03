<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolesSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $roles = [
            ['role_name' => 'Admin',     'role_type' => 'admin',     'description' => 'Full system access'],
            ['role_name' => 'Therapist', 'role_type' => 'therapist', 'description' => 'Therapist with limited access (own data)'],
            ['role_name' => 'Receptionist', 'role_type' => 'admin',  'description' => 'Receptionist with operational access'],
        ];

        foreach ($roles as $r) {
            DB::table('roles')->updateOrInsert(
                ['role_name' => $r['role_name']],
                ['role_type' => $r['role_type'], 'description' => $r['description'], 'created_at' => $now, 'updated_at' => $now]
            );
        }
    }
}
