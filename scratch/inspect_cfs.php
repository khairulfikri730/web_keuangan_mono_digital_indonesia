<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$cfs = \App\Models\Cashflow::withoutGlobalScopes()
    ->whereNotNull('shift_id')
    ->where('type', 'expense')
    ->whereIn('source', ['pos_bank', 'transfer'])
    ->get();

foreach ($cfs as $cf) {
    echo $cf->transaction_date . ' | Shift: ' . $cf->shift_id . ' | Amount: ' . $cf->amount . ' | ' . $cf->category . "\n";
}
