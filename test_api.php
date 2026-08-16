<?php
$payload = [
    "patient_name" => "Test Patient",
    "phone" => "9999999999",
    "gender" => "male",
    "status" => "active",
    "therapies" => [
        [
            "therapy_id" => 1,
            "billing_type" => "monthly",
            "fee" => 1000
        ],
        [
            "therapy_id" => 2,
            "billing_type" => "monthly",
            "fee" => 1000
        ]
    ]
];
$ch = curl_init('http://localhost:8000/api/v1/patients/1');
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type:application/json', 'Accept:application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$res = curl_exec($ch);
echo "RESPONSE: " . $res . "\n";
