<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$cfs = \App\Models\Cashflow::withoutGlobalScopes()
    ->where('type', 'expense')
    ->where('description', 'like', 'POS Cash Out:%')
    ->whereNull('shift_id')
    ->get();

foreach ($cfs as $cf) {
    // Find a shift that was active at the time of creation
    $shift = \App\Models\Shift::where('opened_at', '<=', $cf->created_at)
        ->where(function($q) use ($cf) {
            $q->whereNull('closed_at')->orWhere('closed_at', '>=', $cf->created_at);
        })->orderBy('id', 'desc')->first();
        
    if ($shift) {
        $cf->update(['shift_id' => $shift->id]);
        echo "Restored CF {$cf->id} to Shift {$shift->id}\n";
    } else {
        echo "Could not find shift for CF {$cf->id} (created at {$cf->created_at})\n";
    }
}

// Recalculate shift totals
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
echo "Done!\n";
