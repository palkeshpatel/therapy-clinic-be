<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $modules = [
            'users', 'roles', 'clinic',
            'patients', 'therapies', 'therapists',
            'attendance', 'leaves', 'salary',
            'scheduling', 'sessions', 'invoices',
            'payments', 'reports',
        ];

        $actions = ['view', 'create', 'edit', 'delete'];

        $rows = [];
        foreach ($modules as $module) {
            foreach ($actions as $action) {
                $rows[] = [
                    'module'     => $module,
                    'action'     => $action,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        foreach ($rows as $row) {
            DB::table('permissions')->updateOrInsert(
                ['module' => $row['module'], 'action' => $row['action']],
                ['created_at' => $row['created_at'], 'updated_at' => $row['updated_at']]
            );
        }
    }
}
