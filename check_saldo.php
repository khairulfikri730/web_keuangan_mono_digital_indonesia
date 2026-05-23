<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Shift;
use App\Models\Cashflow;
use App\Models\Transaction;

$shift = Shift::withoutGlobalScopes()->where('status', 'open')->latest()->first();
if ($shift) {
    $summary = app(\App\Services\FinancialReportService::class)->getShiftSummary($shift->id, null);
    $cashSales = $summary->cash_sales;
    $cashExpenses = $summary->cash_expense;

    $transfers = (float) Cashflow::withoutGlobalScopes()
        ->where('shift_id', $shift->id)
        ->where('source', 'pos_cash')
        ->where('category', '!=', 'Penjualan')
        ->where('transaction_category', '!=', 'expense')
        ->sum(\Illuminate\Support\Facades\DB::raw('CASE WHEN type = "income" THEN amount ELSE -amount END'));

    $expectedCash = $shift->opening_cash + $cashSales - $cashExpenses + $transfers;
    
    echo "=== ESTIMASI SHIFT (Fixed) ===" . PHP_EOL;
    echo "Opening Cash: " . number_format($shift->opening_cash) . PHP_EOL;
    echo "Cash Sales: " . number_format($cashSales) . PHP_EOL;
    echo "Cash Expenses: " . number_format($cashExpenses) . PHP_EOL;
    echo "Transfers/Adj: " . number_format($transfers) . PHP_EOL;
    echo "Expected Cash (Tutup Shift): " . number_format($expectedCash) . PHP_EOL;
}
