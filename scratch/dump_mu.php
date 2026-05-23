<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$mu = \App\Models\MonthlyUsage::where('sub_category', 'pos_cash_out')->get();
foreach ($mu as $m) {
    echo $m->id . ' - ' . $m->description . "\n";
}
