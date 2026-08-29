<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$start = '2026-08-01';
$end = '2026-08-31';

$statsAssignments = App\Models\ScheduleAssignment::with(['shift.location', 'user'])
    ->whereBetween('date', [$start, $end])
    ->get();

echo "Total Assignments: " . $statsAssignments->count() . "\n";
if ($statsAssignments->count() > 0) {
    $a = $statsAssignments->first();
    echo "First Assignment User ID: " . $a->user_id . "\n";
    echo "First Assignment Shift: " . ($a->shift ? "YES" : "NO") . "\n";
    if ($a->shift) {
        echo "Shift ID: " . $a->shift->id . "\n";
        echo "Shift Location ID: " . $a->shift->location_id . "\n";
    }
}
