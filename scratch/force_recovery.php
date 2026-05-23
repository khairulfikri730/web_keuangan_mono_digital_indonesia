<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

\App\Models\Cashflow::withoutGlobalScopes()->whereIn('id', [39, 40])->update(['shift_id' => 7]);

$s = \App\Models\Shift::find(7);
if ($s) {
    $bankExp = \App\Models\Cashflow::withoutGlobalScopes()
        ->where('shift_id', $s->id)
        ->where('type', 'expense')
        ->whereIn('source', ['pos_bank', 'transfer'])
        ->sum('amount');
        
    $cashExp = \App\Models\Cashflow::withoutGlobalScopes()
        ->where('shift_id', $s->id)
        ->where('type', 'expense')
        ->where('source', 'pos_cash')
        ->sum('amount');
        
    $s->update([
        'bank_expenses' => $bankExp,
        'cash_expenses' => $cashExp,
    ]);
}
echo "Shift 7 recovered!\n";
