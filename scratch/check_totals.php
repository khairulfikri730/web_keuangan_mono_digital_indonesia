<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$dateFrom = \Carbon\Carbon::today();
$dateTo = \Carbon\Carbon::today()->endOfDay();

$expenseBreakdownQuery = \App\Models\Cashflow::where('transaction_category', 'expense')
    ->whereBetween('transaction_date', [$dateFrom, $dateTo]);

$cashExpensesToday = (clone $expenseBreakdownQuery)->where('source', 'pos_cash')->sum('amount');
$bankExpensesToday = (clone $expenseBreakdownQuery)->whereIn('source', ['pos_bank', 'transfer'])->sum('amount');
$nonShiftCashExpenses = (clone $expenseBreakdownQuery)->whereNull('shift_id')->where('source', 'pos_cash')->sum('amount');
$nonShiftBankExpenses = (clone $expenseBreakdownQuery)->whereNull('shift_id')->whereIn('source', ['pos_bank', 'transfer'])->sum('amount');

echo "Cash Expenses Today: $cashExpensesToday\n";
echo "Bank Expenses Today: $bankExpensesToday\n";
echo "Non-Shift Cash: $nonShiftCashExpenses\n";
echo "Non-Shift Bank: $nonShiftBankExpenses\n";

$shifts = \App\Models\Shift::whereBetween('opened_at', [$dateFrom, $dateTo])->get();
echo "\nShifts for today:\n";
$sumShiftExpenses = 0;
foreach ($shifts as $s) {
    echo "ID: " . $s->id . " Status: " . $s->status . " CashExp: " . $s->cash_expenses . " BankExp: " . $s->bank_expenses . "\n";
    $sumShiftExpenses += $s->cash_expenses + $s->bank_expenses;
}

echo "Total expenses recorded in shifts: $sumShiftExpenses\n";
