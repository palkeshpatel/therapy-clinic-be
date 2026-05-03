<?php

namespace App\Services;

use App\Helpers\Money;
use App\Models\Holiday;
use App\Models\SalaryModel;
use App\Models\Therapist;
use App\Models\TherapistAttendance;
use App\Models\TherapistLeave;
use App\Models\TherapistPayroll;
use App\Models\TherapySession;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class PayrollService
{
    /**
     * Generate or update payroll rows for a given month (YYYY-MM).
     */
    public function generateForMonth(string $month): array
    {
        $start = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        $end = $start->copy()->endOfMonth();

        $therapists = Therapist::query()->where('status', 'active')->get();
        $rows = [];

        DB::transaction(function () use ($therapists, $start, $end, &$rows) {
            foreach ($therapists as $therapist) {
                $salaryModel = SalaryModel::query()
                    ->where('therapist_id', $therapist->id)
                    ->where(function ($q) use ($start) {
                        $q->whereNull('effective_from')
                            ->orWhereDate('effective_from', '<=', $start->toDateString());
                    })
                    ->orderByRaw('effective_from is null')
                    ->orderByDesc('effective_from')
                    ->orderByDesc('id')
                    ->first();

                $totalSessions = TherapySession::query()
                    ->where('therapist_id', $therapist->id)
                    ->whereBetween('session_date', [$start->toDateString(), $end->toDateString()])
                    ->where('status', 'completed')
                    ->count();

                $overtimeSessions = 0;
                $salaryAmount = $this->calculateSalary($salaryModel, $totalSessions, $overtimeSessions);
                $attendanceStats = $this->attendanceStats($therapist->id, $start, $end);
                $workingDays = max(1, $attendanceStats['working_days']);
                $salaryCents = Money::toCents($salaryAmount);
                $dailyRateCents = intdiv($salaryCents, $workingDays);
                $deductionAmount = Money::fromCents($dailyRateCents * $attendanceStats['absent_days']);
                $netSalary = Money::fromCents(max(0, $salaryCents - Money::toCents($deductionAmount)));

                $payroll = TherapistPayroll::query()->updateOrCreate(
                    ['therapist_id' => $therapist->id, 'month' => $start->toDateString()],
                    [
                        'present_days' => $attendanceStats['present_days'],
                        'leave_days' => $attendanceStats['leave_days'],
                        'holiday_days' => $attendanceStats['holiday_days'],
                        'absent_days' => $attendanceStats['absent_days'],
                        'total_sessions' => $totalSessions,
                        'overtime_sessions' => $overtimeSessions,
                        'salary_amount' => $salaryAmount,
                        'deduction_amount' => $deductionAmount,
                        'net_salary' => $netSalary,
                    ]
                );

                $rows[] = $payroll;
            }
        });

        return $rows;
    }

    private function attendanceStats(int $therapistId, Carbon $start, Carbon $end): array
    {
        $holidayCount = Holiday::query()
            ->where('status', 'active')
            ->whereBetween('holiday_date', [$start->toDateString(), $end->toDateString()])
            ->count();

        $presentDays = TherapistAttendance::query()
            ->where('therapist_id', $therapistId)
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->whereNotNull('check_in')
            ->count();

        $leaveDays = TherapistLeave::query()
            ->where('therapist_id', $therapistId)
            ->where('status', 'approved')
            ->whereBetween('leave_date', [$start->toDateString(), $end->toDateString()])
            ->count();

        $workingDays = collect(\Illuminate\Support\CarbonPeriod::create($start, $end))
            ->filter(fn ($date) => $date->isWeekday())
            ->count() - $holidayCount;

        $workingDays = max(0, $workingDays);
        $absentDays = max(0, $workingDays - $presentDays - $leaveDays);

        return [
            'present_days' => $presentDays,
            'leave_days' => $leaveDays,
            'holiday_days' => $holidayCount,
            'absent_days' => $absentDays,
            'working_days' => $workingDays,
        ];
    }

    private function calculateSalary(?SalaryModel $model, int $totalSessions, int $overtimeSessions): string
    {
        if (! $model) {
            return '0.00';
        }

        $fixed = (string) ($model->fixed_salary ?? '0.00');
        $rate = (string) ($model->per_session_rate ?? '0.00');

        $sessionCount = $totalSessions + $overtimeSessions;
        $sessionPay = Money::mul($rate, $sessionCount);

        return match ($model->salary_type) {
            'fixed' => $fixed,
            'per_session' => $sessionPay,
            'hybrid' => Money::add($fixed, $sessionPay),
            default => $fixed,
        };
    }
}

