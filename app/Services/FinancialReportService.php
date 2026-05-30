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
        $incomeQuery = Transaction::withoutGlobalScopes()
            ->where(function($q) {
                $q->where('status', 'completed')
                  ->orWhere(function($sq) {
                      $sq->where('status', 'pending')->where('paid_so_far', '>', 0);
                  });
            })
            ->whereBetween('created_at', [$dateFrom->copy()->startOfDay(), $dateTo->copy()->endOfDay()]);
            
        if ($worksheetId) {
            $incomeQuery->where('worksheet_id', $worksheetId);
        }
        $totalIncome = $incomeQuery->sum(DB::raw("CASE WHEN status = 'completed' AND payment_method != 'piutang' THEN total ELSE paid_so_far END"));
        $totalCount = (clone $incomeQuery)->count();
 
 
        $expenseQuery = Cashflow::withoutGlobalScopes()->where('transaction_category', 'expense')
            ->whereBetween('transaction_date', [$dateFrom->copy()->startOfDay(), $dateTo->copy()->endOfDay()]);

        if ($worksheetId) {
            $expenseQuery->where('worksheet_id', $worksheetId);
        }

        $cashflowCashExp = (clone $expenseQuery)->where('source', 'pos_cash')->sum('amount');
        $cashflowBankExp = (clone $expenseQuery)->whereIn('source', ['pos_bank', 'transfer', 'bank'])->sum('amount');

        // Include MonthlyUsage that might not be synced to Cashflow yet (legacy or failed sync)
        $baseMonthlyUsage = \App\Models\MonthlyUsage::withoutGlobalScopes()->whereBetween('expense_date', [$dateFrom->copy()->startOfDay(), $dateTo->copy()->endOfDay()])
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
            'total_count' => $totalCount,
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
        $incomeQuery = Transaction::withoutGlobalScopes()
            ->where(function($q) {
                $q->where('status', 'completed')
                  ->orWhere(function($sq) {
                      $sq->where('status', 'pending')->where('paid_so_far', '>', 0);
                  });
            });
        if ($worksheetId) {
            $incomeQuery->where('worksheet_id', $worksheetId);
        }
        $totalIncome = $incomeQuery->sum(DB::raw("CASE WHEN status = 'completed' AND payment_method != 'piutang' THEN total ELSE paid_so_far END"));
 
        $cashflowExpense = Cashflow::withoutGlobalScopes()->where('transaction_category', 'expense')
            ->when($worksheetId, fn($q) => $q->where('worksheet_id', $worksheetId))
            ->sum('amount');

        $monthlyUsageExpense = \App\Models\MonthlyUsage::withoutGlobalScopes()->when($worksheetId, fn($q) => $q->where('worksheet_id', $worksheetId))
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
        return \App\Models\TransactionItem::withoutGlobalScopes()
            ->join('transactions', 'transaction_items.transaction_id', '=', 'transactions.id')
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

        // 1. Full Sales (non-piutang)
        $fullSalesQuery = Transaction::withoutGlobalScopes()
            ->where('shift_id', $shiftId)
            ->where('status', 'completed')
            ->where('payment_method', '!=', 'piutang');
        
        $fullSalesTotal = (clone $fullSalesQuery)->sum('total');
        $fullSalesCash = (clone $fullSalesQuery)->where('payment_method', 'cash')->sum('total');

        // 2. DP Piutang (piutang transactions)
        $dpQuery = Transaction::withoutGlobalScopes()
            ->where('shift_id', $shiftId)
            ->where('payment_method', 'piutang')
            ->whereNotIn('status', ['cancelled', 'batal'])
            ->where('paid_amount', '>', 0);

        $dpTotal = (clone $dpQuery)->sum('paid_amount');
        $dpCash = (clone $dpQuery)->where(function($q) {
            $q->where('dp_payment_method', 'cash')->orWhereNull('dp_payment_method');
        })->sum('paid_amount');

        // 3. Pelunasan Piutang
        $pelunasanQuery = Cashflow::withoutGlobalScopes()
            ->where('category', 'Pelunasan Piutang')
            ->where(function($q) use ($shiftId, $openedAt, $closedAt) {
                $q->where('shift_id', $shiftId)
                  ->orWhere(fn($q2) => $q2->whereNull('shift_id')->whereBetween('created_at', [$openedAt, $closedAt]));
            });
            
        $pelunasanTotal = (clone $pelunasanQuery)->sum('amount');
        $pelunasanCash = (clone $pelunasanQuery)->where('source', 'pos_cash')->sum('amount');

        $posIncome = $fullSalesTotal + $dpTotal + $pelunasanTotal;
        $cashSales = $fullSalesCash + $dpCash + $pelunasanCash;
        $bankSales = $posIncome - $cashSales;

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
