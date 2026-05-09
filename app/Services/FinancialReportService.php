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
        // 1. INCOME CALCULATION (From Transactions for maximum accuracy)
        $incomeQuery = Transaction::completed()
            ->whereBetween('created_at', [$dateFrom->copy()->startOfDay(), $dateTo->copy()->endOfDay()]);
            
        if ($worksheetId && $worksheetId !== 'all') {
            $incomeQuery->where('worksheet_id', $worksheetId);
        }
        
        $posIncome = $incomeQuery->sum('total');
        
        // Include manual income from Cashflow that is not from POS (reference is null)
        $manualIncome = Cashflow::where('transaction_category', 'income')
            ->whereNull('reference')
            ->whereBetween('transaction_date', [$dateFrom->copy()->startOfDay(), $dateTo->copy()->endOfDay()])
            ->when($worksheetId && $worksheetId !== 'all', fn($q) => $q->where('worksheet_id', $worksheetId))
            ->sum('amount');
            
        $totalIncome = $posIncome + $manualIncome;
 
        $expenseQuery = Cashflow::where('transaction_category', 'expense')
            ->whereBetween('transaction_date', [$dateFrom->copy()->startOfDay(), $dateTo->copy()->endOfDay()]);

        if ($worksheetId && $worksheetId !== 'all') {
            $expenseQuery->where('worksheet_id', $worksheetId);
        }

        $cashflowExpense = $expenseQuery->sum('amount');

        // Include MonthlyUsage that might not be synced to Cashflow yet (legacy or failed sync)
        $monthlyUsageExpense = \App\Models\MonthlyUsage::whereBetween('expense_date', [$dateFrom->copy()->startOfDay(), $dateTo->copy()->endOfDay()])
            ->when($worksheetId && $worksheetId !== 'all', fn($q) => $q->where('worksheet_id', $worksheetId))
            ->whereNotExists(function($query) {
                $query->select(DB::raw(1))
                      ->from('cashflows')
                      ->whereRaw('cashflows.reference_id = monthly_usages.id')
                      ->where('cashflows.reference_type', "MonthlyUsage");
            })
            ->sum('usage_amount');

        $totalExpense = $cashflowExpense + $monthlyUsageExpense;
 
        // 3. NET PROFIT
        $netProfit = $totalIncome - $totalExpense;
 
        return (object) [
            'total_income' => $totalIncome,
            'total_expense' => $totalExpense,
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
        $incomeQuery = Transaction::completed();
        if ($worksheetId && $worksheetId !== 'all') {
            $incomeQuery->where('worksheet_id', $worksheetId);
        }
        $totalIncome = $incomeQuery->sum('total');
 
        $cashflowExpense = Cashflow::where('transaction_category', 'expense')
            ->when($worksheetId && $worksheetId !== 'all', fn($q) => $q->where('worksheet_id', $worksheetId))
            ->sum('amount');

        $monthlyUsageExpense = \App\Models\MonthlyUsage::when($worksheetId && $worksheetId !== 'all', fn($q) => $q->where('worksheet_id', $worksheetId))
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

    /**
     * Get top profitable products
     */
    public function getTopProducts($dateFrom, $dateTo, $worksheetId = null, $limit = 5)
    {
        return \App\Models\TransactionItem::join('transactions', 'transaction_items.transaction_id', '=', 'transactions.id')
            ->where('transactions.status', 'completed')
            ->whereBetween('transactions.created_at', [$dateFrom->copy()->startOfDay(), $dateTo->copy()->endOfDay()])
            ->when($worksheetId && $worksheetId !== 'all', fn($q) => $q->where('transactions.worksheet_id', $worksheetId))
            ->selectRaw('product_name, SUM(quantity) as total_qty, SUM(transaction_items.subtotal) as total_revenue, SUM(quantity * cost_price) as total_cost')
            ->groupBy('product_name')
            ->selectRaw('SUM(transaction_items.subtotal - (quantity * cost_price)) as profit')
            ->orderByDesc('profit')
            ->take($limit)
            ->get();
    }
}
