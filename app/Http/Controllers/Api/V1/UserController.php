<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) ($request->input('per_page', 15));
        $perPage = max(1, min(100, $perPage));

        $query = User::query()->with(['role', 'therapist']);

        if ($search = trim((string) $request->input('search', ''))) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $sortBy = (string) $request->input('sort_by', 'created_at');
        $sortDir = strtolower((string) $request->input('sort_dir', 'desc')) === 'asc' ? 'asc' : 'desc';
        $allowedSort = ['id', 'name', 'email', 'status', 'created_at'];
        if (! in_array($sortBy, $allowedSort, true)) {
            $sortBy = 'created_at';
        }
        $query->orderBy($sortBy, $sortDir);

        return ApiResponse::paginate($query->paginate($perPage), 'OK');
    }

    public function store(Request $request)
    {
        try {
            $this->validate($request, [
                'name' => ['required', 'string', 'max:100'],
                'email' => ['required', 'email', 'max:150', 'unique:users,email'],
                'phone' => ['nullable', 'string', 'max:20'],
                'password' => ['required', 'string', 'min:6'],
                'role_id' => ['required', 'integer', 'exists:roles,id'],
                'status' => ['nullable', Rule::in(['active', 'inactive'])],
                'specialization' => ['nullable', 'string', 'max:150'],
            ]);

            $user = new User();
            $user->fill($request->only(['name', 'email', 'phone', 'role_id', 'status']));
            $user->password = Hash::make((string) $request->input('password'));
            if (! $user->status) {
                $user->status = 'active';
            }
            $user->save();

            $role = \App\Models\Role::find($user->role_id);
            if ($role && $role->role_type === 'therapist') {
                \App\Models\Therapist::create([
                    'user_id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'status' => $user->status,
                    'specialization' => $request->input('specialization'),
                ]);
            }

            $user->load(['role', 'therapist']);

            return ApiResponse::success($user, 'User created', 201);
        } catch (ValidationException $e) {
            return ApiResponse::error('Validation failed', 422, $e->errors());
        }
    }

    public function show($id)
    {
        $user = User::with(['role', 'therapist'])->find($id);
        if (! $user) {
            return ApiResponse::error('User not found', 404);
        }
        return ApiResponse::success($user, 'OK');
    }

    public function update(Request $request, $id)
    {
        $user = User::find($id);
        if (! $user) {
            return ApiResponse::error('User not found', 404);
        }

        try {
            $this->validate($request, [
                'name' => ['sometimes', 'required', 'string', 'max:100'],
                'email' => ['sometimes', 'required', 'email', 'max:150', Rule::unique('users', 'email')->ignore($user->id)],
                'phone' => ['sometimes', 'nullable', 'string', 'max:20'],
                'password' => ['sometimes', 'required', 'string', 'min:6'],
                'role_id' => ['sometimes', 'required', 'integer', 'exists:roles,id'],
                'status' => ['sometimes', 'required', Rule::in(['active', 'inactive'])],
                'specialization' => ['sometimes', 'nullable', 'string', 'max:150'],
            ]);

            $fields = $request->only(['name', 'email', 'phone', 'status']);
            if (! $user->isAdministrator() && $request->has('role_id')) {
                $fields['role_id'] = $request->input('role_id');
            } elseif ($user->isAdministrator() && $request->has('role_id')
                && (int) $request->input('role_id') !== (int) $user->role_id) {
                return ApiResponse::error('Administrator role cannot be changed', 403);
            }

            $user->fill($fields);
            if ($request->has('password')) {
                $user->password = Hash::make((string) $request->input('password'));
            }
            $user->save();

            $role = \App\Models\Role::find($user->role_id);
            if ($role && $role->role_type === 'therapist') {
                $therapist = \App\Models\Therapist::where('user_id', $user->id)->first();
                if (!$therapist) {
                    $therapist = new \App\Models\Therapist();
                    $therapist->user_id = $user->id;
                }
                $therapist->fill([
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'status' => $user->status,
                    'specialization' => $request->input('specialization'),
                ]);
                $therapist->save();
            } else {
                \App\Models\Therapist::where('user_id', $user->id)->update(['user_id' => null]);
            }

            $user->load(['role', 'therapist']);

            return ApiResponse::success($user, 'User updated');
        } catch (ValidationException $e) {
            return ApiResponse::error('Validation failed', 422, $e->errors());
        }
    }

    public function destroy($id)
    {
        $user = User::with('role')->find($id);
        if (! $user) {
            return ApiResponse::error('User not found', 404);
        }

        if ($user->isAdministrator()) {
            return ApiResponse::error('Administrator account cannot be deleted', 403);
        }

        $user->delete();
        return ApiResponse::success(null, 'User deleted');
    }
}

