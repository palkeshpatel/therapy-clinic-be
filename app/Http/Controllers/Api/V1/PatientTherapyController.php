<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\PatientTherapy;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PatientTherapyController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) ($request->input('per_page', 15));
        $perPage = max(1, min(100, $perPage));

        $query = PatientTherapy::query()->with(['patient', 'therapy', 'therapist']);

        if ($patientId = $request->input('patient_id')) {
            $query->where('patient_id', $patientId);
        }
        if ($therapyId = $request->input('therapy_id')) {
            $query->where('therapy_id', $therapyId);
        }
        if ($therapistId = $request->input('therapist_id')) {
            $query->where('therapist_id', $therapistId);
        }
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $query->orderByDesc('id');

        return ApiResponse::paginate($query->paginate($perPage), 'OK');
    }

    public function store(Request $request)
    {
        try {
            $this->validate($request, [
                'patient_id' => ['required', 'integer', 'exists:patients,id'],
                'therapy_id' => ['required', 'integer', 'exists:therapies,id'],
                'therapist_id' => ['nullable', 'integer', 'exists:therapists,id'],
                'billing_type' => ['required', Rule::in(['monthly', 'session'])],
                'fee' => ['required', 'numeric', 'min:0'],
                'start_date' => ['nullable', 'date'],
                'end_date' => ['nullable', 'date'],
                'status' => ['nullable', Rule::in(['active', 'completed', 'cancelled'])],
            ]);

            $row = PatientTherapy::create($request->only([
                'patient_id',
                'therapy_id',
                'therapist_id',
                'billing_type',
                'fee',
                'start_date',
                'end_date',
                'status',
            ]));

            $row->load(['patient', 'therapy', 'therapist']);

            return ApiResponse::success($row, 'Patient therapy assigned', 201);
        } catch (ValidationException $e) {
            return ApiResponse::error('Validation failed', 422, $e->errors());
        }
    }

    public function update(Request $request, $id)
    {
        $row = PatientTherapy::find($id);
        if (! $row) {
            return ApiResponse::error('Patient therapy not found', 404);
        }

        try {
            $this->validate($request, [
                'therapist_id' => ['sometimes', 'nullable', 'integer', 'exists:therapists,id'],
                'billing_type' => ['sometimes', 'required', Rule::in(['monthly', 'session'])],
                'fee' => ['sometimes', 'required', 'numeric', 'min:0'],
                'start_date' => ['sometimes', 'nullable', 'date'],
                'end_date' => ['sometimes', 'nullable', 'date'],
                'status' => ['sometimes', 'required', Rule::in(['active', 'completed', 'cancelled'])],
            ]);

            $row->fill($request->only([
                'therapist_id',
                'billing_type',
                'fee',
                'start_date',
                'end_date',
                'status',
            ]));
            $row->save();
            $row->load(['patient', 'therapy', 'therapist']);

            return ApiResponse::success($row, 'Patient therapy updated');
        } catch (ValidationException $e) {
            return ApiResponse::error('Validation failed', 422, $e->errors());
        }
    }

    public function destroy($id)
    {
        $row = PatientTherapy::find($id);
        if (! $row) {
            return ApiResponse::error('Patient therapy not found', 404);
        }

        $row->delete();
        return ApiResponse::success(null, 'Patient therapy removed');
    }
}

