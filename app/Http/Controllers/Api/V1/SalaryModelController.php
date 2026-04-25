<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\SalaryModel;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class SalaryModelController extends Controller
{
    public function index(Request $request)
    {
        $query = SalaryModel::query()->with('therapist');

        if ($therapistId = $request->input('therapist_id')) {
            $query->where('therapist_id', $therapistId);
        }

        $query->orderByDesc('effective_from')->orderByDesc('id');

        return ApiResponse::success($query->get(), 'OK');
    }

    public function store(Request $request)
    {
        try {
            $this->validate($request, [
                'therapist_id' => ['required', 'integer', 'exists:therapists,id'],
                'salary_type' => ['required', Rule::in(['fixed', 'per_session', 'hybrid'])],
                'fixed_salary' => ['nullable', 'numeric', 'min:0'],
                'per_session_rate' => ['nullable', 'numeric', 'min:0'],
                'effective_from' => ['nullable', 'date'],
            ]);

            $row = SalaryModel::create($request->only([
                'therapist_id',
                'salary_type',
                'fixed_salary',
                'per_session_rate',
                'effective_from',
            ]));
            $row->load('therapist');

            return ApiResponse::success($row, 'Salary model created', 201);
        } catch (ValidationException $e) {
            return ApiResponse::error('Validation failed', 422, $e->errors());
        }
    }

    public function update(Request $request, $id)
    {
        $row = SalaryModel::find($id);
        if (! $row) {
            return ApiResponse::error('Salary model not found', 404);
        }

        try {
            $this->validate($request, [
                'salary_type' => ['sometimes', 'required', Rule::in(['fixed', 'per_session', 'hybrid'])],
                'fixed_salary' => ['sometimes', 'nullable', 'numeric', 'min:0'],
                'per_session_rate' => ['sometimes', 'nullable', 'numeric', 'min:0'],
                'effective_from' => ['sometimes', 'nullable', 'date'],
            ]);

            $row->fill($request->only(['salary_type', 'fixed_salary', 'per_session_rate', 'effective_from']));
            $row->save();
            $row->load('therapist');

            return ApiResponse::success($row, 'Salary model updated');
        } catch (ValidationException $e) {
            return ApiResponse::error('Validation failed', 422, $e->errors());
        }
    }
}

