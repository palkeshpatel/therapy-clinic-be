<?php
$patient = \App\Models\Patient::find(1);
$request = new \Illuminate\Http\Request([
    'patient_name' => 'ARVA PATEL',
    'phone' => '9999999999',
    'gender' => 'female',
    'status' => 'active',
    'therapies' => [
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
    ]
]);
$controller = new \App\Http\Controllers\Api\V1\PatientController();
$response = $controller->update($request, 1);
echo "RESPONSE CODE: " . $response->getStatusCode() . "\n";
echo "RESPONSE DATA: " . $response->getContent() . "\n";
