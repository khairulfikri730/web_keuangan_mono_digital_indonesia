<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$as = App\Models\ScheduleAssignment::where('date', '2026-05-22')->get();
echo "Assignments for 2026-05-22:\n";
foreach($as as $a) {
    echo $a->id . ' | ' . $a->crew->name . ' | shift_id:' . $a->schedule_shift_id . ' | status:' . $a->status . " | date: " . $a->date->format('Y-m-d') . "\n";
}

$a = App\Models\ScheduleAssignment::where('date', '2026-05-22')->first();
echo "\nTesting query:\n";
echo App\Models\ScheduleAssignment::where('schedule_shift_id', $a->schedule_shift_id)
    ->where('date', $a->date->format('Y-m-d'))->toSql() . "\n";
echo "Count: " . App\Models\ScheduleAssignment::where('schedule_shift_id', $a->schedule_shift_id)
    ->where('date', $a->date->format('Y-m-d'))->count() . "\n";
