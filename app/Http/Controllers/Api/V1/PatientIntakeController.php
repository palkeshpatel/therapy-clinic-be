<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Patient;
use App\Models\PatientIntake;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PatientIntakeController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) ($request->input('per_page', 15));
        $perPage = max(1, min(100, $perPage));

        $query = PatientIntake::query()
            ->with(['patient:id,patient_name,phone,gender']);

        if ($search = trim((string) $request->input('search', ''))) {
            $query->whereHas('patient', function ($q) use ($search) {
                $q->where('patient_name', 'like', "%{$search}%");
            })->orWhere('child_name', 'like', "%{$search}%");
        }

        if ($status = trim((string) $request->input('status', ''))) {
            $query->where('status', $status);
        }

        $sortBy = (string) $request->input('sort_by', 'created_at');
        $sortDir = strtolower((string) $request->input('sort_dir', 'desc')) === 'asc' ? 'asc' : 'desc';
        $allowedSort = ['id', 'date_of_assessment', 'status', 'created_at', 'updated_at'];
        if (! in_array($sortBy, $allowedSort, true)) {
            $sortBy = 'created_at';
        }
        $query->orderBy($sortBy, $sortDir);

        return ApiResponse::paginate($query->paginate($perPage), 'OK');
    }

    public function show($id)
    {
        $intake = PatientIntake::with(['patient'])->find($id);

        if (! $intake) {
            return ApiResponse::error('Intake form not found', 404);
        }

        return ApiResponse::success($intake);
    }

    public function store(Request $request)
    {
        try {
            $rules = [
                'patient_id' => ['required', 'integer', 'exists:patients,id'],
                'date_of_assessment' => ['nullable', 'date'],
                'status' => ['nullable', Rule::in(['draft', 'completed'])],
            ];

            $this->validate($request, $rules);

            $patient = Patient::findOrFail($request->input('patient_id'));

            // Pre-populate demographics from patient record
            $intakeData = [
                'patient_id' => $patient->id,
                'date_of_assessment' => $request->input('date_of_assessment') ?? Carbon::now()->toDateString(),
                'status' => $request->input('status', 'draft'),
                'child_name' => $patient->patient_name,
                'dob' => $patient->dob ? $patient->dob->toDateString() : null,
                'gender' => $patient->gender,
                'address' => $patient->address,
                'email' => $patient->email,
                'father_name' => $patient->guardian_name,
                'father_phone' => $patient->phone,
            ];

            $intake = PatientIntake::create($intakeData);

            return ApiResponse::success($intake, 'Intake form created successfully', 201);
        } catch (ValidationException $e) {
            return ApiResponse::error('Validation failed', 422, $e->errors());
        }
    }

    public function update(Request $request, $id)
    {
        $intake = PatientIntake::find($id);

        if (! $intake) {
            return ApiResponse::error('Intake form not found', 404);
        }

        try {
            // Validate basic metadata. Since the intake form is flat, we allow modifying any of the fields.
            $rules = [
                'patient_id' => ['nullable', 'integer', 'exists:patients,id'],
                'date_of_assessment' => ['nullable', 'date'],
                'status' => ['nullable', Rule::in(['draft', 'completed'])],
                'age' => ['nullable', 'integer', 'min:0'],
                'email' => ['nullable', 'email', 'max:150'],
                'natal_mother_age' => ['nullable', 'integer', 'min:0'],
            ];

            $this->validate($request, $rules);

            // Fetch all request inputs except system columns that shouldn't change easily unless needed
            $updateData = $request->except(['id', 'created_at', 'updated_at']);

            // Eloquent automatically handles JSON serialization because of model casting
            $intake->update($updateData);

            return ApiResponse::success($intake, 'Intake form updated successfully');
        } catch (ValidationException $e) {
            return ApiResponse::error('Validation failed', 422, $e->errors());
        }
    }

    public function destroy($id)
    {
        $intake = PatientIntake::find($id);

        if (! $intake) {
            return ApiResponse::error('Intake form not found', 404);
        }

        $intake->delete();

        return ApiResponse::success(null, 'Intake form deleted successfully');
    }
}
