<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// 1. Detach MonthlyUsage expenses from shifts
\App\Models\Cashflow::withoutGlobalScopes()
    ->where('reference_type', 'MonthlyUsage')
    ->whereNotNull('shift_id')
    ->update(['shift_id' => null]);

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
            // Re-calculate expected cash if needed? Not strictly necessary unless requested, 
            // but doing it makes it mathematically perfect.
        ]);
    }
}
echo "Cleaned up MonthlyUsage shift_id and recalculated shifts!\n";
