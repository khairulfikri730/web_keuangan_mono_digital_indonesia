<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$mappings = [
    8 => 1,
    11 => 1,
    12 => 1,
    17 => 2,
    18 => 2,
    19 => 2,
    20 => 2,
    26 => 3,
    27 => 3,
    31 => 4,
    32 => 4,
];

foreach ($mappings as $cfId => $shiftId) {
    \App\Models\Cashflow::withoutGlobalScopes()->where('id', $cfId)->update(['shift_id' => $shiftId]);
}

$shifts = \App\Models\Shift::all();
foreach ($shifts as $s) {
    if ($s->status === 'closed') {
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
}
echo "Full recovery complete!\n";
