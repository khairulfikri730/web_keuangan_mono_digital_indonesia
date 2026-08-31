<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$jamsit = App\Models\ScheduleLocation::where('name', 'like', '%Jamsit%')->first();
if($jamsit) { 
    $jamsit->update(['latitude' => -0.9656779, 'longitude' => 100.3589299, 'radius' => 200]);
    echo "Updated Jamsit\n";
}

$studio = App\Models\ScheduleLocation::where('name', 'like', '%Studio%')->first();
if($studio) { 
    $studio->update(['latitude' => -0.8925484, 'longitude' => 100.3481005, 'radius' => 200]); 
    echo "Updated Studio\n";
}
