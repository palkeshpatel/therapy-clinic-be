<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $adminRoleId = DB::table('roles')->where('role_type', 'admin')->orderBy('id')->value('id');

        if (! $adminRoleId) {
            return;
        }

        $now = Carbon::now();

        DB::table('users')->updateOrInsert(
            ['email' => 'admin@clinic.com'],
            [
                'name'       => 'Administrator',
                'phone'      => '9999999999',
                'password'   => Hash::make('admin123'),
                'role_id'    => $adminRoleId,
                'status'     => 'active',
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );
    }
}
