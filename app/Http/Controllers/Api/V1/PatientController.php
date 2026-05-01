<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Patient;
use Illuminate\Support\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PatientController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) ($request->input('per_page', 15));
        $perPage = max(1, min(100, $perPage));

        $therapyPick = '(SELECT th.therapy_name FROM patient_therapies pt '
            .'INNER JOIN therapies th ON th.id = pt.therapy_id '
            .'WHERE pt.patient_id = patients.id ORDER BY pt.id ASC LIMIT 1)';

        $query = Patient::query()
            ->select('patients.*')
            ->selectRaw('('.$therapyPick.') as primary_therapy_name')
            ->withCount(['sessions as sessions_count'])
            ->withMax('sessions as last_session_date', 'session_date');

        if ($search = trim((string) $request->input('search', ''))) {
            $query->where(function ($q) use ($search) {
                $q->where('patient_name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $allowedStatuses = ['active', 'inactive', 'discharged'];
        if ($request->filled('status')) {
            $raw = $request->input('status');
            $statuses = is_array($raw)
                ? $raw
                : array_filter(array_map('trim', explode(',', (string) $raw)));
            $statuses = array_values(array_intersect($statuses, $allowedStatuses));
            if (count($statuses) > 0) {
                $query->whereIn('status', $statuses);
            }
        }

        $sortBy = (string) $request->input('sort_by', 'created_at');
        $sortDir = strtolower((string) $request->input('sort_dir', 'desc')) === 'asc' ? 'asc' : 'desc';
        $allowedSort = ['id', 'patient_name', 'phone', 'status', 'created_at'];
        if (! in_array($sortBy, $allowedSort, true)) {
            $sortBy = 'created_at';
        }
        $query->orderBy($sortBy, $sortDir);

        $extraMeta = [];
        if ($request->boolean('include_stats')) {
            $extraMeta['stats'] = [
                'total' => Patient::query()->count(),
                'active' => Patient::query()->where('status', 'active')->count(),
                'inactive' => Patient::query()->where('status', 'inactive')->count(),
                'discharged' => Patient::query()->where('status', 'discharged')->count(),
                'new_this_month' => Patient::query()
                    ->whereYear('created_at', Carbon::now()->year)
                    ->whereMonth('created_at', Carbon::now()->month)
                    ->count(),
                'on_hold' => Patient::query()->where('status', 'inactive')->count(),
            ];
        }

        return ApiResponse::paginate($query->paginate($perPage), 'OK', $extraMeta);
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
                'status' => ['nullable', Rule::in(['active', 'inactive', 'discharged'])],
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
                'status' => ['sometimes', 'required', Rule::in(['active', 'inactive', 'discharged'])],
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

    public function sessions($id)
    {
        $patient = Patient::find($id);
        if (!$patient) {
            return ApiResponse::error('Patient not found', 404);
        }
        $sessions = \App\Models\TherapySession::query()
            ->with(['therapist', 'therapy', 'slot'])
            ->where('patient_id', $id)
            ->orderByDesc('session_date')
            ->paginate(15);
        return ApiResponse::paginate($sessions, 'OK');
    }

    public function invoices($id)
    {
        $patient = Patient::find($id);
        if (!$patient) {
            return ApiResponse::error('Patient not found', 404);
        }
        $invoices = \App\Models\Invoice::query()
            ->with(['items', 'payments'])
            ->where('patient_id', $id)
            ->orderByDesc('invoice_date')
            ->paginate(15);
        return ApiResponse::paginate($invoices, 'OK');
    }
}
