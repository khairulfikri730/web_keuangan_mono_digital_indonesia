<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

foreach(\App\Models\Shift::all() as $s) {
    $cashSales = \App\Models\Transaction::withoutGlobalScopes()->where('shift_id', $s->id)->where('payment_method', 'cash')->where('status', 'completed')->sum('total');
    $cashExpenses = \App\Models\Cashflow::withoutGlobalScopes()->where('shift_id', $s->id)->where('transaction_category', 'expense')->where('source', 'pos_cash')->sum('amount');
    $cashTransfers = \App\Models\Cashflow::withoutGlobalScopes()->where('shift_id', $s->id)->where('source', 'pos_cash')->where('category', '!=', 'Penjualan')->where('transaction_category', '!=', 'expense')->sum(\Illuminate\Support\Facades\DB::raw('CASE WHEN type = "income" THEN amount ELSE -amount END'));
    $expected = $s->opening_cash + $cashSales - $cashExpenses + $cashTransfers;
    if($s->closing_cash !== null && $s->closing_cash != $expected) {
        echo "Shift {$s->id}: expected {$expected}, closing {$s->closing_cash}, discrepancy " . ($s->closing_cash - $expected) . "\n";
    }
}
echo "Done\n";
