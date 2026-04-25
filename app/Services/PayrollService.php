<?php

namespace App\Services;

use App\Helpers\Money;
use App\Models\SalaryModel;
use App\Models\Therapist;
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

                $payroll = TherapistPayroll::query()->updateOrCreate(
                    ['therapist_id' => $therapist->id, 'month' => $start->toDateString()],
                    [
                        'total_sessions' => $totalSessions,
                        'overtime_sessions' => $overtimeSessions,
                        'salary_amount' => $salaryAmount,
                    ]
                );

                $rows[] = $payroll;
            }
        });

        return $rows;
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

