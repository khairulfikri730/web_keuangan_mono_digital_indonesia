<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Cashflow;
use App\Models\Product;
use App\Models\TransactionItem;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function sales(Request $request)
    {
        $dateFrom = $request->date_from ? Carbon::parse($request->date_from) : now()->startOfMonth();
        $dateTo = $request->date_to ? Carbon::parse($request->date_to) : now();

        $baseQuery = Transaction::completed()
            ->whereBetween('created_at', [$dateFrom->startOfDay(), $dateTo->endOfDay()])
            ->when($request->payment_method, fn($q) => $q->where('payment_method', $request->payment_method))
            ->when($request->user_id, fn($q) => $q->where('user_id', $request->user_id));

        $transactions = (clone $baseQuery)->with(['user', 'items'])->latest()->paginate(20)->withQueryString();

        $summary = (clone $baseQuery)
            ->selectRaw('COUNT(*) as total_trx, SUM(total) as total_sales, SUM(discount) as total_discount')
            ->first();

        // COGS
        $totalCogs = TransactionItem::whereIn('transaction_id', (clone $baseQuery)->pluck('id'))
            ->sum(\Illuminate\Support\Facades\DB::raw('quantity * cost_price'));
        $summary->total_cogs = $totalCogs;

        $byPayment = (clone $baseQuery)
            ->selectRaw('payment_method, COUNT(*) as count, SUM(total) as total')
            ->groupBy('payment_method')->get();

        // Charts Data
        $salesPerDay = (clone $baseQuery)
            ->selectRaw('DATE(created_at) as date, SUM(total) as total')
            ->groupBy('date')->orderBy('date')->get();

        $byCategory = TransactionItem::whereIn('transaction_id', (clone $baseQuery)->pluck('id'))
            ->join('products', 'transaction_items.product_id', '=', 'products.id')
            ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
            ->selectRaw('COALESCE(categories.name, "Tanpa Kategori") as category_name, SUM(transaction_items.subtotal) as total')
            ->groupBy('category_name')->get();

        $topProducts = TransactionItem::whereIn('transaction_id', (clone $baseQuery)->pluck('id'))
            ->selectRaw('product_name, SUM(quantity) as total_qty, SUM(subtotal) as total_revenue, SUM(quantity * cost_price) as total_cost')
            ->groupBy('product_name')->orderByDesc('total_revenue')->take(10)->get();

        $peakHours = (clone $baseQuery)
            ->selectRaw('HOUR(created_at) as hour, COUNT(*) as count')
            ->groupBy('hour')->orderByDesc('count')->take(5)->get();

        $users = \App\Models\User::all();

        return view('reports.sales', compact(
            'transactions', 'summary', 'byPayment', 'topProducts', 'dateFrom', 'dateTo', 'salesPerDay', 'byCategory', 'peakHours', 'users'
        ));
    }

    public function financial(Request $request)
    {
        $month = $request->month ?? now()->month;
        $year = $request->year ?? now()->year;

        $income = Cashflow::income()->whereMonth('transaction_date', $month)->whereYear('transaction_date', $year)->sum('amount');
        $expense = Cashflow::expense()->whereMonth('transaction_date', $month)->whereYear('transaction_date', $year)->sum('amount');
        $profit = $income - $expense;

        $salesTotal = Transaction::completed()
            ->whereMonth('created_at', $month)->whereYear('created_at', $year)->sum('total');

        $cogs = TransactionItem::join('transactions', 'transaction_items.transaction_id', '=', 'transactions.id')
            ->where('transactions.status', 'completed')
            ->whereMonth('transactions.created_at', $month)->whereYear('transactions.created_at', $year)
            ->sum(\Illuminate\Support\Facades\DB::raw('transaction_items.quantity * transaction_items.cost_price'));

        $grossProfit = $salesTotal - $cogs;

        return view('reports.financial', compact(
            'income', 'expense', 'profit', 'salesTotal', 'cogs', 'grossProfit', 'month', 'year'
        ));
    }

    public function shifts(Request $request)
    {
        $baseQuery = \App\Models\Shift::query()
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->date_from, fn($q) => $q->whereDate('opened_at', '>=', $request->date_from))
            ->when($request->date_to, fn($q) => $q->whereDate('opened_at', '<=', $request->date_to))
            ->when($request->user_id, fn($q) => $q->where('opened_by', $request->user_id));

        $shifts = (clone $baseQuery)->with(['opener', 'closer'])->latest()->paginate(15)->withQueryString();

        // Summary
        $activeShiftsCount = (clone $baseQuery)->where('status', 'open')->count();
        $totalSalesToday = \App\Models\Transaction::completed()->whereDate('created_at', now()->toDateString())->sum('total');
        
        // Calculate total closing cash and discrepancies for closed shifts
        $closedShifts = (clone $baseQuery)->where('status', 'closed')->get();
        $totalClosingCash = 0;
        $totalDiscrepancy = 0;

        foreach ($closedShifts as $s) {
            $totalClosingCash += $s->closing_cash;
            $expected = $s->opening_cash + \App\Models\Transaction::where('shift_id', $s->id)->where('payment_method', 'cash')->where('status', 'completed')->sum('total');
            $totalDiscrepancy += ($s->closing_cash - $expected);
        }

        $avgDiscrepancy = $closedShifts->count() > 0 ? $totalDiscrepancy / $closedShifts->count() : 0;
        $highestShift = $closedShifts->sortByDesc('total_sales')->first();

        // Best Cashier today
        $bestCashier = \App\Models\Transaction::completed()->whereDate('transactions.created_at', now()->toDateString())
            ->join('users', 'transactions.user_id', '=', 'users.id')
            ->selectRaw('users.name, SUM(transactions.total) as total')
            ->groupBy('users.name')->orderByDesc('total')->first();

        // Active Shift Card data
        $activeShift = \App\Models\Shift::with('opener')->where('status', 'open')->latest()->first();

        $users = \App\Models\User::all();

        return view('reports.shifts', compact(
            'shifts', 'activeShiftsCount', 'totalClosingCash', 'totalSalesToday', 'totalDiscrepancy',
            'highestShift', 'avgDiscrepancy', 'bestCashier', 'activeShift', 'users'
        ));
    }
}
