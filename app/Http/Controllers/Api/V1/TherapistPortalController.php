<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\DailySchedule;
use App\Models\Therapist;
use App\Models\TherapistAttendance;
use App\Models\TherapistLeave;
use App\Models\TherapistSchedule;
use App\Models\TherapySession;
use App\Models\TimeSlot;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class TherapistPortalController extends Controller
{
    public function attendanceIndex(Request $request)
    {
        $therapist = $this->requireTherapist();
        if (! $therapist) {
            return ApiResponse::error('Therapist profile not found', 404);
        }

        $perPage = (int) ($request->input('per_page', 15));
        $perPage = max(1, min(100, $perPage));

        $query = TherapistAttendance::query()
            ->with('therapist')
            ->where('therapist_id', $therapist->id);

        if ($month = $request->input('month')) {
            $start = Carbon::createFromFormat('Y-m', (string) $month)->startOfMonth()->toDateString();
            $end = Carbon::createFromFormat('Y-m', (string) $month)->endOfMonth()->toDateString();
            $query->whereBetween('date', [$start, $end]);
        }
        if ($from = $request->input('from')) {
            $query->whereDate('date', '>=', $from);
        }
        if ($to = $request->input('to')) {
            $query->whereDate('date', '<=', $to);
        }

        $query->orderByDesc('date')->orderByDesc('id');
        return ApiResponse::paginate($query->paginate($perPage), 'OK');
    }

    public function attendanceToday()
    {
        $therapist = $this->requireTherapist();
        if (! $therapist) {
            return ApiResponse::error('Therapist profile not found', 404);
        }

        $today = Carbon::today()->toDateString();
        $rows = TherapistAttendance::query()
            ->with('therapist')
            ->where('therapist_id', $therapist->id)
            ->whereDate('date', $today)
            ->orderByDesc('id')
            ->get();

        return ApiResponse::success($rows, 'OK');
    }

    public function attendanceCheckIn()
    {
        $therapist = $this->requireTherapist();
        if (! $therapist) {
            return ApiResponse::error('Therapist profile not found', 404);
        }

        $today = Carbon::today()->toDateString();
        $row = TherapistAttendance::query()->firstOrNew([
            'therapist_id' => $therapist->id,
            'date' => $today,
        ]);

        if (! $row->check_in) {
            $row->check_in = Carbon::now();
        }
        $row->save();
        $row->load('therapist');

        return ApiResponse::success($row, 'Checked in');
    }

    public function attendanceCheckOut()
    {
        $therapist = $this->requireTherapist();
        if (! $therapist) {
            return ApiResponse::error('Therapist profile not found', 404);
        }

        $today = Carbon::today()->toDateString();
        $row = TherapistAttendance::query()->firstOrNew([
            'therapist_id' => $therapist->id,
            'date' => $today,
        ]);

        if (! $row->check_in) {
            $row->check_in = Carbon::now();
        }
        $row->check_out = Carbon::now();
        $row->save();
        $row->load('therapist');

        return ApiResponse::success($row, 'Checked out');
    }

    public function leavesIndex(Request $request)
    {
        $therapist = $this->requireTherapist();
        if (! $therapist) {
            return ApiResponse::error('Therapist profile not found', 404);
        }

        $perPage = (int) ($request->input('per_page', 15));
        $perPage = max(1, min(100, $perPage));

        $query = TherapistLeave::query()
            ->with('therapist')
            ->where('therapist_id', $therapist->id);

        if ($month = $request->input('month')) {
            $start = Carbon::createFromFormat('Y-m', (string) $month)->startOfMonth()->toDateString();
            $end = Carbon::createFromFormat('Y-m', (string) $month)->endOfMonth()->toDateString();
            $query->whereBetween('leave_date', [$start, $end]);
        }
        if ($from = $request->input('from')) {
            $query->whereDate('leave_date', '>=', $from);
        }
        if ($to = $request->input('to')) {
            $query->whereDate('leave_date', '<=', $to);
        }
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $query->orderByDesc('leave_date')->orderByDesc('id');
        return ApiResponse::paginate($query->paginate($perPage), 'OK');
    }

    public function leavesStore(Request $request)
    {
        $therapist = $this->requireTherapist();
        if (! $therapist) {
            return ApiResponse::error('Therapist profile not found', 404);
        }

        try {
            $this->validate($request, [
                'leave_date' => ['required', 'date'],
                'leave_type' => ['required', 'string', 'max:50'],
                'reason' => ['nullable', 'string'],
            ]);

            $leave = TherapistLeave::create([
                'therapist_id' => $therapist->id,
                'leave_date' => $request->input('leave_date'),
                'leave_type' => (string) $request->input('leave_type'),
                'reason' => $request->input('reason'),
                'status' => 'pending',
            ]);
            $leave->load('therapist');

            return ApiResponse::success($leave, 'Leave applied', 201);
        } catch (ValidationException $e) {
            return ApiResponse::error('Validation failed', 422, $e->errors());
        }
    }

    public function leavesDestroy($id)
    {
        $therapist = $this->requireTherapist();
        if (! $therapist) {
            return ApiResponse::error('Therapist profile not found', 404);
        }

        $leave = TherapistLeave::query()
            ->where('therapist_id', $therapist->id)
            ->find($id);
        if (! $leave) {
            return ApiResponse::error('Leave not found', 404);
        }
        if ((string) $leave->status !== 'pending') {
            return ApiResponse::error('Only pending leave can be cancelled', 422);
        }

        $leave->delete();
        return ApiResponse::success(null, 'Leave cancelled');
    }

    public function schedulingDaily(Request $request)
    {
        $therapist = $this->requireTherapist();
        if (! $therapist) {
            return ApiResponse::error('Therapist profile not found', 404);
        }

        $date = (string) $request->input('date', Carbon::today()->toDateString());

        $rows = DailySchedule::query()
            ->with(['slot', 'patient', 'therapist', 'therapy'])
            ->whereDate('date', $date)
            ->where('therapist_id', $therapist->id)
            ->orderBy('slot_id')
            ->get();

        $rows = $this->attachSessionTimes($rows, $date, $therapist->id);

        return ApiResponse::success($rows, 'OK');
    }

    public function schedulingUpdate(Request $request, $id)
    {
        $therapist = $this->requireTherapist();
        if (! $therapist) {
            return ApiResponse::error('Therapist profile not found', 404);
        }

        $row = DailySchedule::find($id);
        if (! $row) {
            return ApiResponse::error('Booking not found', 404);
        }
        if ((int) $row->therapist_id !== (int) $therapist->id) {
            return ApiResponse::error('Forbidden', 403);
        }

        try {
            $this->validate($request, [
                'status' => ['required', Rule::in(['in_progress', 'completed'])],
            ]);

            $next = (string) $request->input('status');
            if ($next === 'in_progress' && $row->status !== 'scheduled') {
                return ApiResponse::error('Only scheduled bookings can be started', 422);
            }
            if ($next === 'completed' && ! in_array($row->status, ['scheduled', 'in_progress', 'completed'], true)) {
                return ApiResponse::error('Invalid status transition', 422);
            }

            $row->status = $next;
            $row->save();
            $row->load(['slot', 'patient', 'therapist', 'therapy']);

            return ApiResponse::success($row, 'Updated');
        } catch (ValidationException $e) {
            return ApiResponse::error('Validation failed', 422, $e->errors());
        }
    }

    public function schedulingAvailability(Request $request)
    {
        $therapist = $this->requireTherapist();
        if (! $therapist) {
            return ApiResponse::error('Therapist profile not found', 404);
        }

        try {
            $this->validate($request, [
                'date' => ['required', 'date'],
            ]);

            $date = (string) $request->input('date');
            $therapistId = (int) $therapist->id;

            $allSlots = TimeSlot::query()->where('is_active', true)->orderBy('start_time')->get();
            $blocked = TherapistSchedule::query()
                ->where('therapist_id', $therapistId)
                ->whereDate('date', $date)
                ->whereIn('status', ['busy', 'leave'])
                ->pluck('slot_id')
                ->all();
            $booked = DailySchedule::query()
                ->where('therapist_id', $therapistId)
                ->whereDate('date', $date)
                ->whereIn('status', ['scheduled', 'in_progress', 'completed'])
                ->pluck('slot_id')
                ->all();

            $unavailable = array_unique(array_merge($blocked, $booked));
            $freeSlots = $allSlots->filter(fn ($s) => ! in_array($s->id, $unavailable, true))->values();

            return ApiResponse::success($freeSlots, 'OK');
        } catch (ValidationException $e) {
            return ApiResponse::error('Validation failed', 422, $e->errors());
        }
    }

    public function sessionsIndex(Request $request)
    {
        $therapist = $this->requireTherapist();
        if (! $therapist) {
            return ApiResponse::error('Therapist profile not found', 404);
        }

        $perPage = (int) ($request->input('per_page', 15));
        $perPage = max(1, min(100, $perPage));

        $query = TherapySession::query()
            ->with(['patient', 'therapist', 'therapy', 'slot'])
            ->where('therapist_id', $therapist->id);

        if ($patientId = $request->input('patient_id')) {
            $query->where('patient_id', $patientId);
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

    public function sessionsShow($id)
    {
        $therapist = $this->requireTherapist();
        if (! $therapist) {
            return ApiResponse::error('Therapist profile not found', 404);
        }

        $row = TherapySession::query()
            ->with(['patient', 'therapist', 'therapy', 'slot'])
            ->where('therapist_id', $therapist->id)
            ->find($id);
        if (! $row) {
            return ApiResponse::error('Session not found', 404);
        }

        return ApiResponse::success($row, 'OK');
    }

    public function sessionsStore(Request $request)
    {
        $therapist = $this->requireTherapist();
        if (! $therapist) {
            return ApiResponse::error('Therapist profile not found', 404);
        }

        try {
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
            ]) + ['therapist_id' => $therapist->id]);

            $this->syncDailyScheduleStatus($row);
            $row->load(['patient', 'therapist', 'therapy', 'slot']);

            return ApiResponse::success($row, 'Session created', 201);
        } catch (ValidationException $e) {
            return ApiResponse::error('Validation failed', 422, $e->errors());
        }
    }

    private function requireTherapist(): ?Therapist
    {
        $user = Auth::user();
        if (! $user) {
            return null;
        }

        return Therapist::query()->where('user_id', $user->id)->first();
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

        if ($session->status === 'completed') {
            $booking->status = 'completed';
            $booking->save();
        } elseif ($session->status === 'cancelled') {
            $booking->status = 'cancelled';
            $booking->save();
        }
    }

    private function attachSessionTimes($rows, string $date, int $therapistId)
    {
        $sessions = TherapySession::query()
            ->whereDate('session_date', $date)
            ->where('therapist_id', $therapistId)
            ->get()
            ->groupBy(fn ($session) => $session->therapist_id.'-'.$session->slot_id);

        return $rows->map(function ($row) use ($sessions) {
            $session = $sessions->get($row->therapist_id.'-'.$row->slot_id)?->first();
            $row->setAttribute('start_time', $session->start_time ?? null);
            $row->setAttribute('end_time', $session->end_time ?? null);
            $row->setAttribute('duration', $session->duration ?? null);
            return $row;
        });
    }
}
