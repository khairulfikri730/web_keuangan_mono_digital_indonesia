<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$ws = \App\Models\Worksheet::all();
echo $ws->count() . " worksheets:\n";
foreach ($ws as $w) {
    echo "  ID={$w->id} | name={$w->name} | initial_balance={$w->initial_balance}\n";
}
