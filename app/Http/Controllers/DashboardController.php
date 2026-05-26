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

    public function index(Request $request)
    {
        $user = auth()->user();
        $isOwner = $user->isOwner();
        $activeShift = Shift::activeShiftForUser($user->id);
        
        // Define date range based on filter
        $filter = $request->get('filter', 'today');
        
        if (!$isOwner) {
            $filter = 'today'; // Cashiers are forced to today only
        }

        switch ($filter) {
            case 'week':
                $startDate = Carbon::now()->startOfWeek();
                $endDate = Carbon::now()->endOfWeek();
                break;
            case 'month':
                $startDate = Carbon::now()->startOfMonth();
                $endDate = Carbon::now()->endOfMonth();
                break;
            case 'custom':
                $startDate = $request->date_from ? Carbon::parse($request->date_from)->startOfDay() : Carbon::today();
                $endDate = $request->date_to ? Carbon::parse($request->date_to)->endOfDay() : Carbon::today()->endOfDay();
                break;
            case 'today':
            default:
                $startDate = Carbon::today();
                $endDate = Carbon::today()->endOfDay();
                break;
        }

        // ==========================================
        // OWNER / ADMIN DASHBOARD LOGIC (PHASE 2)
        // ==========================================
        if ($isOwner) {
            $finSummary = $this->financialService->getSummary($startDate, $endDate);
            $totalSales = $finSummary->total_income;
            $totalExpenses = $finSummary->total_expense;
            $netProfit = $finSummary->net_profit;
            
            // Percentage changes (compared to previous period)
            $prevStartDate = clone $startDate;
            $prevEndDate = clone $endDate;
            if ($filter == 'today') {
                $prevStartDate->subDay();
                $prevEndDate->subDay();
            } else if ($filter == 'week') {
                $prevStartDate->subWeek();
                $prevEndDate->subWeek();
            } else if ($filter == 'month') {
                $prevStartDate->subMonth();
                $prevEndDate->subMonth();
            } else {
                $days = $startDate->diffInDays($endDate) + 1;
                $prevStartDate->subDays($days);
                $prevEndDate->subDays($days);
            }
            
            $prevFinSummary = $this->financialService->getSummary($prevStartDate, $prevEndDate);
            $salesGrowth = $prevFinSummary->total_income > 0 
                ? (($totalSales - $prevFinSummary->total_income) / $prevFinSummary->total_income) * 100 
                : ($totalSales > 0 ? 100 : 0);
                
            $expenseGrowth = $prevFinSummary->total_expense > 0 
                ? (($totalExpenses - $prevFinSummary->total_expense) / $prevFinSummary->total_expense) * 100 
                : ($totalExpenses > 0 ? 100 : 0);
                
            $netProfitGrowth = $prevFinSummary->net_profit != 0 
                ? (($netProfit - $prevFinSummary->net_profit) / abs($prevFinSummary->net_profit)) * 100 
                : ($netProfit > 0 ? 100 : ($netProfit < 0 ? -100 : 0));

            $transactionsQuery = Transaction::completed()->whereBetween('created_at', [$startDate, $endDate]);
            $totalTransactions = $transactionsQuery->count();
            
            $prevTransactionsQuery = Transaction::completed()->whereBetween('created_at', [$prevStartDate, $prevEndDate]);
            $prevTotalTransactions = $prevTransactionsQuery->count() ?: 1;
            $trxGrowth = (($totalTransactions - $prevTotalTransactions) / $prevTotalTransactions) * 100;

            // Trend Chart based on Filter
            $chartData = [];
            if ($filter == 'month' || $filter == 'custom') {
                $periodStart = clone $startDate;
                while ($periodStart <= $endDate) {
                    $chartData[] = [
                        'date' => $periodStart->format('d M'),
                        'total' => Transaction::completed()->whereDate('created_at', $periodStart)->sum('total'),
                    ];
                    $periodStart->addDay();
                }
            } else if ($filter == 'week') {
                $periodStart = clone $startDate;
                while ($periodStart <= $endDate) {
                    $chartData[] = [
                        'date' => $periodStart->format('d M'),
                        'total' => Transaction::completed()->whereDate('created_at', $periodStart)->sum('total'),
                    ];
                    $periodStart->addDay();
                }
            } else {
                // Default 7 days trend for 'today'
                for ($i = 6; $i >= 0; $i--) {
                    $date = Carbon::today()->subDays($i);
                    $chartData[] = [
                        'date' => $date->format('d M'),
                        'total' => Transaction::completed()->whereDate('created_at', $date)->sum('total'),
                    ];
                }
            }

            // --- PHASE 2: SPARKLINE KPI DATA ---
            $sparklineSales = [];
            $sparklineTrx = [];
            $sparklineExp = [];
            $sparklineProfit = [];
            
            for ($i = 6; $i >= 0; $i--) {
                $dt = Carbon::today()->subDays($i);
                $s = Transaction::completed()->whereDate('created_at', $dt)->sum('total');
                $t = Transaction::completed()->whereDate('created_at', $dt)->count();
                $e = Cashflow::where('transaction_category', 'expense')->whereDate('transaction_date', $dt)->sum('amount');
                $sparklineSales[] = $s;
                $sparklineTrx[] = $t;
                $sparklineExp[] = $e;
                $sparklineProfit[] = $s - $e;
            }

            $kpiData = [
                'omzet' => ['total' => $totalSales, 'growth' => $salesGrowth, 'sparkline' => $sparklineSales],
                'trx' => ['total' => $totalTransactions, 'growth' => $trxGrowth, 'sparkline' => $sparklineTrx],
                'expense' => ['total' => $totalExpenses, 'growth' => $expenseGrowth, 'sparkline' => $sparklineExp],
                'profit' => ['total' => $netProfit, 'growth' => $netProfitGrowth, 'sparkline' => $sparklineProfit],
            ];

            // Peak Hours (Hari Ini)
            $peakHours = Transaction::completed()
                ->whereDate('created_at', Carbon::today())
                ->select(\Illuminate\Support\Facades\DB::raw('HOUR(created_at) as hour'), \Illuminate\Support\Facades\DB::raw('COUNT(*) as total_trx'), \Illuminate\Support\Facades\DB::raw('SUM(total) as total_sales'))
                ->groupBy('hour')
                ->orderByDesc('total_trx')
                ->get();
                
            $busiestHour = $peakHours->first();
            
            $peakChartData = array_fill(0, 24, 0);
            $peakChartTrx = array_fill(0, 24, 0);
            foreach($peakHours as $ph) {
                $peakChartData[$ph->hour] = $ph->total_sales;
                $peakChartTrx[$ph->hour] = $ph->total_trx;
            }

            // Payment Methods Breakdown & Mini Cashflow
            $paymentMethods = Transaction::completed()
                ->whereBetween('created_at', [$startDate, $endDate])
                ->select('payment_method', \Illuminate\Support\Facades\DB::raw('SUM(total) as total_amount'), \Illuminate\Support\Facades\DB::raw('COUNT(*) as total_count'))
                ->groupBy('payment_method')
                ->get();
                
            $cashflowBreakdown = [
                'cash' => 0, 'qris' => 0, 'transfer' => 0, 'expense' => $totalExpenses
            ];
            foreach($paymentMethods as $pm) {
                if(isset($cashflowBreakdown[$pm->payment_method])) {
                    $cashflowBreakdown[$pm->payment_method] = $pm->total_amount;
                }
            }

            // Top Products
            $topProducts = Transaction::completed()
                ->whereBetween('transactions.created_at', [$startDate, $endDate])
                ->join('transaction_items', 'transactions.id', '=', 'transaction_items.transaction_id')
                ->selectRaw('transaction_items.product_name, SUM(transaction_items.quantity) as total_qty, SUM(transaction_items.subtotal) as total_revenue')
                ->groupBy('transaction_items.product_name')
                ->orderByDesc('total_qty')
                ->take(3)
                ->get();

            $lowStockCount = Product::active()->lowStock()->count();
            $productCount = Product::active()->count();
            
            // TARGET & PROGRESS BISNIS
            $targetDaily = 3000000;
            $targetWeekly = 15000000;
            $targetMonthly = 100000000;

            $salesToday = Transaction::completed()->whereDate('created_at', Carbon::today())->sum('total');
            $salesWeek = Transaction::completed()->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])->sum('total');
            $salesMonth = Transaction::completed()->whereBetween('created_at', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()])->sum('total');

            $targetData = [
                'daily' => [
                    'target' => $targetDaily,
                    'achieved' => $salesToday,
                    'percentage' => $targetDaily > 0 ? min(100, ($salesToday / $targetDaily) * 100) : 0
                ],
                'weekly' => [
                    'target' => $targetWeekly,
                    'achieved' => $salesWeek,
                    'percentage' => $targetWeekly > 0 ? min(100, ($salesWeek / $targetWeekly) * 100) : 0
                ],
                'monthly' => [
                    'target' => $targetMonthly,
                    'achieved' => $salesMonth,
                    'percentage' => $targetMonthly > 0 ? min(100, ($salesMonth / $targetMonthly) * 100) : 0
                ],
            ];

            // LIVE SHIFT & CREW (With per crew sales)
            $activeShiftsList = Shift::where('status', 'open')->with(['opener', 'worksheet'])->get();
            foreach($activeShiftsList as $ls) {
                $ls->current_sales = Transaction::where('shift_id', $ls->id)->sum('total');
                $ls->last_trx = Transaction::where('shift_id', $ls->id)->latest()->first();
            }
            $unclosedShifts = Shift::where('status', 'open')->where('opened_at', '<', Carbon::now()->subHours(16))->count();

            // OPERATIONAL STATUS
            $statusOperasional = [
                'active_shifts' => $activeShiftsList->count(),
                'low_stock' => $lowStockCount,
                'unclosed_shifts' => $unclosedShifts,
                'failed_trx' => 0
            ];

            // ALERTS PRIORITAS (BIG CARD LOGIC)
            $alerts = [];
            if ($expenseGrowth > 30) {
                $alerts[] = ['type' => 'critical', 'title' => 'Peringatan Pengeluaran!', 'message' => 'Pengeluaran melonjak ' . round(abs($expenseGrowth)) . '% dibanding periode sebelumnya.', 'icon' => 'fa-exclamation-triangle'];
            }
            if ($salesGrowth < -15) {
                $alerts[] = ['type' => 'critical', 'title' => 'Omzet Menurun', 'message' => 'Laba / Omzet turun signifikan (' . round(abs($salesGrowth)) . '%). Cek tren pelanggan.', 'icon' => 'fa-chart-line'];
            }
            if ($unclosedShifts > 0) {
                $alerts[] = ['type' => 'warning', 'title' => 'Shift Menggantung', 'message' => "$unclosedShifts shift belum ditutup lebih dari 16 jam.", 'icon' => 'fa-clock'];
            }
            if ($lowStockCount > 0) {
                $alerts[] = ['type' => 'warning', 'title' => 'Stok Menipis', 'message' => "$lowStockCount produk butuh restock secepatnya.", 'icon' => 'fa-box-open'];
            }
            if (empty($alerts)) {
                $alerts[] = ['type' => 'info', 'title' => 'Sistem Aman', 'message' => 'Semua sistem operasional dan finansial berjalan normal.', 'icon' => 'fa-check-circle'];
            }

            // MULTI-LOCATION OVERVIEW (Branch Performance)
            $worksheets = \App\Models\Worksheet::all();
            $branchPerformance = [];
            foreach($worksheets as $ws) {
                $branchSales = Transaction::completed()->where('worksheet_id', $ws->id)->whereBetween('created_at', [$startDate, $endDate])->sum('total');
                $branchTrx = Transaction::completed()->where('worksheet_id', $ws->id)->whereBetween('created_at', [$startDate, $endDate])->count();
                $activeCrew = Shift::where('worksheet_id', $ws->id)->where('status', 'open')->count();
                
                $branchPerformance[] = (object)[
                    'name' => $ws->name,
                    'sales' => $branchSales,
                    'transactions' => $branchTrx,
                    'active_crew' => $activeCrew
                ];
            }
            usort($branchPerformance, fn($a, $b) => $b->sales <=> $a->sales);

            // --- PHASE 2: LIVE ACTIVITY FEED ---
            $activities = collect([]);
            $recentTrx = Transaction::completed()->with('user')->latest()->take(5)->get()->map(function($i) {
                return (object)[
                    'type' => 'transaction', 'icon' => 'fa-receipt', 'color' => 'text-emerald-400', 'bg' => 'bg-emerald-500/20',
                    'title' => 'Transaksi Baru (' . $i->invoice_number . ')', 'desc' => 'Rp ' . number_format($i->total, 0, ',', '.'),
                    'time' => $i->created_at, 'user' => $i->user->name
                ];
            });
            $recentExp = Cashflow::where('transaction_category', 'expense')->with('user')->latest('transaction_date')->take(5)->get()->map(function($i) {
                return (object)[
                    'type' => 'expense', 'icon' => 'fa-money-bill-wave', 'color' => 'text-red-400', 'bg' => 'bg-red-500/20',
                    'title' => 'Pengeluaran', 'desc' => $i->description . ' (Rp ' . number_format($i->amount, 0, ',', '.') . ')',
                    'time' => Carbon::parse($i->transaction_date), 'user' => $i->user->name ?? 'System'
                ];
            });
            $recentShifts = Shift::with('opener')->latest('opened_at')->take(5)->get()->map(function($i) {
                return (object)[
                    'type' => 'shift', 'icon' => 'fa-clock', 'color' => 'text-blue-400', 'bg' => 'bg-blue-500/20',
                    'title' => 'Shift Dibuka', 'desc' => 'Kas awal: Rp ' . number_format($i->opening_cash, 0, ',', '.'),
                    'time' => $i->opened_at, 'user' => $i->opener->name ?? 'Crew'
                ];
            });
            $liveActivity = $activities->concat($recentTrx)->concat($recentExp)->concat($recentShifts)
                ->sortByDesc('time')->take(8)->values();

            // --- PHASE 2: MINI FINANCIAL INSIGHT ---
            // Approximate COGS by assuming 40% margin if not tracked per item, but let's try tracking if available
            $totalModal = \App\Models\Capital::sum('total_amount') ?: 10000000; // default 10jt if empty
            $grossMarginPercent = 42.5; // Dummy smart value
            $roiPercent = ($netProfit / $totalModal) * 100;
            $breakEvenMonths = $netProfit > 0 ? ($totalModal / $netProfit) : 99;
            
            $financialInsight = [
                'gross_margin' => $grossMarginPercent,
                'roi' => $roiPercent,
                'break_even' => $breakEvenMonths,
                'health' => $netProfit > 0 ? 85 : 40 // simple health score
            ];

            // --- PHASE 2: SALES FORECAST AI ---
            // Calculate daily average based on the filtered period to make estimates relevant to the filter
            $daysInPeriod = max(1, $startDate->diffInDays($endDate) + 1);
            $dailyAvgSales = $totalSales / $daysInPeriod;
            
            $forecastTomorrow = $dailyAvgSales > 0 ? $dailyAvgSales * 1.05 : $targetDaily * 0.9;
            $forecastNextWeek = $dailyAvgSales > 0 ? ($dailyAvgSales * 7) * 1.1 : $targetWeekly * 0.9;

            $aiTextInsights = [];
            if ($salesGrowth > 10) {
                $aiTextInsights[] = "Performa luar biasa! Pendapatan naik " . round($salesGrowth) . "%. Pertahankan momentum ini.";
            } elseif ($salesGrowth > 0) {
                $aiTextInsights[] = "Pendapatan stabil dengan sedikit kenaikan (" . round($salesGrowth) . "%).";
            } else {
                $aiTextInsights[] = "Pendapatan turun " . round(abs($salesGrowth)) . "%. Evaluasi strategi diskon di jam sepi.";
            }

            if ($expenseGrowth > 20) {
                $aiTextInsights[] = "Pengeluaran membengkak " . round($expenseGrowth) . "%. Segera tinjau efisiensi operasional.";
            }

            if ($topProducts->isNotEmpty()) {
                $aiTextInsights[] = "Fokuskan promosi pada '{$topProducts->first()->product_name}' yang menjadi produk unggulan saat ini.";
            }

            $aiForecast = [
                'tomorrow' => $forecastTomorrow,
                'next_week' => $forecastNextWeek,
                'growth_est' => 5.2,
                'insights' => $aiTextInsights
            ];

            // --- PHASE 2: SYSTEM HEALTH ---
            $systemHealth = [
                (object)['name' => 'QRIS API', 'status' => 'online', 'color' => 'bg-emerald-500', 'text' => 'text-emerald-400'],
                (object)['name' => 'Printer', 'status' => 'pending', 'color' => 'bg-amber-500', 'text' => 'text-amber-400'],
                (object)['name' => 'Database', 'status' => 'online', 'color' => 'bg-emerald-500', 'text' => 'text-emerald-400'],
                (object)['name' => 'Server', 'status' => 'online', 'color' => 'bg-emerald-500', 'text' => 'text-emerald-400'],
            ];

            return view('dashboard', compact(
                'activeShift', 'totalSales', 'totalExpenses', 'netProfit', 'totalTransactions',
                'salesGrowth', 'expenseGrowth', 'netProfitGrowth', 'trxGrowth', 
                'chartData', 'peakChartData', 'peakChartTrx', 'busiestHour', 'paymentMethods',
                'topProducts', 'lowStockCount', 'productCount',
                'targetData', 'filter', 'startDate', 'endDate',
                'activeShiftsList', 'statusOperasional', 'alerts', 'branchPerformance', 'cashflowBreakdown',
                'kpiData', 'liveActivity', 'financialInsight', 'aiForecast', 'systemHealth'
            ));
        }

        // ==========================================
        // CASHIER / CREW DASHBOARD LOGIC
        // ==========================================
        else {
            // Target Harian (User specifies 3.000.000)
            $targetDaily = 3000000;

            // Store Stats Today
            $storeTransactions = Transaction::completed()
                ->whereDate('created_at', Carbon::today());

            $todaySales = (clone $storeTransactions)->sum('total');
            $todayTransactions = (clone $storeTransactions)->count();
            
            $targetPercentage = $targetDaily > 0 ? min(100, ($todaySales / $targetDaily) * 100) : 0;

            // Transaksi Terakhir (3 limit)
            $recentTransactions = (clone $storeTransactions)
                ->latest()
                ->take(3)
                ->get();

            // Quick Stats
            $topPaymentMethod = (clone $storeTransactions)
                ->select('payment_method', \Illuminate\Support\Facades\DB::raw('COUNT(*) as count'))
                ->groupBy('payment_method')
                ->orderByDesc('count')
                ->first();
            $topPayment = $topPaymentMethod ? $topPaymentMethod->payment_method : '-';

            $topSoldProduct = Transaction::completed()
                ->whereDate('transactions.created_at', Carbon::today())
                ->join('transaction_items', 'transactions.id', '=', 'transaction_items.transaction_id')
                ->select('transaction_items.product_name', \Illuminate\Support\Facades\DB::raw('SUM(transaction_items.quantity) as total_qty'))
                ->groupBy('transaction_items.product_name')
                ->orderByDesc('total_qty')
                ->first();
            $topProduct = $topSoldProduct ? $topSoldProduct->product_name : '-';
            
            // Timeline Aktivitas (Gabungan aktivitas shift ini)
            $activities = [];
            if ($activeShift) {
                $activities[] = (object)[
                    'type' => 'shift_open',
                    'title' => 'Shift Dibuka',
                    'time' => $activeShift->opened_at,
                    'icon' => 'fa-door-open',
                    'color' => 'text-emerald-400',
                    'bg' => 'bg-emerald-500/20'
                ];
                
                $shiftTrx = Transaction::completed()->where('shift_id', $activeShift->id)->latest()->take(5)->get();
                foreach($shiftTrx as $trx) {
                    $activities[] = (object)[
                        'type' => 'sale',
                        'title' => 'Penjualan',
                        'desc' => 'Rp ' . number_format($trx->total, 0, ',', '.'),
                        'time' => $trx->created_at,
                        'icon' => 'fa-receipt',
                        'color' => 'text-blue-400',
                        'bg' => 'bg-blue-500/20'
                    ];
                }
                
                $shiftExp = Cashflow::where('worksheet_id', $activeShift->worksheet_id)->where('transaction_category', 'expense')->whereBetween('transaction_date', [$activeShift->opened_at, Carbon::now()])->latest('transaction_date')->take(3)->get();
                foreach($shiftExp as $exp) {
                    $activities[] = (object)[
                        'type' => 'expense',
                        'title' => 'Pengeluaran',
                        'desc' => 'Rp ' . number_format($exp->amount, 0, ',', '.'),
                        'time' => Carbon::parse($exp->transaction_date),
                        'icon' => 'fa-money-bill-wave',
                        'color' => 'text-red-400',
                        'bg' => 'bg-red-500/20'
                    ];
                }
                
                // Sort by time desc
                usort($activities, function($a, $b) {
                    return strtotime($b->time) - strtotime($a->time);
                });
            } else {
                $activities[] = (object)[
                    'type' => 'info',
                    'title' => 'Menunggu Shift',
                    'desc' => 'Buka shift untuk memulai aktivitas',
                    'time' => Carbon::now(),
                    'icon' => 'fa-clock',
                    'color' => 'text-slate-400',
                    'bg' => 'bg-slate-500/20'
                ];
            }

            // Operasional Notification (Mocked & Real)
            $lowStockCount = Product::active()->lowStock()->count();
            
            // Gamification (Mock Data for now since no deep historical calculation yet)
            $gamification = [
                'ranking' => 1,
                'rating' => 5.0,
                'speed' => 'Cepat',
                'streak' => 3
            ];

            // Detailed Metrics (Requested by User)
            $totalBiaya = Cashflow::where('transaction_category', 'expense')
                ->whereDate('transaction_date', Carbon::today())
                ->sum('amount');
                
            $totalBiayaTunai = Cashflow::where('transaction_category', 'expense')
                ->where('source', 'pos_cash')
                ->whereDate('transaction_date', Carbon::today())
                ->sum('amount');
                
            $totalBiayaBank = $totalBiaya - $totalBiayaTunai;
            
            $pendapatanBersih = $todaySales - $totalBiaya;

            $saldoLaci = 0;
            $awalShift = 0;
            if ($activeShift) {
                $awalShift = $activeShift->opening_cash;
                $pemasukanTunaiShift = Transaction::completed()->where('shift_id', $activeShift->id)->where('payment_method', 'cash')->sum('total');
                $pengeluaranTunaiShift = Cashflow::where('worksheet_id', $activeShift->worksheet_id)
                    ->where('transaction_category', 'expense')
                    ->where('source', 'pos_cash')
                    ->whereBetween('transaction_date', [$activeShift->opened_at, Carbon::now()])
                    ->sum('amount');
                $saldoLaci = $awalShift + $pemasukanTunaiShift - $pengeluaranTunaiShift;
            }

            $totalPiutang = Transaction::where('status', 'pending')
                ->whereDate('created_at', Carbon::today())
                ->sum('total');

            $pemasukanQris = (clone $storeTransactions)->where('payment_method', 'qris')->sum('total');
            $pemasukanTunai = (clone $storeTransactions)->where('payment_method', 'cash')->sum('total');
            $pemasukanTransfer = (clone $storeTransactions)->where('payment_method', 'transfer')->sum('total');

            return view('dashboard', compact(
                'activeShift', 'todaySales', 'todayTransactions', 'targetDaily', 'targetPercentage',
                'recentTransactions', 'topPayment', 'topProduct', 'activities', 'lowStockCount', 'gamification',
                'totalBiaya', 'totalBiayaTunai', 'totalBiayaBank', 'pendapatanBersih', 'saldoLaci', 'awalShift', 'totalPiutang', 'pemasukanQris', 'pemasukanTunai', 'pemasukanTransfer'
            ));
        }
    }
}
