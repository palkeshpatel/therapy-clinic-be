<?php
// Bootstrap Laravel
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$request = Illuminate\Http\Request::create('/api/v1/patients/1', 'PUT', [], [], [], [
    'CONTENT_TYPE' => 'application/json',
    'HTTP_ACCEPT' => 'application/json'
], json_encode([
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
]));
// We need to bypass auth or login
\Illuminate\Support\Facades\Auth::loginUsingId(1);
$response = $kernel->handle($request);
echo "STATUS: " . $response->getStatusCode() . "\n";
echo "CONTENT: " . $response->getContent() . "\n";
