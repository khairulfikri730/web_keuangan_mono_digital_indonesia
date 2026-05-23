<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$shifts = \App\Models\Shift::where('status', 'closed')->get();
foreach ($shifts as $s) {
    echo 'Shift: ' . $s->id . ' BankExp: ' . $s->bank_expenses . "\n";
    $cfs = \App\Models\Cashflow::withoutGlobalScopes()
        ->where('shift_id', $s->id)
        ->where('type', 'expense')
        ->whereIn('source', ['pos_bank', 'transfer'])
        ->get();
    foreach ($cfs as $cf) {
        echo ' - ' . $cf->amount . ' (' . $cf->category . ', ' . $cf->source . ', trans_cat: ' . $cf->transaction_category . ")\n";
    }
}
