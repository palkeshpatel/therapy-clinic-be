<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\DailySchedule;
use App\Models\TherapySession;
use App\Models\Therapist;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class SessionController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) ($request->input('per_page', 15));
        $perPage = max(1, min(100, $perPage));

        $query = TherapySession::query()->with(['patient', 'therapist', 'therapy', 'slot']);
        $myTherapistId = $this->myTherapistIdIfTherapist();
        if ($myTherapistId) {
            $query->where('therapist_id', $myTherapistId);
        }

        if ($patientId = $request->input('patient_id')) {
            $query->where('patient_id', $patientId);
        }
        if ($therapistId = $request->input('therapist_id')) {
            $query->where('therapist_id', $therapistId);
        }
        if ($therapyId = $request->input('therapy_id')) {
            $query->where('therapy_id', $therapyId);
        }
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }
        if ($from = $request->input('from')) {
            $query->whereDate('session_date', '>=', $from);
        }
        if ($to = $request->input('to')) {
            $query->whereDate('session_date', '<=', $to);
        }

        $query->orderByDesc('session_date')->orderByDesc('id');

        return ApiResponse::paginate($query->paginate($perPage), 'OK');
    }

    public function store(Request $request)
    {
        try {
            $myTherapistId = $this->myTherapistIdIfTherapist();
            if ($myTherapistId) {
                $this->validate($request, [
                    'patient_id' => ['required', 'integer', 'exists:patients,id'],
                    'therapy_id' => ['required', 'integer', 'exists:therapies,id'],
                    'slot_id' => ['nullable', 'integer', 'exists:time_slots,id'],
                    'session_date' => ['required', 'date'],
                    'start_time' => ['nullable', 'date_format:H:i'],
                    'end_time' => ['nullable', 'date_format:H:i'],
                    'duration' => ['nullable', 'string', 'max:20'],
                    'status' => ['required', Rule::in(['completed', 'absent', 'cancelled'])],
                    'notes' => ['nullable', 'string'],
                ]);
            } else {
                $this->validate($request, [
                    'patient_id' => ['required', 'integer', 'exists:patients,id'],
                    'therapist_id' => ['required', 'integer', 'exists:therapists,id'],
                    'therapy_id' => ['required', 'integer', 'exists:therapies,id'],
                    'slot_id' => ['nullable', 'integer', 'exists:time_slots,id'],
                    'session_date' => ['required', 'date'],
                    'start_time' => ['nullable', 'date_format:H:i'],
                    'end_time' => ['nullable', 'date_format:H:i'],
                    'duration' => ['nullable', 'string', 'max:20'],
                    'status' => ['required', Rule::in(['completed', 'absent', 'cancelled'])],
                    'notes' => ['nullable', 'string'],
                ]);
            }

            $therapistId = $myTherapistId ?: (int) $request->input('therapist_id');

            $row = TherapySession::create($request->only([
                'patient_id',
                'therapy_id',
                'slot_id',
                'session_date',
                'start_time',
                'end_time',
                'duration',
                'status',
                'notes',
            ]) + ['therapist_id' => $therapistId]);

            $this->syncDailyScheduleStatus($row);
            $row->load(['patient', 'therapist', 'therapy', 'slot']);

            return ApiResponse::success($row, 'Session created', 201);
        } catch (ValidationException $e) {
            return ApiResponse::error('Validation failed', 422, $e->errors());
        }
    }

    public function show($id)
    {
        $row = TherapySession::with(['patient', 'therapist', 'therapy', 'slot'])->find($id);
        if (! $row) {
            return ApiResponse::error('Session not found', 404);
        }
        $myTherapistId = $this->myTherapistIdIfTherapist();
        if ($myTherapistId && (int) $row->therapist_id !== (int) $myTherapistId) {
            return ApiResponse::error('Forbidden', 403);
        }
        return ApiResponse::success($row, 'OK');
    }

    public function update(Request $request, $id)
    {
        $row = TherapySession::find($id);
        if (! $row) {
            return ApiResponse::error('Session not found', 404);
        }

        try {
            $this->validate($request, [
                'patient_id' => ['sometimes', 'required', 'integer', 'exists:patients,id'],
                'therapist_id' => ['sometimes', 'required', 'integer', 'exists:therapists,id'],
                'therapy_id' => ['sometimes', 'required', 'integer', 'exists:therapies,id'],
                'slot_id' => ['sometimes', 'nullable', 'integer', 'exists:time_slots,id'],
                'session_date' => ['sometimes', 'required', 'date'],
                'start_time' => ['sometimes', 'nullable', 'date_format:H:i'],
                'end_time' => ['sometimes', 'nullable', 'date_format:H:i'],
                'duration' => ['sometimes', 'nullable', 'string', 'max:20'],
                'status' => ['sometimes', 'required', Rule::in(['completed', 'absent', 'cancelled'])],
                'notes' => ['sometimes', 'nullable', 'string'],
            ]);

            $row->fill($request->only([
                'patient_id',
                'therapist_id',
                'therapy_id',
                'slot_id',
                'session_date',
                'start_time',
                'end_time',
                'duration',
                'status',
                'notes',
            ]));
            $row->save();

            $this->syncDailyScheduleStatus($row);
            $row->load(['patient', 'therapist', 'therapy', 'slot']);

            return ApiResponse::success($row, 'Session updated');
        } catch (ValidationException $e) {
            return ApiResponse::error('Validation failed', 422, $e->errors());
        }
    }

    public function destroy($id)
    {
        $row = TherapySession::find($id);
        if (! $row) {
            return ApiResponse::error('Session not found', 404);
        }

        $row->delete();
        return ApiResponse::success(null, 'Session deleted');
    }

    private function syncDailyScheduleStatus(TherapySession $session): void
    {
        if (! $session->slot_id) {
            return;
        }

        $date = Carbon::parse($session->session_date)->toDateString();

        $booking = DailySchedule::query()
            ->whereDate('date', $date)
            ->where('slot_id', $session->slot_id)
            ->where('therapist_id', $session->therapist_id)
            ->first();

        if (! $booking) {
            return;
        }

        // Only map completed/cancelled. Absent keeps booking as scheduled (clinic can handle separately).
        if ($session->status === 'completed') {
            $booking->status = 'completed';
            $booking->save();
        } elseif ($session->status === 'cancelled') {
            $booking->status = 'cancelled';
            $booking->save();
        }
    }

    private function myTherapistIdIfTherapist(): ?int
    {
        $user = Auth::user();
        if (! $user) {
            return null;
        }
        $user->loadMissing('role');
        if (($user->role?->role_type ?? null) !== 'therapist') {
            return null;
        }

        return Therapist::query()->where('user_id', $user->id)->value('id');
    }
}

