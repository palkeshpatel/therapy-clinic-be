<?php
// We will test the API by getting a token first
$loginPayload = json_encode(['email' => 'admin@clinic.com', 'password' => 'admin123']);
$ch = curl_init('http://therapy-api.local/api/v1/login');
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
curl_setopt($ch, CURLOPT_POSTFIELDS, $loginPayload);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type:application/json', 'Accept:application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$loginRes = curl_exec($ch);
$loginData = json_decode($loginRes, true);
$token = $loginData['data']['token'] ?? null;

if (!$token) {
    echo "Login failed: $loginRes\n";
    exit(1);
}

// Now test the update API
$updatePayload = json_encode([
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

$ch2 = curl_init('http://therapy-api.local/api/v1/patients/1');
curl_setopt($ch2, CURLOPT_CUSTOMREQUEST, "PUT");
curl_setopt($ch2, CURLOPT_POSTFIELDS, $updatePayload);
curl_setopt($ch2, CURLOPT_HTTPHEADER, [
    'Content-Type:application/json', 
    'Accept:application/json',
    "Authorization: Bearer $token"
]);
curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
$updateRes = curl_exec($ch2);
echo "UPDATE RESPONSE: $updateRes\n";
