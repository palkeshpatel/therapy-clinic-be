<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PatientController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) ($request->input('per_page', 15));
        $perPage = max(1, min(100, $perPage));

        $query = Patient::query();

        if ($search = trim((string) $request->input('search', ''))) {
            $query->where(function ($q) use ($search) {
                $q->where('patient_name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $sortBy = (string) $request->input('sort_by', 'created_at');
        $sortDir = strtolower((string) $request->input('sort_dir', 'desc')) === 'asc' ? 'asc' : 'desc';
        $allowedSort = ['id', 'patient_name', 'phone', 'status', 'created_at'];
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
                'patient_name' => ['required', 'string', 'max:150'],
                'phone' => ['required', 'string', 'max:20'],
                'email' => ['nullable', 'email', 'max:150'],
                'dob' => ['nullable', 'date'],
                'gender' => ['nullable', Rule::in(['male', 'female', 'other'])],
                'address' => ['nullable', 'string'],
                'status' => ['nullable', Rule::in(['active', 'inactive', 'return'])],
            ]);

            $patient = Patient::create($request->only([
                'patient_name', 'phone', 'email', 'dob', 'gender', 'address', 'status',
            ]));

            return ApiResponse::success($patient, 'Patient created', 201);
        } catch (ValidationException $e) {
            return ApiResponse::error('Validation failed', 422, $e->errors());
        }
    }

    public function show($id)
    {
        $patient = Patient::with(['medicalRecords', 'therapies'])->find($id);
        if (! $patient) {
            return ApiResponse::error('Patient not found', 404);
        }
        return ApiResponse::success($patient, 'OK');
    }

    public function update(Request $request, $id)
    {
        $patient = Patient::find($id);
        if (! $patient) {
            return ApiResponse::error('Patient not found', 404);
        }

        try {
            $this->validate($request, [
                'patient_name' => ['sometimes', 'required', 'string', 'max:150'],
                'phone' => ['sometimes', 'required', 'string', 'max:20'],
                'email' => ['sometimes', 'nullable', 'email', 'max:150'],
                'dob' => ['sometimes', 'nullable', 'date'],
                'gender' => ['sometimes', 'nullable', Rule::in(['male', 'female', 'other'])],
                'address' => ['sometimes', 'nullable', 'string'],
                'status' => ['sometimes', 'required', Rule::in(['active', 'inactive', 'return'])],
            ]);

            $patient->fill($request->only([
                'patient_name', 'phone', 'email', 'dob', 'gender', 'address', 'status',
            ]));
            $patient->save();

            return ApiResponse::success($patient, 'Patient updated');
        } catch (ValidationException $e) {
            return ApiResponse::error('Validation failed', 422, $e->errors());
        }
    }

    public function destroy($id)
    {
        $patient = Patient::find($id);
        if (! $patient) {
            return ApiResponse::error('Patient not found', 404);
        }

        $patient->delete();
        return ApiResponse::success(null, 'Patient deleted');
    }
}

