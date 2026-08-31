<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$jamsitUrl = 'https://maps.app.goo.gl/qXtH4vt6TWUV3GB49';
$studioUrl = 'https://maps.app.goo.gl/F2yFYtHLuzW7ZDr26';

$resp1 = \Illuminate\Support\Facades\Http::withoutRedirecting()->get($jamsitUrl);
echo "Jamsit redirect: " . $resp1->header('Location') . "\n";

$resp2 = \Illuminate\Support\Facades\Http::withoutRedirecting()->get($studioUrl);
echo "Studio redirect: " . $resp2->header('Location') . "\n";
