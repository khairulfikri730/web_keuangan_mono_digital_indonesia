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
     * Get all-time net profit for ROI calculation
     */
    public function getAllTimeNetProfit($worksheetId = null)
    {
        $incomeQuery = Transaction::completed();
        if ($worksheetId && $worksheetId !== 'all') {
            $incomeQuery->where('worksheet_id', $worksheetId);
        }
        $totalIncome = $incomeQuery->sum('total');

        $expenseQuery = Cashflow::where('type', 'expense')
            ->whereNotIn('category', ['Transfer Internal', 'Refund / Retur', 'Transfer Bank'])
            ->where(function($q) {
                $q->where('category', 'not like', '%Transfer%')
                  ->where('description', 'not like', '%Transfer%');
            });
            
        if ($worksheetId && $worksheetId !== 'all') {
            $expenseQuery->where('worksheet_id', $worksheetId);
        }
        
        $totalExpense = $expenseQuery->sum('amount');

        return $totalIncome - $totalExpense;
    }
}
