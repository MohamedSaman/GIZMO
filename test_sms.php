<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$url = 'https://www.smslenz.lk/api/send-sms';
$params = [
    'user_id' => '1644',
    'sender_id' => 'Sportynix',
    'to' => '0759037101',
    'message' => 'Test message from Gizmo Elec'
];
$apiKey = '9b60385e-e599-49f3-a30b-fee08a8aa52a';

// Try with Bearer Token
$responseBearer = Illuminate\Support\Facades\Http::withToken($apiKey)->post($url, $params);
echo "Bearer Token Status: " . $responseBearer->status() . "\n";
echo "Bearer Token Body snippet: " . substr($responseBearer->body(), 0, 150) . "\n\n";

// Try different payload key for phone number
$params2 = [
    'user_id' => '1644',
    'api_key' => '9b60385e-e599-49f3-a30b-fee08a8aa52a',
    'sender_id' => 'Sportynix',
    'number' => '0759037101', // sometimes 'number' is used instead of 'to'
    'text' => 'Test message from Gizmo Elec' // sometimes 'text' instead of 'message'
];

$responseKeys = Illuminate\Support\Facades\Http::asForm()->post($url, $params2);
echo "Alternate Keys Status: " . $responseKeys->status() . "\n";
echo "Alternate Keys Body snippet: " . substr($responseKeys->body(), 0, 150) . "\n\n";

