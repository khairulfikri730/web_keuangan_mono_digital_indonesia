<?php

namespace App\Http\Controllers;

use App\Models\Cashflow;
use App\Models\Product;
use App\Models\Shift;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    protected $financialService;

    public function __construct(\App\Services\FinancialReportService $financialService)
    {
        $this->financialService = $financialService;
    }

    public function index()
    {
        $today = Carbon::today();
        $activeShift = Shift::activeShiftForUser(auth()->id());

        // 1. Stats using Unified Service
        $finSummary = $this->financialService->getSummary($today, $today);
        $todaySales = $finSummary->total_income;
        $todayExpenses = $finSummary->total_expense;
        $todayNetProfit = $finSummary->net_profit;
        $todayTransactions = Transaction::completed()
            ->whereDate('created_at', $today)->count();

        // Produk stok rendah
        $lowStockProducts = Product::active()->lowStock()->with('category')->take(5)->get();

        // 7 hari penjualan untuk chart
        $chartData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $chartData[] = [
                'date' => $date->format('d M'),
                'total' => Transaction::completed()->whereDate('created_at', $date)->sum('total'),
            ];
        }

        // Transaksi terbaru (hari ini)
        $recentTransactions = Transaction::completed()
            ->whereDate('created_at', $today)
            ->with(['user', 'items'])
            ->latest()
            ->take(5)
            ->get();

        // Total produk
        $productCount = Product::count();

        // Top produk (hari ini)
        $topProducts = Transaction::completed()
            ->whereDate('transactions.created_at', $today)
            ->join('transaction_items', 'transactions.id', '=', 'transaction_items.transaction_id')
            ->selectRaw('transaction_items.product_id, transaction_items.product_name, SUM(transaction_items.quantity) as total_qty, SUM(transaction_items.subtotal) as total_revenue')
            ->groupBy('transaction_items.product_id', 'transaction_items.product_name')
            ->orderByDesc('total_qty')
            ->take(5)
            ->get();

        return view('dashboard', compact(
            'activeShift', 'todaySales', 'todayTransactions',
            'todayExpenses', 'todayNetProfit', 'lowStockProducts',
            'chartData', 'recentTransactions', 'topProducts', 'productCount'
        ));
    }
}
