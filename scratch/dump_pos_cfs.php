<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$cfs = \App\Models\Cashflow::withoutGlobalScopes()->where('type', 'expense')->get();
foreach ($cfs as $c) {
    if (strpos($c->description, 'POS Cash Out') !== false) {
        echo $c->id . ' | ' . $c->description . ' | ref_type: ' . $c->reference_type . ' | ref_id: ' . $c->reference_id . ' | shift_id: ' . $c->shift_id . ' | created_at: ' . $c->created_at . "\n";
    }
}
