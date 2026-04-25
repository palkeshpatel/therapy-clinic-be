<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolePermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $roles = DB::table('roles')->pluck('id', 'role_name');
        $permissions = DB::table('permissions')->get();

        // Admin → everything
        $adminId = $roles['Admin'] ?? null;
        if ($adminId) {
            foreach ($permissions as $p) {
                DB::table('role_permissions')->updateOrInsert(
                    ['role_id' => $adminId, 'permission_id' => $p->id],
                    ['created_at' => $now, 'updated_at' => $now]
                );
            }
        }

        // Therapist → view-only on most + own attendance/leaves/sessions/scheduling
        $therapistId = $roles['Therapist'] ?? null;
        if ($therapistId) {
            $therapistAllowed = [
                'patients' => ['view'],
                'therapies' => ['view'],
                'therapists' => ['view'],
                'attendance' => ['view', 'create', 'edit'],
                'leaves' => ['view', 'create'],
                'salary' => ['view'],
                'scheduling' => ['view'],
                'sessions' => ['view', 'create', 'edit'],
                'invoices' => ['view'],
            ];
            $this->grant($therapistId, $therapistAllowed, $permissions, $now);
        }

        // Staff → operational (no admin/salary/users)
        $staffId = $roles['Staff'] ?? null;
        if ($staffId) {
            $staffAllowed = [
                'patients' => ['view', 'create', 'edit', 'delete'],
                'therapies' => ['view'],
                'therapists' => ['view'],
                'attendance' => ['view', 'create', 'edit'],
                'leaves' => ['view'],
                'scheduling' => ['view', 'create', 'edit', 'delete'],
                'sessions' => ['view', 'create', 'edit'],
                'invoices' => ['view', 'create', 'edit'],
                'payments' => ['view', 'create'],
                'clinic' => ['view'],
            ];
            $this->grant($staffId, $staffAllowed, $permissions, $now);
        }
    }

    private function grant(int $roleId, array $allowed, $permissions, $now): void
    {
        foreach ($permissions as $p) {
            if (! isset($allowed[$p->module])) {
                continue;
            }
            if (! in_array($p->action, $allowed[$p->module], true)) {
                continue;
            }

            DB::table('role_permissions')->updateOrInsert(
                ['role_id' => $roleId, 'permission_id' => $p->id],
                ['created_at' => $now, 'updated_at' => $now]
            );
        }
    }
}
