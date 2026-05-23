<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// 1. Assign CF 45 to Shift 9
$cf45 = \App\Models\Cashflow::withoutGlobalScopes()->find(45);
if ($cf45) {
    $cf45->update(['shift_id' => 9]);
    echo "Assigned CF 45 to Shift 9.\n";
}

// 2. Delete Shifts 1, 2, 3, 4, 5, 6, 8 and their transactions/cashflows
$shiftIdsToDelete = [1, 2, 3, 4, 5, 6, 8];
foreach ($shiftIdsToDelete as $sid) {
    $shift = \App\Models\Shift::find($sid);
    if ($shift) {
        // Delete Cashflows associated with this shift
        \App\Models\Cashflow::withoutGlobalScopes()->where('shift_id', $sid)->delete();
        
        // Delete Transactions and TransactionItems
        $transactions = \App\Models\Transaction::withoutGlobalScopes()->where('shift_id', $sid)->get();
        foreach ($transactions as $t) {
            \App\Models\TransactionItem::where('transaction_id', $t->id)->delete();
            $t->delete();
        }
        
        $shift->delete();
        echo "Deleted Shift $sid and its records.\n";
    }
}

// 3. Recalculate remaining shifts (Shift 7 and 9)
$shifts = \App\Models\Shift::all();
foreach ($shifts as $s) {
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
echo "Recalculated remaining shifts.\n";
