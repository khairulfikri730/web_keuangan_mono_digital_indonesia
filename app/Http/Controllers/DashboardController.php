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
        $activeShift = Shift::activeShift();

        // 1. Stats using Unified Service
        $finSummary = $this->financialService->getSummary($today, $today);
        $todaySales = $finSummary->total_income;
        $todayExpenses = $finSummary->total_expense;
        $todayTransactions = Transaction::completed()
            ->whereDate('created_at', $today)->count();

        // Stats bulan ini
        $monthSales = Transaction::completed()
            ->whereMonth('created_at', $today->month)
            ->whereYear('created_at', $today->year)
            ->sum('total');

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

        // Transaksi terbaru
        $recentTransactions = Transaction::with(['user', 'items'])
            ->latest()->take(5)->get();

        // Total produk
        $productCount = Product::count();

        // Top produk
        $topProducts = \App\Models\TransactionItem::selectRaw('product_id, product_name, SUM(quantity) as total_qty, SUM(subtotal) as total_revenue')
            ->groupBy('product_id', 'product_name')
            ->orderByDesc('total_qty')
            ->take(5)->get();

        return view('dashboard', compact(
            'activeShift', 'todaySales', 'todayTransactions',
            'todayExpenses', 'monthSales', 'lowStockProducts',
            'chartData', 'recentTransactions', 'topProducts', 'productCount'
        ));
    }
}
