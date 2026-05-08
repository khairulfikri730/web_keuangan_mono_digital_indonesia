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
        
        $totalIncome = $incomeQuery->sum('total');

        // 2. EXPENSE CALCULATION (From Cashflow with strict filters)
        $expenseQuery = Cashflow::where('type', 'expense')
            ->whereBetween('transaction_date', [$dateFrom->copy()->startOfDay(), $dateTo->copy()->endOfDay()])
            ->whereNotIn('category', ['Transfer Internal', 'Refund / Retur', 'Transfer Bank'])
            ->where(function($q) {
                $q->where('category', 'not like', '%Transfer%')
                  ->where('description', 'not like', '%Transfer%');
            });

        if ($worksheetId && $worksheetId !== 'all') {
            $expenseQuery->where('worksheet_id', $worksheetId);
        }

        $totalExpense = $expenseQuery->sum('amount');

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
            ->selectRaw('product_name, SUM(quantity) as total_qty, SUM(subtotal) as total_revenue, SUM(quantity * cost_price) as total_cost')
            ->groupBy('product_name')
            ->selectRaw('SUM(subtotal - (quantity * cost_price)) as profit')
            ->orderByDesc('profit')
            ->take($limit)
            ->get();
    }
}
