<?php
require 'vendor/autoload.php';

$app = require 'bootstrap/app.php';

$request = Illuminate\Http\Request::create('/api/v1/auth/login', 'POST');
$request->headers->set('Origin', 'http://localhost');

$response = $app->handle($request);

echo "Status: " . $response->getStatusCode() . "\n";
echo "CORS Header: " . ($response->headers->get('Access-Control-Allow-Origin') ?? 'MISSING') . "\n";
