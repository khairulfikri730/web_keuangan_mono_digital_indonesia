<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$shifts = \App\Models\Shift::where('status', 'closed')->get();
foreach ($shifts as $s) {
    $bankExp = \App\Models\Cashflow::withoutGlobalScopes()
        ->where('shift_id', $s->id)
        ->where('type', 'expense')
        ->whereIn('source', ['pos_bank', 'transfer'])
        ->sum('amount');
        
    $s->update(['bank_expenses' => $bankExp]);
}
echo "Done!\n";
