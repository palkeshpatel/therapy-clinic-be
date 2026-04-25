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
            ['role_name' => 'Admin',     'description' => 'Full system access'],
            ['role_name' => 'Therapist', 'description' => 'Therapist with limited access (own data)'],
            ['role_name' => 'Staff',     'description' => 'Reception/staff with operational access'],
        ];

        foreach ($roles as $r) {
            DB::table('roles')->updateOrInsert(
                ['role_name' => $r['role_name']],
                ['description' => $r['description'], 'created_at' => $now, 'updated_at' => $now]
            );
        }
    }
}
