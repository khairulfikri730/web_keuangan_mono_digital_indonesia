<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Locations:\n";
echo json_encode(\App\Models\ScheduleLocation::all()) . "\n\n";
echo "Shifts:\n";
echo json_encode(\App\Models\ScheduleShift::all()) . "\n";
