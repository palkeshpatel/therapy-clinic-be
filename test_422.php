<?php
// Mock request with missing fields to trigger 422
$updatePayload = json_encode([
    'patient_name' => 'ARVA PATEL',
    'status' => 'active',
    'therapies' => [
        [
            'therapy_id' => 9999, // Invalid therapy to trigger validation error
            'billing_type' => 'monthly',
            'fee' => 0
        ]
    ]
]);

$ch2 = curl_init('http://therapy-api.local/api/v1/patients/2');
curl_setopt($ch2, CURLOPT_CUSTOMREQUEST, "PUT");
curl_setopt($ch2, CURLOPT_POSTFIELDS, $updatePayload);
curl_setopt($ch2, CURLOPT_HTTPHEADER, [
    'Content-Type:application/json', 
    'Accept:application/json',
    'Origin: http://localhost:8080'
]);
curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch2, CURLOPT_HEADER, true);
$updateRes = curl_exec($ch2);
echo "UPDATE RESPONSE: $updateRes\n";
