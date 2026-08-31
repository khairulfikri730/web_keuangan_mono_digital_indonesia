<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$url1 = 'https://maps.app.goo.gl/uTHUKVCT5kbevnwHA';
$response = \Illuminate\Support\Facades\Http::withoutRedirecting()->get($url1);
echo "Location 1: " . $response->header('Location') . "\n";

$url2 = 'https://maps.app.goo.gl/vYYSj4spLQvUxUtFA';
$response2 = \Illuminate\Support\Facades\Http::withoutRedirecting()->get($url2);
echo "Location 2: " . $response2->header('Location') . "\n";
