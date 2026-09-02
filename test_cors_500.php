<?php
require 'vendor/autoload.php';

$app = require 'bootstrap/app.php';

$app->router->post('/test-500', function () {
    throw new \Exception("Test 500");
});

$request = Illuminate\Http\Request::create('/test-500', 'POST');
$request->headers->set('Origin', 'http://localhost');

$response = $app->handle($request);

echo "Status: " . $response->getStatusCode() . "\n";
echo "CORS Header: " . ($response->headers->get('Access-Control-Allow-Origin') ?? 'MISSING') . "\n";
