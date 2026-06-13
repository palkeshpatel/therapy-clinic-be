<?php

namespace App\Services;

use App\Models\Patient;
use App\Models\PatientTherapy;
use App\Models\Therapy;
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

                if (in_array($therapyId, $therapyIds, true)) {
                    throw ValidationException::withMessages([
                        "therapies.{$index}.therapy_id" => ['Each therapy can only be added once.'],
                    ]);
                }
                $therapyIds[] = $therapyId;

                $therapy = Therapy::query()->find($therapyId);
                if (! $therapy || $therapy->status !== 'active') {
                    throw ValidationException::withMessages([
                        "therapies.{$index}.therapy_id" => ['Selected therapy is not available.'],
                    ]);
                }

                $sessionFee = (float) $therapy->session_price;
                $monthlyFee = (float) $therapy->fixed_price;
                
                $rowBillingType = $row['billing_type'] ?? $billingType;
                $rowFee = isset($row['fee']) ? (float) $row['fee'] : ($rowBillingType === 'monthly' ? $monthlyFee : $sessionFee);

                PatientTherapy::create([
                    'patient_id' => $patient->id,
                    'therapy_id' => $therapyId,
                    'therapist_id' => (int) $row['therapist_id'],
                    'billing_type' => $rowBillingType,
                    'fee' => $rowFee,
                    'start_date' => $row['start_date'] ?? $startDate,
                    'status' => 'active',
                ]);

                $totalMonthly += ($rowBillingType === 'monthly' ? $rowFee : 0);
                $totalSession += ($rowBillingType === 'session' ? $rowFee : 0);
                $totalApplied += $rowFee;
            }

            $patient->load(['therapies.therapy', 'therapies.therapist']);

            return [
                'patient' => $patient,
                'fee_summary' => [
                    'billing_type' => $billingType,
                    'total_monthly' => round($totalMonthly, 2),
                    'total_session_rate' => round($totalSession, 2),
                    'total_applied' => round($totalApplied, 2),
                    'therapy_count' => count($therapyRows),
                ],
            ];
        });
    }
}
