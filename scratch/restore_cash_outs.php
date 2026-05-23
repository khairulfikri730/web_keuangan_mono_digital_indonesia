<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$cfs = \App\Models\Cashflow::withoutGlobalScopes()
    ->where('reference_type', 'MonthlyUsage')
    ->where('description', 'like', 'POS Cash Out:%')
    ->get();

foreach ($cfs as $cf) {
    $monthlyUsage = \App\Models\MonthlyUsage::find($cf->reference_id);
    if ($monthlyUsage) {
        if (preg_match('/Shift ID:\s*(\d+)/', $monthlyUsage->description, $matches)) {
            $shiftId = $matches[1];
            $cf->update(['shift_id' => $shiftId]);
            echo "Restored shift_id $shiftId for CF " . $cf->id . "\n";
        }
    }
}

// 2. Recalculate all shift expenses accurately (both cash and bank)
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
echo "Done restoring genuine POS Cash Outs!\n";
