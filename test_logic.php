<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

DB::transaction(function () {
    try {
        $patient = \App\Models\Patient::find(1);
        \App\Models\PatientTherapy::where('patient_id', $patient->id)->delete();
        \App\Models\DailySchedule::where('patient_id', $patient->id)->delete();

        $therapies = [
            [
                'therapy_id' => 7,
                'billing_type' => 'monthly',
                'fee' => 0,
                'therapist_id' => 2,
                'schedules' => [
                    ['date' => '2026-08-17', 'slot_id' => 1]
                ]
            ],
            [
                'therapy_id' => 7,
                'billing_type' => 'monthly',
                'fee' => 0,
                'therapist_id' => 2,
                'schedules' => [
                    ['date' => '2026-08-17', 'slot_id' => 2]
                ]
            ]
        ];

        foreach ($therapies as $index => $row) {
            $therapyId = (int) $row['therapy_id'];
            $therapy = \App\Models\Therapy::query()->find($therapyId);
            $therapistId = !empty($row['therapist_id']) ? (int) $row['therapist_id'] : null;

            \App\Models\PatientTherapy::create([
                'patient_id' => $patient->id,
                'therapy_id' => $therapyId,
                'therapist_id' => $therapistId,
                'billing_type' => $row['billing_type'],
                'fee' => $row['fee'],
                'total_sessions' => null,
                'start_date' => date('Y-m-d'),
                'status' => 'active',
            ]);

            $schedules = $row['schedules'] ?? [];
            if (!empty($schedules) && $therapistId) {
                foreach ($schedules as $scheduleIndex => $sched) {
                    \App\Models\DailySchedule::updateOrCreate(
                        [
                            'date'         => $sched['date'],
                            'slot_id'      => $sched['slot_id'],
                            'patient_id'   => $patient->id,
                            'therapist_id' => $therapistId,
                        ],
                        [
                            'therapy_id'   => $therapyId,
                            'status'       => 'scheduled',
                            'created_by'   => 1,
                        ]
                    );
                }
            }
        }
        echo "SUCCESS\n";
    } catch (\Exception $e) {
        echo "ERROR: " . $e->getMessage() . "\n";
    }
});
