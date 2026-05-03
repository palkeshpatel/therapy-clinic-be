<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class RoleController extends Controller
{
    public function index()
    {
        return ApiResponse::success(Role::query()->withCount('permissions')->orderBy('role_type')->orderBy('role_name')->get(), 'OK');
    }

    public function store(Request $request)
    {
        try {
            $this->validate($request, [
                'role_name' => ['required', 'string', 'max:50', 'unique:roles,role_name'],
                'role_type' => ['nullable', 'in:admin,therapist'],
                'description' => ['nullable', 'string'],
            ]);

            $payload = $request->only(['role_name', 'description', 'role_type']);
            if (! isset($payload['role_type'])) {
                $payload['role_type'] = 'admin';
            }
            $role = Role::create($payload);

            return ApiResponse::success($role, 'Role created', 201);
        } catch (ValidationException $e) {
            return ApiResponse::error('Validation failed', 422, $e->errors());
        }
    }

    public function show($id)
    {
        $role = Role::with('permissions')->find($id);
        if (! $role) {
            return ApiResponse::error('Role not found', 404);
        }
        return ApiResponse::success($role, 'OK');
    }

    public function update(Request $request, $id)
    {
        $role = Role::find($id);
        if (! $role) {
            return ApiResponse::error('Role not found', 404);
        }

        try {
            $this->validate($request, [
                'role_name' => ['sometimes', 'required', 'string', 'max:50', 'unique:roles,role_name,' . $role->id],
                'role_type' => ['sometimes', 'required', 'in:admin,therapist'],
                'description' => ['sometimes', 'nullable', 'string'],
            ]);

            $role->fill($request->only(['role_name', 'description', 'role_type']));
            $role->save();

            return ApiResponse::success($role, 'Role updated');
        } catch (ValidationException $e) {
            return ApiResponse::error('Validation failed', 422, $e->errors());
        }
    }

    public function destroy($id)
    {
        $role = Role::find($id);
        if (! $role) {
            return ApiResponse::error('Role not found', 404);
        }

        $role->delete();
        return ApiResponse::success(null, 'Role deleted');
    }

    public function syncPermissions(Request $request, $id)
    {
        $role = Role::find($id);
        if (! $role) {
            return ApiResponse::error('Role not found', 404);
        }

        try {
            $this->validate($request, [
                'permission_ids' => ['required', 'array'],
                'permission_ids.*' => ['integer', 'distinct', 'exists:permissions,id'],
            ]);

            $ids = $request->input('permission_ids', []);
            $role->permissions()->sync($ids);
            $role->load('permissions');

            return ApiResponse::success($role, 'Permissions synced');
        } catch (ValidationException $e) {
            return ApiResponse::error('Validation failed', 422, $e->errors());
        }
    }
}

