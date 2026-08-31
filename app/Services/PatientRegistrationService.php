<?php

namespace App\Services;

use App\Models\DailySchedule;
use App\Models\Patient;
use App\Models\PatientTherapy;
use App\Models\Therapy;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PatientRegistrationService
{
    /**
     * @param  array<string, mixed>  $patientData
     * @param  array<int, array<string, mixed>>  $therapyRows
     * @return array{patient: Patient, fee_summary: array<string, mixed>}
     */
    public function register(array $patientData, array $therapyRows, string $billingType): array
    {
        return DB::transaction(function () use ($patientData, $therapyRows, $billingType) {
            $patient = Patient::create($patientData);

            $therapyIds = [];
            $totalMonthly = 0.0;
            $totalSession = 0.0;
            $totalApplied = 0.0;
            $startDate = $patientData['joining_date'] ?? now()->toDateString();

            foreach ($therapyRows as $index => $row) {
                $therapyId = (int) $row['therapy_id'];

                $therapy = Therapy::query()->find($therapyId);
                if (! $therapy || $therapy->status !== 'active') {
                    throw ValidationException::withMessages([
                        "therapies.{$index}.therapy_id" => ['Selected therapy is not available.'],
                    ]);
                }

                $sessionFee  = (float) $therapy->session_price;
                $monthlyFee  = (float) $therapy->fixed_price;
                $rowBillingType = $row['billing_type'] ?? $billingType;
                $rowFee      = isset($row['fee'])
                    ? (float) $row['fee']
                    : ($rowBillingType === 'monthly' ? $monthlyFee : $sessionFee);

                $therapistId  = !empty($row['therapist_id'])   ? (int) $row['therapist_id']  : null;
                $slotId       = !empty($row['slot_id'])        ? (int) $row['slot_id']       : null;
                $scheduleDate = !empty($row['schedule_date'])  ? $row['schedule_date']        : null;

                $totalSessions = isset($row['total_sessions']) ? (int) $row['total_sessions'] : null;

                PatientTherapy::create([
                    'patient_id'     => $patient->id,
                    'therapy_id'     => $therapyId,
                    'therapist_id'   => $therapistId,
                    'billing_type'   => $rowBillingType,
                    'fee'            => $rowFee,
                    'number_of_days' => $row['number_of_days'] ?? null,
                    'total_sessions' => $totalSessions,
                    'start_date'     => $row['start_date'] ?? $startDate,
                    'status'         => 'active',
                ]);

                // ── Optionally create multiple DailySchedule bookings ──────────
                $schedules = $row['schedules'] ?? [];
                
                if (!empty($schedules) && $therapistId) {
                    foreach ($schedules as $scheduleIndex => $sched) {
                        $scheduleDate = $sched['date'] ?? null;
                        $slotId = isset($sched['slot_id']) ? (int) $sched['slot_id'] : null;

                        if (!$scheduleDate || !$slotId) {
                            continue;
                        }


                        DailySchedule::updateOrCreate(
                            [
                                'date'         => $scheduleDate,
                                'slot_id'      => $slotId,
                                'patient_id'   => $patient->id,
                                'therapist_id' => $therapistId,
                            ],
                            [
                                'therapy_id'   => $therapyId,
                                'status'       => 'scheduled',
                                'created_by'   => Auth::id(),
                            ]
                        );
                    }
                }

                $totalMonthly += ($rowBillingType === 'monthly' ? $rowFee : 0);
                $totalSession += ($rowBillingType === 'session' ? $rowFee : 0);
                $totalApplied += $rowFee;
            }

            $patient->load(['therapies.therapy', 'therapies.therapist']);

            return [
                'patient'     => $patient,
                'fee_summary' => [
                    'billing_type'       => $billingType,
                    'total_monthly'      => round($totalMonthly, 2),
                    'total_session_rate' => round($totalSession, 2),
                    'total_applied'      => round($totalApplied, 2),
                    'therapy_count'      => count($therapyRows),
                ],
            ];
        });
    }
}
