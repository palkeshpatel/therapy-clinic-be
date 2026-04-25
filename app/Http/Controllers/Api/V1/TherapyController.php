<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Therapy;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class TherapyController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) ($request->input('per_page', 15));
        $perPage = max(1, min(100, $perPage));

        $query = Therapy::query();

        if ($search = trim((string) $request->input('search', ''))) {
            $query->where('therapy_name', 'like', "%{$search}%");
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $sortBy = (string) $request->input('sort_by', 'therapy_name');
        $sortDir = strtolower((string) $request->input('sort_dir', 'asc')) === 'desc' ? 'desc' : 'asc';
        $allowedSort = ['id', 'therapy_name', 'default_price', 'status', 'created_at'];
        if (! in_array($sortBy, $allowedSort, true)) {
            $sortBy = 'therapy_name';
        }
        $query->orderBy($sortBy, $sortDir);

        return ApiResponse::paginate($query->paginate($perPage), 'OK');
    }

    public function store(Request $request)
    {
        try {
            $this->validate($request, [
                'therapy_name' => ['required', 'string', 'max:150'],
                'description' => ['nullable', 'string'],
                'default_price' => ['required', 'numeric', 'min:0'],
                'status' => ['nullable', Rule::in(['active', 'inactive'])],
            ]);

            $therapy = Therapy::create($request->only(['therapy_name', 'description', 'default_price', 'status']));
            return ApiResponse::success($therapy, 'Therapy created', 201);
        } catch (ValidationException $e) {
            return ApiResponse::error('Validation failed', 422, $e->errors());
        }
    }

    public function show($id)
    {
        $therapy = Therapy::find($id);
        if (! $therapy) {
            return ApiResponse::error('Therapy not found', 404);
        }
        return ApiResponse::success($therapy, 'OK');
    }

    public function update(Request $request, $id)
    {
        $therapy = Therapy::find($id);
        if (! $therapy) {
            return ApiResponse::error('Therapy not found', 404);
        }

        try {
            $this->validate($request, [
                'therapy_name' => ['sometimes', 'required', 'string', 'max:150'],
                'description' => ['sometimes', 'nullable', 'string'],
                'default_price' => ['sometimes', 'required', 'numeric', 'min:0'],
                'status' => ['sometimes', 'required', Rule::in(['active', 'inactive'])],
            ]);

            $therapy->fill($request->only(['therapy_name', 'description', 'default_price', 'status']));
            $therapy->save();

            return ApiResponse::success($therapy, 'Therapy updated');
        } catch (ValidationException $e) {
            return ApiResponse::error('Validation failed', 422, $e->errors());
        }
    }

    public function destroy($id)
    {
        $therapy = Therapy::find($id);
        if (! $therapy) {
            return ApiResponse::error('Therapy not found', 404);
        }

        $therapy->delete();
        return ApiResponse::success(null, 'Therapy deleted');
    }
}

