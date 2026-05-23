<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$cfs = \App\Models\Cashflow::withoutGlobalScopes()->where('type', 'expense')->get();
foreach ($cfs as $c) {
    echo $c->description . ' | ref_type: ' . $c->reference_type . ' | shift_id: ' . $c->shift_id . "\n";
}
