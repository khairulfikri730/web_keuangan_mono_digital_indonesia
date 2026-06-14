<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$shift = App\Models\Shift::latest()->first();
echo "Shift ID: " . $shift->id . "\n";
echo "Opening Cash: " . $shift->opening_cash . "\n";
echo "Cash Sales: " . $shift->cash_sales . "\n";
echo "Cash Expenses: " . $shift->cash_expenses . "\n";
echo "Expected Cash: " . $shift->expected_cash . "\n";

$transfers = (float) \App\Models\Cashflow::withoutGlobalScopes()
    ->where('shift_id', $shift->id)
    ->where('source', 'pos_cash')
    ->whereNotIn('category', ['Penjualan', 'Uang Muka (DP)'])
    ->where('transaction_category', '!=', 'expense')
    ->sum(\Illuminate\Support\Facades\DB::raw('CASE WHEN type = "income" THEN amount ELSE -amount END'));
echo "Transfers (query): " . $transfers . "\n";

$allCashflows = \App\Models\Cashflow::withoutGlobalScopes()->where('shift_id', $shift->id)->get();
foreach($allCashflows as $cf) {
    echo "CF #{$cf->id} | {$cf->category} | {$cf->transaction_category} | {$cf->type} | {$cf->source} | {$cf->amount} \n";
}
