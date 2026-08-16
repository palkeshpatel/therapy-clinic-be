<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Patient;
use App\Services\PatientRegistrationService;
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
            ->withCount(['sessions as sessions_count', 'therapies as therapies_count'])
            ->withMax('sessions as last_session_date', 'session_date')
            ->with(['therapies.therapy']);

        if ($search = trim((string) $request->input('search', ''))) {
            $query->where(function ($q) use ($search) {
                $q->where('patient_name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->boolean('without_intake')) {
            $query->doesntHave('intake');
        }

        if ($request->boolean('with_intake')) {
            $query->with(['intake' => function($q) {
                $q->select('id', 'patient_id', 'status');
            }]);
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

        $sortBy = (string) $request->input('sort_by', 'id');
        $sortDir = strtolower((string) $request->input('sort_dir', 'asc')) === 'desc' ? 'desc' : 'asc';
        $allowedSort = ['id', 'patient_name', 'phone', 'status', 'created_at'];
        if (! in_array($sortBy, $allowedSort, true)) {
            $sortBy = 'id';
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

    public function store(Request $request, PatientRegistrationService $registrationService)
    {
        try {
            $hasTherapies = $request->has('therapies');

            $rules = [
                'patient_name' => ['required', 'string', 'max:150'],
                'guardian_name' => ['nullable', 'string', 'max:150'],
                'phone' => ['required', 'string', 'max:20'],
                'email' => ['nullable', 'email', 'max:150'],
                'dob' => ['nullable', 'date'],
                'joining_date' => ['nullable', 'date'],
                'gender' => ['required', Rule::in(['male', 'female', 'other'])],
                'address' => ['nullable', 'string'],
                'notes' => ['nullable', 'string'],
                'status' => ['nullable', Rule::in(['active', 'inactive', 'discharged'])],
            ];

            if ($hasTherapies) {
                $rules['default_billing_type'] = ['nullable', Rule::in(['monthly', 'session'])];
                $rules['therapies'] = ['required', 'array', 'min:1'];
                $rules['therapies.*.therapy_id'] = ['required', 'integer', 'exists:therapies,id'];
                $rules['therapies.*.therapist_id'] = ['nullable', 'integer', 'exists:therapists,id'];
                $rules['therapies.*.billing_type'] = ['required', Rule::in(['monthly', 'session'])];
                $rules['therapies.*.fee'] = ['required', 'numeric', 'min:0'];
                $rules['therapies.*.start_date'] = ['nullable', 'date'];
                $rules['therapies.*.total_sessions'] = ['nullable', 'integer', 'min:1'];
                $rules['therapies.*.schedules'] = ['nullable', 'array'];
                $rules['therapies.*.schedules.*.date'] = ['required_with:therapies.*.schedules', 'date'];
                $rules['therapies.*.schedules.*.slot_id'] = ['required_with:therapies.*.schedules', 'integer', 'exists:time_slots,id'];
            } else {
                $rules['default_billing_type'] = ['nullable', Rule::in(['monthly', 'session'])];
            }

            $this->validate($request, $rules);

            $patientFields = $request->only([
                'patient_name',
                'guardian_name',
                'phone',
                'email',
                'dob',
                'joining_date',
                'gender',
                'address',
                'notes',
                'default_billing_type',
                'status',
            ]);

            if (! $hasTherapies) {
                $patient = Patient::create($patientFields);

                return ApiResponse::success($patient, 'Patient created', 201);
            }

            $billingType = (string) $request->input('default_billing_type', 'monthly');
            $patientFields['default_billing_type'] = $billingType;
            $patientFields['joining_date'] = $patientFields['joining_date'] ?? Carbon::now()->toDateString();

            $result = $registrationService->register(
                $patientFields,
                $request->input('therapies', []),
                $billingType
            );

            return ApiResponse::success(
                $result['patient'],
                'Patient created with therapies',
                201,
                ['fee_summary' => $result['fee_summary']]
            );
        } catch (ValidationException $e) {
            return ApiResponse::error('Validation failed', 422, $e->errors());
        }
    }

    public function show($id)
    {
        $patient = Patient::with(['medicalRecords', 'therapies.therapy', 'therapies.therapist'])->find($id);
        if (! $patient) {
            return ApiResponse::error('Patient not found', 404);
        }

        $schedules = \App\Models\DailySchedule::where('patient_id', $id)->get();
        
        $patientArray = $patient->toArray();
        if (isset($patientArray['therapies'])) {
            // Keep track of which schedules have been assigned to avoid duplicates
            // if the user has the exact same therapy and therapist in multiple cards
            $assignedScheduleIds = [];
            
            foreach ($patientArray['therapies'] as &$therapy) {
                $matchedSchedules = $schedules->filter(function ($schedule) use ($therapy, &$assignedScheduleIds) {
                    if (in_array($schedule->id, $assignedScheduleIds)) {
                        return false;
                    }
                    return $schedule->therapy_id == $therapy['therapy_id'] && 
                           $schedule->therapist_id == $therapy['therapist_id'];
                });
                
                // Group the matched schedules by slot_id.
                // Since a TherapyAssignmentCard in the frontend only supports ONE time slot,
                // we should only assign schedules that share the SAME slot_id to this card.
                // The remaining schedules for this therapy/therapist will be picked up by the next card.
                if ($matchedSchedules->isNotEmpty()) {
                    $firstSlotId = $matchedSchedules->first()->slot_id;
                    $schedulesForThisCard = $matchedSchedules->where('slot_id', $firstSlotId);
                    
                    foreach ($schedulesForThisCard as $s) {
                        $assignedScheduleIds[] = $s->id;
                    }
                    
                    $therapy['schedules'] = $schedulesForThisCard->values()->toArray();
                } else {
                    $therapy['schedules'] = [];
                }
            }
        }

        return ApiResponse::success($patientArray, 'OK');
    }

    public function update(Request $request, $id)
    {
        $patient = Patient::find($id);
        if (! $patient) {
            return ApiResponse::error('Patient not found', 404);
        }
        

        try {
            $hasTherapies = $request->has('therapies');

            $rules = [
                'patient_name' => ['sometimes', 'required', 'string', 'max:150'],
                'guardian_name' => ['sometimes', 'nullable', 'string', 'max:150'],
                'phone' => ['sometimes', 'required', 'string', 'max:20'],
                'email' => ['sometimes', 'nullable', 'email', 'max:150'],
                'dob' => ['sometimes', 'nullable', 'date'],
                'joining_date' => ['sometimes', 'nullable', 'date'],
                'gender' => ['sometimes', 'required', Rule::in(['male', 'female', 'other'])],
                'address' => ['sometimes', 'nullable', 'string'],
                'notes' => ['sometimes', 'nullable', 'string'],
                'default_billing_type' => ['sometimes', 'nullable', Rule::in(['monthly', 'session'])],
                'status' => ['sometimes', 'required', Rule::in(['active', 'inactive', 'discharged'])],
            ];

            if ($hasTherapies) {
                $rules['therapies'] = ['required', 'array', 'min:1'];
                $rules['therapies.*.therapy_id'] = ['required', 'integer', 'exists:therapies,id'];
                $rules['therapies.*.therapist_id'] = ['nullable', 'integer', 'exists:therapists,id'];
                $rules['therapies.*.billing_type'] = ['required', Rule::in(['monthly', 'session'])];
                $rules['therapies.*.fee'] = ['required', 'numeric', 'min:0'];
                $rules['therapies.*.start_date'] = ['nullable', 'date'];
                $rules['therapies.*.total_sessions'] = ['nullable', 'integer', 'min:1'];
                $rules['therapies.*.schedules'] = ['nullable', 'array'];
                $rules['therapies.*.schedules.*.date'] = ['required_with:therapies.*.schedules', 'date'];
                $rules['therapies.*.schedules.*.slot_id'] = ['required_with:therapies.*.schedules', 'integer', 'exists:time_slots,id'];
            }

            $this->validate($request, $rules);

            \DB::transaction(function () use ($patient, $request, $hasTherapies) {
                $patient->fill($request->only([
                    'patient_name',
                    'guardian_name',
                    'phone',
                    'email',
                    'dob',
                    'joining_date',
                    'gender',
                    'address',
                    'notes',
                    'default_billing_type',
                    'status',
                ]));
                $patient->save();

                if ($hasTherapies) {
                    \App\Models\PatientTherapy::where('patient_id', $patient->id)->delete();
                    \App\Models\DailySchedule::where('patient_id', $patient->id)->delete();

                    $therapyRows = $request->input('therapies', []);
                    $startDate = $patient->joining_date ?? Carbon::now()->toDateString();
                    $therapyIds = [];

                    foreach ($therapyRows as $index => $row) {
                        $therapyId = (int) $row['therapy_id'];

                        $therapy = \App\Models\Therapy::query()->find($therapyId);
                        if (! $therapy || $therapy->status !== 'active') {
                            throw ValidationException::withMessages([
                                "therapies.{$index}.therapy_id" => ['Selected therapy is not available.'],
                            ]);
                        }

                        $sessionFee = (float) $therapy->session_price;
                        $monthlyFee = (float) $therapy->fixed_price;

                        $rowBillingType = $row['billing_type'] ?? 'monthly';
                        $rowFee = isset($row['fee']) ? (float) $row['fee'] : ($rowBillingType === 'monthly' ? $monthlyFee : $sessionFee);
                        
                        $therapistId = !empty($row['therapist_id']) ? (int) $row['therapist_id'] : null;

                        \App\Models\PatientTherapy::create([
                            'patient_id' => $patient->id,
                            'therapy_id' => $therapyId,
                            'therapist_id' => $therapistId,
                            'billing_type' => $rowBillingType,
                            'fee' => $rowFee,
                            'total_sessions' => isset($row['total_sessions']) ? (int) $row['total_sessions'] : null,
                            'start_date' => $row['start_date'] ?? $startDate,
                            'status' => 'active',
                        ]);

                        $schedules = $row['schedules'] ?? [];
                        if (!empty($schedules) && $therapistId) {
                            foreach ($schedules as $scheduleIndex => $sched) {
                                $scheduleDate = $sched['date'] ?? null;
                                $slotId = isset($sched['slot_id']) ? (int) $sched['slot_id'] : null;

                                if (!$scheduleDate || !$slotId) {
                                    continue;
                                }


                                \App\Models\DailySchedule::updateOrCreate(
                                    [
                                        'date'         => $scheduleDate,
                                        'slot_id'      => $slotId,
                                        'patient_id'   => $patient->id,
                                        'therapist_id' => $therapistId,
                                    ],
                                    [
                                        'therapy_id'   => $therapyId,
                                        'status'       => 'scheduled',
                                        'created_by'   => \Illuminate\Support\Facades\Auth::id(),
                                    ]
                                );
                            }
                        }
                    }
                }
            });

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

    public function export(Request $request)
    {
        $query = Patient::query()->with(['therapies.therapy', 'therapies.therapist', 'dailySchedules.slot']);

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

        $query->orderBy('id', 'asc');

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="patients_export_' . date('Y-m-d_H-i') . '.csv"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($query) {
            $file = fopen('php://output', 'w');
            
            // CSV Headers
            fputcsv($file, [
                'ID',
                'Patient Name',
                'Guardian Name',
                'Phone',
                'Email',
                'Gender',
                'Status',
                'Joining Date',
                'Assigned Therapy',
                'Therapist',
                'Billing Type',
                'Fee',
                'Time Slot',
                'Scheduled Dates'
            ]);

            // Chunk to avoid memory limits on large datasets
            $query->chunk(200, function ($patients) use ($file) {
                foreach ($patients as $patient) {
                    $therapies = $patient->therapies->values();

                    if ($therapies->isEmpty()) {
                        fputcsv($file, [
                            $patient->id,
                            $patient->patient_name,
                            $patient->guardian_name,
                            $patient->phone,
                            $patient->email,
                            ucfirst($patient->gender ?? ''),
                            ucfirst($patient->status),
                            $patient->joining_date,
                            '', '', '', '', '', ''
                        ]);
                    } else {
                        foreach ($therapies as $index => $pt) {
                            $therapyName = $pt->therapy ? $pt->therapy->therapy_name : '';
                            $therapistName = $pt->therapist ? $pt->therapist->name : '';
                            $billingType = $pt->billing_type ? ucfirst($pt->billing_type) : '';
                            $fee = $pt->fee ?? '';

                            $schedules = $patient->dailySchedules->filter(function($s) use ($pt) {
                                return $s->therapy_id == $pt->therapy_id && $s->therapist_id == $pt->therapist_id;
                            });

                            $timeSlot = '';
                            $datesStr = '';
                            if ($schedules->isNotEmpty()) {
                                $firstSlot = $schedules->first()->slot;
                                if ($firstSlot) {
                                    $timeSlot = \Carbon\Carbon::parse($firstSlot->start_time)->format('g:i A') . ' - ' . \Carbon\Carbon::parse($firstSlot->end_time)->format('g:i A');
                                }
                                $dates = $schedules->pluck('date')->map(function($d) {
                                    return \Carbon\Carbon::parse($d)->format('M d, Y');
                                })->implode(', ');
                                $datesStr = $schedules->count() . ' dates: ' . $dates;
                            }

                            if ($index === 0) {
                                fputcsv($file, [
                                    $patient->id,
                                    $patient->patient_name,
                                    $patient->guardian_name,
                                    $patient->phone,
                                    $patient->email,
                                    ucfirst($patient->gender ?? ''),
                                    ucfirst($patient->status),
                                    $patient->joining_date,
                                    $therapyName,
                                    $therapistName,
                                    $billingType,
                                    $fee,
                                    $timeSlot,
                                    $datesStr
                                ]);
                            } else {
                                fputcsv($file, [
                                    '', '', '', '', '', '', '', '',
                                    $therapyName,
                                    $therapistName,
                                    $billingType,
                                    $fee,
                                    $timeSlot,
                                    $datesStr
                                ]);
                            }
                        }
                    }
                }
            });

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
