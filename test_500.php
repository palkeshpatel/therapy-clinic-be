<?php
$updatePayload = json_encode(['trigger_fatal_error' => true]);

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
