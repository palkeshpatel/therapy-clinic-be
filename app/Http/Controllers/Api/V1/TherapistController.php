<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Therapist;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class TherapistController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) ($request->input('per_page', 15));
        $perPage = max(1, min(100, $perPage));

        $query = Therapist::query();

        if ($search = trim((string) $request->input('search', ''))) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('specialization', 'like', "%{$search}%");
            });
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $query->orderBy('name');

        return ApiResponse::paginate($query->paginate($perPage), 'OK');
    }

    public function store(Request $request)
    {
        try {
            $this->validate($request, [
                'user_id' => ['nullable', 'integer', 'exists:users,id'],
                'name' => ['required', 'string', 'max:150'],
                'specialization' => ['nullable', 'string', 'max:150'],
                'phone' => ['nullable', 'string', 'max:20'],
                'email' => ['nullable', 'email', 'max:150'],
                'joined_date' => ['nullable', 'date'],
                'status' => ['nullable', Rule::in(['active', 'inactive'])],
            ]);

            $therapist = Therapist::create($request->only([
                'user_id',
                'name',
                'specialization',
                'phone',
                'email',
                'joined_date',
                'status',
            ]));

            return ApiResponse::success($therapist, 'Therapist created', 201);
        } catch (ValidationException $e) {
            return ApiResponse::error('Validation failed', 422, $e->errors());
        }
    }

    public function show($id)
    {
        $therapist = Therapist::with('documents')->find($id);
        if (! $therapist) {
            return ApiResponse::error('Therapist not found', 404);
        }
        return ApiResponse::success($therapist, 'OK');
    }

    public function update(Request $request, $id)
    {
        $therapist = Therapist::find($id);
        if (! $therapist) {
            return ApiResponse::error('Therapist not found', 404);
        }

        try {
            $this->validate($request, [
                'user_id' => ['sometimes', 'nullable', 'integer', 'exists:users,id'],
                'name' => ['sometimes', 'required', 'string', 'max:150'],
                'specialization' => ['sometimes', 'nullable', 'string', 'max:150'],
                'phone' => ['sometimes', 'nullable', 'string', 'max:20'],
                'email' => ['sometimes', 'nullable', 'email', 'max:150'],
                'joined_date' => ['sometimes', 'nullable', 'date'],
                'status' => ['sometimes', 'required', Rule::in(['active', 'inactive'])],
            ]);

            $therapist->fill($request->only([
                'user_id',
                'name',
                'specialization',
                'phone',
                'email',
                'joined_date',
                'status',
            ]));
            $therapist->save();

            return ApiResponse::success($therapist, 'Therapist updated');
        } catch (ValidationException $e) {
            return ApiResponse::error('Validation failed', 422, $e->errors());
        }
    }

    public function destroy($id)
    {
        $therapist = Therapist::find($id);
        if (! $therapist) {
            return ApiResponse::error('Therapist not found', 404);
        }

        $therapist->delete();
        return ApiResponse::success(null, 'Therapist deleted');
    }
}

