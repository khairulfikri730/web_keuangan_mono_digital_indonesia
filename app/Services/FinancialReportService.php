<?php

namespace App\Services;

use App\Models\Cashflow;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class FinancialReportService
{
    /**
     * Get consistent operational financial summary
     * 
     * @param Carbon $dateFrom
     * @param Carbon $dateTo
     * @param string|null $worksheetId
     * @return object
     */
    public function getSummary($dateFrom, $dateTo, $worksheetId = null)
    {
        // INCOME = Hanya dari POS Sales (transaksi riil)
        // Manual income (input saldo, adjustment kas) TIDAK dihitung sebagai pendapatan operasional
        $incomeQuery = Transaction::completed()
            ->whereBetween('created_at', [$dateFrom->copy()->startOfDay(), $dateTo->copy()->endOfDay()]);
            
        if ($worksheetId) {
            $incomeQuery->where('worksheet_id', $worksheetId);
        }
        
        $totalIncome = $incomeQuery->sum('total');
 
        $expenseQuery = Cashflow::where('transaction_category', 'expense')
            ->whereBetween('transaction_date', [$dateFrom->copy()->startOfDay(), $dateTo->copy()->endOfDay()]);

        if ($worksheetId) {
            $expenseQuery->where('worksheet_id', $worksheetId);
        }

        $cashflowCashExp = (clone $expenseQuery)->where('source', 'pos_cash')->sum('amount');
        $cashflowBankExp = (clone $expenseQuery)->whereIn('source', ['pos_bank', 'transfer', 'bank'])->sum('amount');

        // Include MonthlyUsage that might not be synced to Cashflow yet (legacy or failed sync)
        $baseMonthlyUsage = \App\Models\MonthlyUsage::whereBetween('expense_date', [$dateFrom->copy()->startOfDay(), $dateTo->copy()->endOfDay()])
            ->when($worksheetId, fn($q) => $q->where('worksheet_id', $worksheetId))
            ->whereNotExists(function($query) {
                $query->select(DB::raw(1))
                      ->from('cashflows')
                      ->whereRaw('cashflows.reference_id = monthly_usages.id')
                      ->where('cashflows.reference_type', "MonthlyUsage");
            });

        $monthlyUsageCashExp = (clone $baseMonthlyUsage)->where('payment_method', 'tunai')->sum('usage_amount');
        $monthlyUsageBankExp = (clone $baseMonthlyUsage)->where('payment_method', '!=', 'tunai')->sum('usage_amount');

        $cashExpense = $cashflowCashExp + $monthlyUsageCashExp;
        $bankExpense = $cashflowBankExp + $monthlyUsageBankExp;
        $totalExpense = $cashExpense + $bankExpense;
 
        // 3. NET PROFIT
        $netProfit = $totalIncome - $totalExpense;
 
        return (object) [
            'total_income' => $totalIncome,
            'total_expense' => $totalExpense,
            'cash_expense' => $cashExpense,
            'bank_expense' => $bankExpense,
            'net_profit' => $netProfit,
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'worksheet_id' => $worksheetId
        ];
    }
 
    /**
     * Get all-time net profit for ROI calculation
     */
    public function getAllTimeNetProfit($worksheetId = null)
    {
        // INCOME = Hanya dari POS Sales (konsisten dengan getSummary)
        $incomeQuery = Transaction::completed();
        if ($worksheetId) {
            $incomeQuery->where('worksheet_id', $worksheetId);
        }
        $totalIncome = $incomeQuery->sum('total');
 
        $cashflowExpense = Cashflow::where('transaction_category', 'expense')
            ->when($worksheetId, fn($q) => $q->where('worksheet_id', $worksheetId))
            ->sum('amount');

        $monthlyUsageExpense = \App\Models\MonthlyUsage::when($worksheetId, fn($q) => $q->where('worksheet_id', $worksheetId))
            ->whereNotExists(function($query) {
                $query->select(DB::raw(1))
                      ->from('cashflows')
                      ->whereRaw('cashflows.reference_id = monthly_usages.id')
                      ->where('cashflows.reference_type', "MonthlyUsage");
            })
            ->sum('usage_amount');

        return $totalIncome - ($cashflowExpense + $monthlyUsageExpense);
    }

    /**
     * Get monthly profit trend for a specific year
     */
    public function getTrend($year, $worksheetId = null)
    {
        $trend = [];
        for ($m = 1; $m <= 12; $m++) {
            $dateFrom = Carbon::createFromDate($year, $m, 1)->startOfMonth();
            $dateTo = $dateFrom->copy()->endOfMonth();
            
            $summary = $this->getSummary($dateFrom, $dateTo, $worksheetId);
            $trend[] = [
                'month' => $dateFrom->format('M'),
                'profit' => $summary->net_profit,
                'income' => $summary->total_income,
                'expense' => $summary->total_expense,
            ];
        }
        return $trend;
    }

    public function getTopProducts($dateFrom, $dateTo, $worksheetId = null, $limit = 5)
    {
        return \App\Models\TransactionItem::join('transactions', 'transaction_items.transaction_id', '=', 'transactions.id')
            ->where('transactions.status', 'completed')
            ->whereBetween('transactions.created_at', [$dateFrom->copy()->startOfDay(), $dateTo->copy()->endOfDay()])
            ->when($worksheetId, fn($q) => $q->where('transactions.worksheet_id', $worksheetId))
            ->selectRaw('product_name, SUM(quantity) as total_qty, SUM(transaction_items.subtotal) as total_revenue, SUM(quantity * cost_price) as total_cost')
            ->groupBy('product_name')
            ->selectRaw('SUM(transaction_items.subtotal - (quantity * cost_price)) as profit')
            ->orderByDesc('profit')
            ->take($limit)
            ->get();
    }

    /**
     * Get financial summary for a specific shift
     * 
     * @param int $shiftId
     * @param string|null $worksheetId
     * @return object
     */
    public function getShiftSummary($shiftId, $worksheetId = null)
    {
        $shift = \App\Models\Shift::withoutGlobalScopes()->find($shiftId);
        
        if (!$shift) {
            return (object) [
                'total_income' => 0, 'total_expense' => 0, 'net_profit' => 0,
                'cash_sales' => 0, 'bank_sales' => 0, 'cash_expense' => 0, 'bank_expense' => 0,
                'pos_income' => 0, 'manual_income' => 0,
            ];
        }

        $openedAt = $shift->opened_at;
        $closedAt = $shift->closed_at ?? now();

        $incomeQuery = Transaction::withoutGlobalScopes()->completed()->where('shift_id', $shiftId);
        $posIncome = $incomeQuery->sum('total');

        $manualIncome = Cashflow::withoutGlobalScopes()->where('transaction_category', 'income')
            ->where(function($q) use ($shiftId, $openedAt, $closedAt) {
                $q->where('shift_id', $shiftId)
                  ->orWhere(fn($q2) => $q2->whereNull('shift_id')->whereBetween('created_at', [$openedAt, $closedAt]));
            })
            ->whereNull('reference')
            ->sum('amount');

        $totalIncome = $posIncome + $manualIncome;

        $expenseQuery = Cashflow::withoutGlobalScopes()->where('transaction_category', 'expense')
            ->where(function($q) use ($shiftId, $openedAt, $closedAt) {
                $q->where('shift_id', $shiftId)
                  ->orWhere(fn($q2) => $q2->whereNull('shift_id')->whereBetween('created_at', [$openedAt, $closedAt]));
            });
        
        $totalExpense = (clone $expenseQuery)->sum('amount');
        $cashExpense = (clone $expenseQuery)->where('source', 'pos_cash')->sum('amount');
        $bankExpense = $totalExpense - $cashExpense;

        $cashSales = (clone $incomeQuery)->where('payment_method', 'cash')->sum('total');
        $bankSales = $posIncome - $cashSales;

        return (object) [
            'total_income' => $totalIncome,
            'total_expense' => $totalExpense,
            'net_profit' => $totalIncome - $totalExpense,
            'cash_sales' => $cashSales,
            'bank_sales' => $bankSales,
            'cash_expense' => $cashExpense,
            'bank_expense' => $bankExpense,
            'pos_income' => $posIncome,
            'manual_income' => $manualIncome,
        ];
    }
}
