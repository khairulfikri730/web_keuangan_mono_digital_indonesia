<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$mu = \App\Models\MonthlyUsage::whereDate('created_at', today())->get();
foreach ($mu as $m) {
    echo $m->id . ' - ' . $m->expense_name . ' | desc: ' . $m->description . "\n";
}
