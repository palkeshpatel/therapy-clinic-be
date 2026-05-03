<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Holiday;
use App\Models\Therapist;
use App\Models\TherapistAttendance;
use App\Models\TherapistLeave;
use Illuminate\Http\Request;
use Illuminate\Support\CarbonPeriod;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) ($request->input('per_page', 15));
        $perPage = max(1, min(100, $perPage));

        $query = TherapistAttendance::query()->with('therapist');
        $myTherapistId = $this->myTherapistIdIfTherapist();
        if ($myTherapistId) {
            $query->where('therapist_id', $myTherapistId);
        }

        if ($month = $request->input('month')) {
            $start = Carbon::createFromFormat('Y-m', (string) $month)->startOfMonth()->toDateString();
            $end = Carbon::createFromFormat('Y-m', (string) $month)->endOfMonth()->toDateString();
            $query->whereBetween('date', [$start, $end]);
        }

        if (! $myTherapistId && ($therapistId = $request->input('therapist_id'))) {
            $query->where('therapist_id', $therapistId);
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

    public function today()
    {
        $today = Carbon::today()->toDateString();

        $rows = TherapistAttendance::query()
            ->with('therapist')
            ->whereDate('date', $today)
            ->when($this->myTherapistIdIfTherapist(), fn ($q, $id) => $q->where('therapist_id', $id))
            ->orderBy('therapist_id')
            ->get();

        return ApiResponse::success($rows, 'OK');
    }

    public function summary(Request $request)
    {
        $month = (string) $request->input('month', Carbon::now()->format('Y-m'));
        $start = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        $end = $start->copy()->endOfMonth();

        $holidays = Holiday::query()
            ->where('status', 'active')
            ->whereBetween('holiday_date', [$start->toDateString(), $end->toDateString()])
            ->orderBy('holiday_date')
            ->get();

        $holidayDates = $holidays->pluck('holiday_date')->map(fn ($date) => Carbon::parse($date)->toDateString())->all();
        $businessDays = collect(CarbonPeriod::create($start, $end))
            ->filter(fn ($date) => $date->isWeekday())
            ->count();

        $therapists = Therapist::query()
            ->where('status', 'active')
            ->with(['attendance' => function ($query) use ($start, $end) {
                $query->whereBetween('date', [$start->toDateString(), $end->toDateString()]);
            }, 'leaves' => function ($query) use ($start, $end) {
                $query->where('status', 'approved')->whereBetween('leave_date', [$start->toDateString(), $end->toDateString()]);
            }])
            ->orderBy('name')
            ->get()
            ->map(function ($therapist) use ($businessDays, $holidayDates) {
                $presentDays = $therapist->attendance->whereNotNull('check_in')->count();
                $leaveDays = $therapist->leaves->count();
                $holidayDays = count($holidayDates);
                $availableDays = max(0, $businessDays - $holidayDays);
                $absentDays = max(0, $availableDays - $presentDays - $leaveDays);

                return [
                    'id' => $therapist->id,
                    'name' => $therapist->name,
                    'specialization' => $therapist->specialization,
                    'present_days' => $presentDays,
                    'leave_days' => $leaveDays,
                    'holiday_days' => $holidayDays,
                    'absent_days' => $absentDays,
                    'working_days' => $availableDays,
                    'attendance' => $therapist->attendance->map(function ($row) {
                        return [
                            'id' => $row->id,
                            'date' => Carbon::parse($row->date)->toDateString(),
                            'check_in' => optional($row->check_in)?->toDateTimeString(),
                            'check_out' => optional($row->check_out)?->toDateTimeString(),
                            'status' => $row->check_out ? 'present' : ($row->check_in ? 'checked-in' : 'absent'),
                        ];
                    })->values(),
                ];
            });

        return ApiResponse::success([
            'month' => $month,
            'start' => $start->toDateString(),
            'end' => $end->toDateString(),
            'business_days' => $businessDays,
            'holiday_count' => $holidays->count(),
            'holidays' => $holidays,
            'therapists' => $therapists,
        ], 'OK');
    }

    public function checkIn(Request $request)
    {
        try {
            $myTherapistId = $this->myTherapistIdIfTherapist();
            if (! $myTherapistId) {
                $this->validate($request, [
                    'therapist_id' => ['required', 'integer', 'exists:therapists,id'],
                ]);
            }

            $today = Carbon::today()->toDateString();
            $therapistId = $myTherapistId ?: (int) $request->input('therapist_id');

            $row = TherapistAttendance::query()->firstOrNew([
                'therapist_id' => $therapistId,
                'date' => $today,
            ]);

            if (! $row->check_in) {
                $row->check_in = Carbon::now();
            }
            $row->save();
            $row->load('therapist');

            return ApiResponse::success($row, 'Checked in');
        } catch (ValidationException $e) {
            return ApiResponse::error('Validation failed', 422, $e->errors());
        }
    }

    public function checkOut(Request $request)
    {
        try {
            $myTherapistId = $this->myTherapistIdIfTherapist();
            if (! $myTherapistId) {
                $this->validate($request, [
                    'therapist_id' => ['required', 'integer', 'exists:therapists,id'],
                ]);
            }

            $today = Carbon::today()->toDateString();
            $therapistId = $myTherapistId ?: (int) $request->input('therapist_id');

            $row = TherapistAttendance::query()->firstOrNew([
                'therapist_id' => $therapistId,
                'date' => $today,
            ]);

            if (! $row->check_in) {
                $row->check_in = Carbon::now();
            }
            $row->check_out = Carbon::now();
            $row->save();
            $row->load('therapist');

            return ApiResponse::success($row, 'Checked out');
        } catch (ValidationException $e) {
            return ApiResponse::error('Validation failed', 422, $e->errors());
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

