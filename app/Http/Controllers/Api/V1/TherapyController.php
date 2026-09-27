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
        $perPage = max(1, min(1000, $perPage));

        $query = Therapy::query();

        if ($search = trim((string) $request->input('search', ''))) {
            $query->where('therapy_name', 'like', "%{$search}%");
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $sortBy = (string) $request->input('sort_by', 'sequence');
        $sortDir = strtolower((string) $request->input('sort_dir', 'asc')) === 'desc' ? 'desc' : 'asc';
        $allowedSort = ['id', 'therapy_name', 'session_price', 'fixed_price', 'status', 'created_at', 'sequence'];
        if (! in_array($sortBy, $allowedSort, true)) {
            $sortBy = 'sequence';
        }
        $query->orderBy($sortBy, $sortDir);
        if ($sortBy !== 'id') {
            $query->orderBy('id', 'asc');
        }

        return ApiResponse::paginate($query->paginate($perPage), 'OK');
    }

    public function reorder(Request $request)
    {
        $items = $request->input('items', []);
        $ids = $request->input('ids', []);

        \Illuminate\Support\Facades\DB::transaction(function () use ($items, $ids) {
            if (! empty($ids) && is_array($ids)) {
                foreach ($ids as $index => $id) {
                    Therapy::where('id', $id)->update(['sequence' => $index + 1]);
                }
            } elseif (! empty($items) && is_array($items)) {
                foreach ($items as $index => $item) {
                    if (is_array($item) && isset($item['id'])) {
                        $seq = isset($item['sequence']) ? (int) $item['sequence'] : ($index + 1);
                        Therapy::where('id', $item['id'])->update(['sequence' => $seq]);
                    } elseif (is_numeric($item)) {
                        Therapy::where('id', $item)->update(['sequence' => $index + 1]);
                    }
                }
            }
        });

        return ApiResponse::success(null, 'Therapy sequence updated');
    }

    public function store(Request $request)
    {
        try {
            $this->validate($request, [
                'therapy_name' => ['required', 'string', 'max:150'],
                'short_name' => ['nullable', 'string', 'max:50'],
                'description' => ['nullable', 'string'],
                'session_price' => ['required', 'numeric', 'min:0'],
                'fixed_price' => ['required', 'numeric', 'min:0'],
                'status' => ['nullable', Rule::in(['active', 'inactive'])],
                'sequence' => ['nullable', 'integer'],
            ]);

            $data = $request->only(['therapy_name', 'short_name', 'description', 'session_price', 'fixed_price', 'status', 'sequence']);
            if (! isset($data['sequence']) || $data['sequence'] === null) {
                $maxSeq = Therapy::max('sequence') ?? 0;
                $data['sequence'] = $maxSeq + 1;
            }

            $therapy = Therapy::create($data);
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
                'short_name' => ['sometimes', 'nullable', 'string', 'max:50'],
                'description' => ['sometimes', 'nullable', 'string'],
                'session_price' => ['sometimes', 'required', 'numeric', 'min:0'],
                'fixed_price' => ['sometimes', 'required', 'numeric', 'min:0'],
                'status' => ['sometimes', 'required', Rule::in(['active', 'inactive'])],
                'sequence' => ['sometimes', 'nullable', 'integer'],
            ]);

            $therapy->fill($request->only(['therapy_name', 'short_name', 'description', 'session_price', 'fixed_price', 'status', 'sequence']));
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

