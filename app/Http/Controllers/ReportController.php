<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Cashflow;
use App\Models\Product;
use App\Models\TransactionItem;
use App\Models\Setting;
use App\Models\Worksheet;
use App\Models\Shift;
use App\Models\Capital;
use App\Exports\ReportExport;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    protected $financialService;

    public function __construct(\App\Services\FinancialReportService $financialService)
    {
        $this->financialService = $financialService;
    }

    public function sales(Request $request)
    {
        $dfParam = $request->date_from;
        $dtParam = $request->date_to;
        $dateFrom = ($dfParam && !is_array($dfParam)) ? Carbon::parse($dfParam) : now()->startOfMonth();
        $dateTo = ($dtParam && !is_array($dtParam)) ? Carbon::parse($dtParam) : now()->endOfMonth();

        $baseQuery = Transaction::completed()
            ->whereBetween('created_at', [$dateFrom->copy()->startOfDay(), $dateTo->copy()->endOfDay()])
            ->when($request->payment_method, fn($q) => $q->where('payment_method', $request->payment_method))
            ->when($request->filled('user_id'), fn($q) => $q->where('user_id', $request->user_id));

        $worksheetId = session('active_worksheet_id');
        if ($worksheetId) {
            $baseQuery->where('worksheet_id', $worksheetId);
        }

        $perPage = $request->input('per_page', 20);
        $transactions = (clone $baseQuery)->with(['user', 'items'])->latest()->paginate($perPage)->withQueryString();

        $summary = (clone $baseQuery)
            ->selectRaw('COUNT(*) as total_trx, SUM(total) as total_sales, SUM(discount) as total_discount')
            ->first();

        // COGS
        $totalCogs = TransactionItem::whereIn('transaction_id', (clone $baseQuery)->pluck('id'))
            ->sum(DB::raw('quantity * cost_price'));
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

        $productPerformance = TransactionItem::whereIn('transaction_id', (clone $baseQuery)->pluck('id'))
            ->selectRaw('product_name, SUM(quantity) as total_qty, SUM(subtotal) as total_revenue, SUM(quantity * cost_price) as total_cost')
            ->groupBy('product_name')
            ->get()
            ->map(function($item) {
                $item->profit = $item->total_revenue - $item->total_cost;
                $item->margin = $item->total_revenue > 0 ? round(($item->profit / $item->total_revenue) * 100) : 0;
                return $item;
            });

        $topProducts = $productPerformance->sortByDesc('total_revenue')->take(10)->values();
        $topProfitProduct = $productPerformance->sortByDesc('profit')->first();
        $worstMarginProduct = $productPerformance->sortBy('margin')->first();

        $peakHours = (clone $baseQuery)
            ->selectRaw('HOUR(created_at) as hour, COUNT(*) as count')
            ->groupBy('hour')->orderByDesc('count')->take(5)->get();

        // JAM PALING MENGUNTUNGKAN (MOST PROFITABLE HOUR)
        $profitableHoursData = DB::table('transactions')
            ->join('transaction_items', 'transactions.id', '=', 'transaction_items.transaction_id')
            ->whereIn('transactions.id', (clone $baseQuery)->pluck('id'))
            ->selectRaw('
                HOUR(transactions.created_at) as hour,
                COUNT(DISTINCT transactions.id) as trx_count,
                SUM(transaction_items.quantity * transaction_items.cost_price) as total_cogs
            ')
            ->groupBy('hour')
            ->get();
            
        $salesPerHour = (clone $baseQuery)
            ->selectRaw('
                HOUR(created_at) as hour,
                SUM(total) as total_sales
            ')
            ->groupBy('hour')
            ->get()->keyBy('hour');

        $profitableHours = $profitableHoursData->map(function($item) use ($salesPerHour) {
            $salesData = $salesPerHour->get($item->hour);
            $totalSales = $salesData ? $salesData->total_sales : 0;
            
            $item->total_sales = $totalSales;
            $item->profit = $totalSales - $item->total_cogs;
            $item->margin = $totalSales > 0 ? round(($item->profit / $totalSales) * 100) : 0;
            return $item;
        })->sortByDesc('profit')->values();
        
        $topProfitableHour = $profitableHours->first();

        // SALDO LACI & BANK (ALL-TIME synced — kondisi uang saat ini, tidak berubah saat filter diganti)
        $saldoLaci = Cashflow::where('source', 'pos_cash')->where('bank_sync_status', 'synced')->where('type', 'income')->sum('amount')
                   - Cashflow::where('source', 'pos_cash')->where('bank_sync_status', 'synced')->where('type', 'expense')->sum('amount');

        $saldoBank = Cashflow::whereIn('source', ['pos_bank', 'transfer'])->where('bank_sync_status', 'synced')->where('type', 'income')->sum('amount')
                   - Cashflow::whereIn('source', ['pos_bank', 'transfer'])->where('bank_sync_status', 'synced')->where('type', 'expense')->sum('amount');


        // Unified Financial Summary
        $finSummary = $this->financialService->getSummary($dateFrom, $dateTo, $worksheetId);
        $totalExpense = $finSummary->total_expense;
        $netProfit = $finSummary->net_profit;

        $topExpenseCategory = \App\Models\Cashflow::where('transaction_category', 'expense')
            ->whereBetween('transaction_date', [$dateFrom->copy()->startOfDay(), $dateTo->copy()->endOfDay()])
            ->when($worksheetId, fn($q) => $q->where('worksheet_id', $worksheetId))
            ->selectRaw('category, SUM(amount) as total')
            ->groupBy('category')
            ->orderByDesc('total')
            ->first();

        $users = \App\Models\User::all();

        // PRODUK TIDAK AKTIF / MATI
        $inactiveProductsQuery = Product::with('category')
            ->where('is_active', true)
            ->where(function($q) {
                $q->where('product_kind', 'unlimited')
                  ->orWhere(function($sq) {
                      $sq->where('product_kind', '!=', 'unlimited')
                         ->where('stock', '>', 0);
                  });
            });

        if ($worksheetId) {
            $inactiveProductsQuery->where('worksheet_id', $worksheetId);
        }
        $allProducts = $inactiveProductsQuery->get();

        $lastSales = DB::table('transaction_items')
            ->join('transactions', 'transaction_items.transaction_id', '=', 'transactions.id')
            ->select('transaction_items.product_id', DB::raw('MAX(transactions.created_at) as last_sold_at'))
            ->groupBy('transaction_items.product_id')
            ->get()
            ->keyBy('product_id');

        $inactiveProducts = $allProducts->map(function($product) use ($lastSales) {
            $lastSoldAt = $lastSales->has($product->id) ? Carbon::parse($lastSales->get($product->id)->last_sold_at) : null;
            $daysSinceLastSale = $lastSoldAt ? $lastSoldAt->diffInDays(now()) : 999; 

            $product->last_sold_at = $lastSoldAt;
            $product->days_since_last_sale = $daysSinceLastSale;
            
            if ($product->product_kind === 'unlimited') {
                $product->potential_loss = 0;
            } else {
                $product->potential_loss = $product->stock * $product->cost_price;
            }

            if ($daysSinceLastSale >= 30) {
                $product->inactive_status = 'Mati';
                $product->inactive_color = 'red';
            } elseif ($daysSinceLastSale >= 14) {
                $product->inactive_status = 'Risiko Kadaluarsa';
                $product->inactive_color = 'orange';
            } elseif ($daysSinceLastSale >= 7) {
                $product->inactive_status = 'Lambat';
                $product->inactive_color = 'yellow';
            } else {
                $product->inactive_status = 'Aktif';
                $product->inactive_color = 'emerald';
            }

            return $product;
        })
        ->filter(function($product) {
            return $product->days_since_last_sale >= 7; 
        })
        ->sortByDesc('days_since_last_sale')
        ->take(4) // Show 4 worst
        ->values();

        // HEATMAP PENJUALAN
        $heatmapRaw = (clone $baseQuery)
            ->selectRaw('DAYOFWEEK(created_at) as day_of_week, HOUR(created_at) as hour, COUNT(*) as trx_count, SUM(total) as revenue')
            ->groupBy('day_of_week', 'hour')
            ->get();

        $heatmapMatrix = [];
        for ($d = 1; $d <= 7; $d++) {
            for ($h = 0; $h < 24; $h++) {
                $heatmapMatrix[$d][$h] = ['trx_count' => 0, 'revenue' => 0];
            }
        }

        $maxHeatmapTrx = 0;
        $dayTotals = [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0, 6 => 0, 7 => 0];
        $hourTotals = array_fill(0, 24, 0);

        foreach ($heatmapRaw as $data) {
            $isoDay = $data->day_of_week - 1;
            if ($isoDay === 0) $isoDay = 7; // MySQL Sun=1 -> ISO Sun=7

            $h = $data->hour;
            $trx = $data->trx_count;
            $rev = $data->revenue;

            $heatmapMatrix[$isoDay][$h] = ['trx_count' => $trx, 'revenue' => $rev];
            
            if ($trx > $maxHeatmapTrx) {
                $maxHeatmapTrx = $trx;
            }

            $dayTotals[$isoDay] += $trx;
            $hourTotals[$h] += $trx;
        }

        $dayNames = [
            1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 
            4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu', 7 => 'Minggu'
        ];

        $busiestDayIso = array_search(max($dayTotals), $dayTotals);
        $busiestDayName = max($dayTotals) > 0 ? $dayNames[$busiestDayIso] : '-';

        $nonZeroDays = array_filter($dayTotals);
        if (count($nonZeroDays) > 0) {
            $quietestDayIso = array_search(min($nonZeroDays), $dayTotals);
            $quietestDayName = $dayNames[$quietestDayIso];
        } else {
            $quietestDayName = '-';
        }

        $bestHour = array_search(max($hourTotals), $hourTotals);
        $bestHourText = max($hourTotals) > 0 ? sprintf('%02d:00 - %02d:59', $bestHour, $bestHour) : '-';

        $promoDayName = $quietestDayName !== '-' ? $quietestDayName : '-';

        $rankedDays = [];
        foreach ($dayTotals as $iso => $total) {
            if ($total > 0) {
                $rankedDays[] = ['day' => $dayNames[$iso], 'count' => $total];
            }
        }
        usort($rankedDays, fn($a, $b) => $b['count'] <=> $a['count']);

        $heatmapInsights = [
            'busiest_day' => $busiestDayName,
            'quietest_day' => $quietestDayName,
            'best_hour' => $bestHourText,
            'promo_day' => $promoDayName,
            'max_trx' => $maxHeatmapTrx,
            'ranked_days' => $rankedDays
        ];

        return view('reports.sales', compact(
            'transactions', 'summary', 'byPayment', 'topProducts', 'dateFrom', 'dateTo', 'salesPerDay', 
            'byCategory', 'peakHours', 'users', 'saldoLaci', 'saldoBank', 'totalExpense', 'netProfit',
            'topProfitableHour', 'topProfitProduct', 'worstMarginProduct', 'inactiveProducts',
            'heatmapMatrix', 'heatmapInsights', 'topExpenseCategory'
        ));
    }

    public function financial(Request $request)
    {
        $filter = $request->filter ?? $request->period ?? 'month';
        $start = is_array($request->start ?? $request->date_from) ? null : ($request->start ?? $request->date_from);
        $end = is_array($request->end ?? $request->date_to) ? null : ($request->end ?? $request->date_to);
        if ($start && $end) {
            $filter = 'custom';
        }

        $now = now();
        switch ($filter) {
            case 'today':
                $dateFrom = $now->copy()->startOfDay();
                $dateTo = $now->copy()->endOfDay();
                break;
            case 'yesterday':
                $dateFrom = $now->copy()->subDay()->startOfDay();
                $dateTo = $now->copy()->subDay()->endOfDay();
                break;
            case 'week':
                $dateFrom = $now->copy()->startOfWeek();
                $dateTo = $now->copy()->endOfWeek();
                break;
            case 'month':
                $dateFrom = $now->copy()->startOfMonth();
                $dateTo = $now->copy()->endOfMonth();
                break;
            case 'year':
                $dateFrom = $now->copy()->startOfYear();
                $dateTo = $now->copy()->endOfYear();
                break;
            case 'custom':
                $dateFrom = $start ? Carbon::parse($start)->startOfDay() : $now->copy()->startOfMonth();
                $dateTo = $end ? Carbon::parse($end)->endOfDay() : $now->copy()->endOfMonth();
                break;
            default:
                $dateFrom = $now->copy()->startOfDay();
                $dateTo = $now->copy()->endOfDay();
                break;
        }

        $daysDiff = $dateFrom->diffInDays($dateTo) + 1;
        $prevDateFrom = $dateFrom->copy()->subDays($daysDiff)->startOfDay();
        $prevDateTo = $dateFrom->copy()->subDay()->endOfDay();
        
        $month = $dateFrom->month;
        $year = $dateFrom->year;
        
        $worksheetId = session('active_worksheet_id');
        
        // 1. Core Summary & Comparison
        $summary = $this->financialService->getSummary($dateFrom, $dateTo, $worksheetId);
        $prevSummary = $this->financialService->getSummary($prevDateFrom, $prevDateTo, $worksheetId);
        
        $income = $summary->total_income;
        $expense = $summary->total_expense;
        $profit = $summary->net_profit;
        
        $growth = [
            'income' => $this->calculateGrowth($income, $prevSummary->total_income),
            'expense' => $this->calculateGrowth($expense, $prevSummary->total_expense),
            'profit' => $this->calculateGrowth($profit, $prevSummary->net_profit),
        ];

        // 2. Sales & COGS
        $salesQuery = Transaction::completed()
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->when($worksheetId, fn($q) => $q->where('worksheet_id', $worksheetId));
        
        $salesTotal = (clone $salesQuery)->sum('total');
        $transactionCount = (clone $salesQuery)->count();
        $cogs = TransactionItem::whereIn('transaction_id', (clone $salesQuery)->pluck('id'))
            ->sum(DB::raw('quantity * cost_price'));
        $grossProfit = $salesTotal - $cogs;

        // 3. Margins
        $margins = [
            'gross' => $salesTotal > 0 ? ($grossProfit / $salesTotal) * 100 : 0,
            'net' => $income > 0 ? ($profit / $income) * 100 : 0,
        ];

        // 4. Details for View
        $incomeDetails = (clone $salesQuery)
            ->selectRaw('payment_method, SUM(total) as total')
            ->groupBy('payment_method')
            ->get();

        $expenseDetails = Cashflow::where('transaction_category', 'expense')
            ->whereBetween('transaction_date', [$dateFrom, $dateTo])
            ->when($worksheetId, fn($q) => $q->where('worksheet_id', $worksheetId))
            ->selectRaw('category, SUM(amount) as total')
            ->groupBy('category')
            ->orderByDesc('total')
            ->get();

        $topProducts = $this->financialService->getTopProducts($dateFrom, $dateTo, $worksheetId);
        $trendData = $this->financialService->getTrend($year, $worksheetId);

        // 5. ROI Analytics
        $totalCapital = Capital::sum('total_amount');
        $allTimeNetProfit = $this->financialService->getAllTimeNetProfit($worksheetId);
        
        $firstTx = Cashflow::orderBy('transaction_date', 'asc')->first();
        $monthsActive = $firstTx ? max(1, $firstTx->transaction_date->diffInMonths(now()) + 1) : 1;
        $avgMonthlyProfit = $allTimeNetProfit / $monthsActive;
        $remainingToPayback = max(0, $totalCapital - $allTimeNetProfit);
        $paybackPeriodMonths = $avgMonthlyProfit > 0 ? $remainingToPayback / $avgMonthlyProfit : null;

        // 6. Business Health & Insights
        $health = 'healthy'; // healthy, warning, danger
        if ($profit < 0) $health = 'danger';
        elseif ($profit < ($income * 0.1)) $health = 'warning';

        $insights = $this->generateAiInsights($summary, [
            'top_products' => $topProducts,
            'expense_categories' => $expenseDetails,
        ]);

        return view('reports.financial', compact(
            'month', 'year', 'income', 'expense', 'profit', 'growth', 'margins',
            'salesTotal', 'transactionCount', 'cogs', 'grossProfit',
            'incomeDetails', 'expenseDetails', 'topProducts', 'trendData',
            'totalCapital', 'allTimeNetProfit', 'avgMonthlyProfit', 
            'remainingToPayback', 'paybackPeriodMonths', 'health', 'insights',
            'dateFrom', 'dateTo'
        ));
    }

    public function shifts(Request $request)
    {
        $isLiveShift = $request->shift === 'live' || $request->period === 'live';
        $period = $request->period ?? 'today';
        $activeShift = Shift::activeShiftForUser(auth()->id());
        
        $dfParam = is_array($request->date_from) ? null : $request->date_from;
        $dtParam = is_array($request->date_to) ? null : $request->date_to;
        if ($isLiveShift && $activeShift) {
            $dateFrom = $activeShift->opened_at->copy()->startOfDay();
            $dateTo = now()->endOfDay();
        } else {
            if ($dfParam && $dtParam) {
                $dateFrom = Carbon::parse($dfParam)->startOfDay();
                $dateTo = Carbon::parse($dtParam)->endOfDay();
            } else {
                // Handle preset periods
                switch ($period) {
                    case 'yesterday':
                        $dateFrom = now()->subDay()->startOfDay();
                        $dateTo = now()->subDay()->endOfDay();
                        break;
                    case 'week':
                        $dateFrom = now()->startOfWeek();
                        $dateTo = now()->endOfWeek();
                        break;
                    case 'month':
                        $dateFrom = now()->startOfMonth();
                        $dateTo = now()->endOfMonth();
                        break;
                    case 'year':
                        $dateFrom = now()->startOfYear();
                        $dateTo = now()->endOfYear();
                        break;
                    case 'today':
                    default:
                        $dateFrom = now()->startOfDay();
                        $dateTo = now()->endOfDay();
                        break;
                }
            }
        }
        
        $worksheetId = session('active_worksheet_id');

        $query = Shift::with(['opener', 'closer'])
            ->whereBetween('opened_at', [$dateFrom, $dateTo])
            ->when($worksheetId, fn($q) => $q->where('worksheet_id', $worksheetId))
            ->when($request->status && !is_array($request->status), fn($q) => $q->where('status', $request->status))
            ->when($request->user_id && !is_array($request->user_id), fn($q) => $q->where('opened_by', $request->user_id));

        $perPage = $request->input('per_page', 20);
        $shifts = $query->latest()->paginate($perPage)->withQueryString();

        $activeShiftsCount = (clone $query)->where('status', 'open')->count();
        $closedShifts = (clone $query)->where('status', 'closed')->get();
        $totalClosingCash = $closedShifts->sum('closing_cash');
        
        $totalSalesToday = Transaction::withoutGlobalScopes()->completed()
            ->when($isLiveShift && $activeShift, fn($q) => $q->where('shift_id', $activeShift->id))
            ->when(!($isLiveShift && $activeShift), fn($q) => $q->whereBetween('created_at', [$dateFrom, $dateTo]))
            ->when($worksheetId, fn($q) => $q->where('worksheet_id', $worksheetId))
            ->sum('total');

        $totalDiscrepancy = 0;
        foreach ($closedShifts as $s) {
            $cashSales = Transaction::withoutGlobalScopes()->where('shift_id', $s->id)->where('payment_method', 'cash')->where('status', 'completed')->sum('total');
            $cashExpenses = Cashflow::withoutGlobalScopes()->where('shift_id', $s->id)->where('type', 'expense')->where('source', 'pos_cash')->sum('amount');
            
            $cashTransfers = Cashflow::withoutGlobalScopes()
                ->where('shift_id', $s->id)
                ->where('source', 'pos_cash')
                ->where('category', '!=', 'Penjualan')
                ->where('transaction_category', '!=', 'expense')
                ->sum(DB::raw('CASE WHEN type = "income" THEN amount ELSE -amount END'));

            $expected = $s->opening_cash + $cashSales - $cashExpenses + $cashTransfers;
            $totalDiscrepancy += ($s->closing_cash - $expected);
        }

        $avgDiscrepancy = $closedShifts->count() > 0 ? $totalDiscrepancy / $closedShifts->count() : 0;
        $highestShift = $closedShifts->sortByDesc('total_sales')->first();

        $bestCashier = Transaction::withoutGlobalScopes()->completed()
            ->whereBetween('transactions.created_at', [$dateFrom, $dateTo])
            ->when($worksheetId, fn($q) => $q->where('transactions.worksheet_id', $worksheetId))
            ->join('users', 'transactions.user_id', '=', 'users.id')
            ->selectRaw('users.name, SUM(transactions.total) as total')
            ->groupBy('users.name')->orderByDesc('total')->first();

        $users = \App\Models\User::all();
        
        // Always mirror Cashflow dashboard: sum all pos_cash (income - expense).
        // This reflects transfers, adjustments, and POS sales in real-time.
        $laciBalance = (float) Cashflow::where('source', 'pos_cash')
            ->where('bank_sync_status', 'synced')
            ->sum(DB::raw('CASE WHEN type = "income" THEN amount ELSE -amount END'));

        $currentSales = 0;
        $currentCashExpenses = 0;
        $currentTotalExpenses = 0;
        $currentExpected = $laciBalance;

        $laciMovements = collect(); // transfers/adjustments during active shift

        if ($activeShift) {
            $sumLive = $this->financialService->getShiftSummary($activeShift->id, $worksheetId);
            $currentSales = $sumLive->cash_sales;
            $currentCashExpenses = $sumLive->cash_expense;
            $currentTotalExpenses = $sumLive->total_expense;

            $nonPosNet = (float) Cashflow::withoutGlobalScopes()
                ->where('shift_id', $activeShift->id)
                ->where('source', 'pos_cash')
                ->where('category', '!=', 'Penjualan')
                ->where('transaction_category', '!=', 'expense')
                ->sum(DB::raw('CASE WHEN type = "income" THEN amount ELSE -amount END'));

            $currentExpected = $activeShift->opening_cash + $currentSales - $currentCashExpenses + $nonPosNet;

            // Fetch non-POS cashflow movements during active shift (transfers, manual adjustments)
            $laciMovements = Cashflow::withoutGlobalScopes()
                ->where('shift_id', $activeShift->id)
                ->where('source', 'pos_cash')
                ->where('category', '!=', 'Penjualan')
                ->where('transaction_category', '!=', 'expense')
                ->orderBy('created_at')
                ->get();
        }

        // Total Expenses & Net Profit
        if ($isLiveShift && $activeShift) {
            $totalExpensesToday = $currentTotalExpenses;
            $cashExpensesToday = $sumLive->cash_expense;
            $bankExpensesToday = $sumLive->bank_expense;
            $netProfitToday = $sumLive->total_income - $totalExpensesToday;
        } else {
            $summary = $this->financialService->getSummary($dateFrom, $dateTo, $worksheetId);
            $totalExpensesToday = $summary->total_expense;
            $netProfitToday = $summary->net_profit;
            
            // Calculate cash vs bank expense breakdown for the period
            $expenseBreakdownQuery = Cashflow::where('transaction_category', 'expense')
                ->whereBetween('transaction_date', [$dateFrom->copy()->startOfDay(), $dateTo->copy()->endOfDay()])
                ->when($worksheetId, fn($q) => $q->where('worksheet_id', $worksheetId));
            $cashExpensesToday = (clone $expenseBreakdownQuery)->where('source', 'pos_cash')->sum('amount');
            $bankExpensesToday = (clone $expenseBreakdownQuery)->whereIn('source', ['pos_bank', 'transfer'])->sum('amount');
        }

        return view('reports.shifts', compact(
            'shifts', 'activeShiftsCount', 'totalClosingCash', 'totalSalesToday', 'totalDiscrepancy', 'closedShifts',
            'highestShift', 'avgDiscrepancy', 'bestCashier', 'activeShift', 'users', 'laciBalance',
            'totalExpensesToday', 'netProfitToday', 'cashExpensesToday', 'bankExpensesToday',
            'currentSales', 'currentCashExpenses', 'currentTotalExpenses', 'currentExpected',
            'laciMovements'
        ));
    }

    public function exportPdf(Request $request)
    {
        $reportData = $this->collectReportData($request);
        
        if ($request->has('preview')) {
            return view('reports.pdf.master_pdf', $reportData);
        }

        $pdf = Pdf::loadView('reports.pdf.master_pdf', $reportData)
                  ->setPaper('a4', $reportData['meta']['orientation']);

        $filename = 'LAPORAN_BISNIS_' . strtoupper(str_replace(' ', '_', $reportData['meta']['settings']['store_name'])) . '_' . now()->format('YmdHis') . '.pdf';
        
        return $pdf->download($filename);
    }

    public function exportExcel(Request $request)
    {
        $reportData = $this->collectReportData($request);
        $filename = 'LAPORAN_EXCEL_' . now()->format('YmdHis') . '.xlsx';
        
        return Excel::download(new ReportExport($reportData), $filename);
    }

    public function exportCsv(Request $request)
    {
        $reportData = $this->collectReportData($request);
        $filename = 'LAPORAN_CSV_' . now()->format('YmdHis') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($reportData) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            $summary = $reportData['summary_data'];
            $growth = $reportData['growth'];

            fputcsv($file, ['LAPORAN BISNIS', $reportData['meta']['settings']['store_name']]);
            fputcsv($file, ['Periode', $reportData['meta']['date_range']]);
            fputcsv($file, ['Unit', $reportData['meta']['worksheet_name']]);
            fputcsv($file, ['Dibuat', $reportData['meta']['export_date']]);
            fputcsv($file, ['']);

            fputcsv($file, ['RINGKASAN KEUANGAN']);
            fputcsv($file, ['Metrik', 'Nilai (Rp)', 'Pertumbuhan (%)']);
            fputcsv($file, ['Total Omzet', $summary->total_income, $growth['income'] . '%']);
            fputcsv($file, ['Total Pengeluaran', $summary->total_expense, $growth['expense'] . '%']);
            fputcsv($file, ['Laba Bersih', $summary->net_profit, $growth['profit'] . '%']);
            fputcsv($file, ['Jumlah Transaksi', $summary->total_count ?? 0, '']);
            fputcsv($file, ['']);

            if (!empty($reportData['transactions'])) {
                fputcsv($file, ['DETAIL PENJUALAN']);
                fputcsv($file, ['Invoice', 'Tanggal', 'Kasir', 'Pelanggan', 'Item', 'Metode', 'Total', 'Status']);
                foreach ($reportData['transactions'] as $trx) {
                    fputcsv($file, [
                        $trx->invoice_number ?? '-',
                        $trx->created_at->format('Y-m-d H:i'),
                        $trx->user->name ?? '-',
                        $trx->customer_name ?? '-',
                        $trx->items->count() ?? 0,
                        $trx->payment_method ?? '-',
                        $trx->total ?? 0,
                        $trx->status ?? '-',
                    ]);
                }
                fputcsv($file, ['']);
            }

            if (!empty($reportData['top_products'])) {
                fputcsv($file, ['RANKING PRODUK']);
                fputcsv($file, ['Produk', 'Qty Terjual', 'Total Omzet']);
                foreach ($reportData['top_products'] as $p) {
                    fputcsv($file, [$p->product_name, $p->total_qty, $p->total_revenue]);
                }
                fputcsv($file, ['']);
            }

            if (!empty($reportData['full_cashflow'])) {
                fputcsv($file, ['ARUS KAS']);
                fputcsv($file, ['Tanggal', 'Tipe', 'Kategori', 'Deskripsi', 'Nominal', 'Sumber']);
                foreach ($reportData['full_cashflow'] as $c) {
                    fputcsv($file, [
                        $c->transaction_date->format('Y-m-d'),
                        $c->type === 'income' ? 'Masuk' : 'Keluar',
                        $c->category ?? '-',
                        $c->description ?? '-',
                        $c->amount ?? 0,
                        $c->source ?? '-',
                    ]);
                }
                fputcsv($file, ['']);
            }

            if (!empty($reportData['expense_details'])) {
                fputcsv($file, ['DETAIL PENGELUARAN']);
                fputcsv($file, ['Tanggal', 'Kategori', 'Deskripsi', 'Sumber', 'Nominal']);
                foreach ($reportData['expense_details'] as $exp) {
                    fputcsv($file, [
                        $exp->transaction_date->format('Y-m-d H:i'),
                        $exp->category ?? '-',
                        $exp->description ?? '-',
                        \App\Models\Cashflow::sourceLabels()[$exp->source] ?? ucfirst($exp->source ?? '-'),
                        $exp->amount ?? 0,
                    ]);
                }
                $totalExpenseCsv = collect($reportData['expense_details'])->sum('amount');
                fputcsv($file, ['', '', '', 'TOTAL PENGELUARAN', $totalExpenseCsv]);
                fputcsv($file, ['']);
            }

            if (!empty($reportData['ai_insights'])) {
                fputcsv($file, ['AI BUSINESS INSIGHT']);
                fputcsv($file, ['Tipe', 'Judul', 'Deskripsi']);
                foreach ($reportData['ai_insights'] as $insight) {
                    if (is_array($insight)) {
                        fputcsv($file, [
                            strtoupper($insight['type'] ?? '-'),
                            $insight['title'] ?? '-',
                            $insight['text'] ?? '-',
                        ]);
                    }
                }
                fputcsv($file, ['']);
            }

            if (!empty($reportData['invoices'])) {
                fputcsv($file, ['ANALISA INVOICE']);
                fputcsv($file, ['Metrik', 'Nilai']);
                fputcsv($file, ['Invoice Lunas', $reportData['invoices']['lunas'] ?? 0]);
                fputcsv($file, ['Invoice Piutang', $reportData['invoices']['piutang'] ?? 0]);
                fputcsv($file, ['Total Piutang (Rp)', $reportData['invoices']['total_piutang'] ?? 0]);
                fputcsv($file, ['Uang Muka / DP', $reportData['invoices']['dp'] ?? 0]);
                fputcsv($file, ['']);
            }

            if (!empty($reportData['internal_mutations'])) {
                fputcsv($file, ['MUTASI INTERNAL']);
                fputcsv($file, ['Tanggal', 'Tipe', 'Kategori', 'Deskripsi', 'Sumber', 'Nominal']);
                foreach ($reportData['internal_mutations'] as $m) {
                    fputcsv($file, [
                        $m->transaction_date instanceof \Carbon\Carbon ? $m->transaction_date->format('Y-m-d H:i') : $m->transaction_date,
                        $m->type ?? '-',
                        $m->category ?? '-',
                        $m->description ?? '-',
                        $m->source ?? '-',
                        $m->amount ?? 0,
                    ]);
                }
                fputcsv($file, ['']);
            }

            if (!empty($reportData['roi_data'])) {
                fputcsv($file, ['ANALISA ROI & BEP']);
                fputcsv($file, ['Metrik', 'Nilai']);
                fputcsv($file, ['Modal Investasi (Rp)', $reportData['roi_data']['total_capital'] ?? 0]);
                fputcsv($file, ['Profit Akumulasi (Rp)', $reportData['roi_data']['total_profit'] ?? 0]);
                fputcsv($file, ['Status Investasi', $reportData['roi_data']['status'] ?? '-']);
                fputcsv($file, ['']);
            }

            if (!empty($reportData['shifts'])) {
                fputcsv($file, ['LAPORAN SHIFT']);
                fputcsv($file, ['ID', 'Kasir', 'Buka', 'Tutup', 'Kas Awal', 'Penjualan Tunai', 'Penjualan Non-Tunai', 'Pengeluaran Tunai', 'Pengeluaran Bank', 'Estimasi Kas Akhir', 'Aktual Kas Akhir', 'Selisih']);
                $csvTotalCashExp = 0; $csvTotalBankExp = 0;
                foreach ($reportData['shifts'] as $s) {
                    $rowCashSales = \App\Models\Transaction::withoutGlobalScopes()->where('shift_id', $s->id)->where('payment_method', 'cash')->where('status', 'completed')->sum('total');
                    $rowBankSales = \App\Models\Transaction::withoutGlobalScopes()->where('shift_id', $s->id)->where('payment_method', '!=', 'cash')->where('status', 'completed')->sum('total');
                    $rowCashExpenses = \App\Models\Cashflow::withoutGlobalScopes()->where('shift_id', $s->id)->where('type', 'expense')->where('source', 'pos_cash')->sum('amount');
                    $rowBankExpenses = \App\Models\Cashflow::withoutGlobalScopes()->where('shift_id', $s->id)->where('type', 'expense')->whereIn('source', ['pos_bank', 'transfer'])->sum('amount');
                    $csvTotalCashExp += $rowCashExpenses;
                    $csvTotalBankExp += $rowBankExpenses;
                    $expected = $s->opening_cash + $rowCashSales - $rowCashExpenses;
                    $selisih = $s->closed_at ? ($s->closing_cash - $expected) : 0;

                    fputcsv($file, [
                        $s->id,
                        $s->opener->name ?? '-',
                        $s->opened_at->format('Y-m-d H:i'),
                        $s->closed_at ? $s->closed_at->format('Y-m-d H:i') : 'Aktif',
                        $s->opening_cash,
                        $rowCashSales,
                        $rowBankSales,
                        $rowCashExpenses,
                        $rowBankExpenses,
                        $expected,
                        $s->closing_cash ?? 0,
                        $selisih,
                    ]);
                }
                $csvTotalShiftExp = $csvTotalCashExp + $csvTotalBankExp;
                $csvUnallocatedExp = max(0, ($reportData['summary_data']->total_expense ?? 0) - $csvTotalShiftExp);
                if ($csvUnallocatedExp > 0) {
                    fputcsv($file, ['', 'TOTAL PENGELUARAN DALAM SHIFT', '', '', '', '', '', $csvTotalCashExp, $csvTotalBankExp, $csvTotalShiftExp, '', '']);
                    fputcsv($file, ['', 'PENGELUARAN DI LUAR SHIFT', '', '', '', '', '', $csvUnallocatedExp, '', '', '', '']);
                    fputcsv($file, ['', 'TOTAL SELURUH PENGELUARAN', '', '', '', '', '', $reportData['summary_data']->total_expense ?? 0, '', '', '', '']);
                }
                fputcsv($file, ['']);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    protected function collectReportData(Request $request)
    {
        $request->validate([
            'sections' => 'required|array',
            'period' => 'required|string',
            'orientation' => 'required|in:portrait,landscape',
            'theme' => 'required|in:white,dark,blue',
        ]);

        // 1. Date Range
        $now = now();
        $dateFrom = $now->copy()->startOfDay();
        $dateTo = $now->copy()->endOfDay();
        $prevDateFrom = $now->copy()->subDay()->startOfDay();
        $prevDateTo = $now->copy()->subDay()->endOfDay();

        switch ($request->period) {
            case 'kemarin':
                $dateFrom = $now->copy()->subDay()->startOfDay();
                $dateTo = $now->copy()->subDay()->endOfDay();
                $prevDateFrom = $now->copy()->subDays(2)->startOfDay();
                $prevDateTo = $now->copy()->subDays(2)->endOfDay();
                break;
            case 'minggu_ini':
                $dateFrom = $now->copy()->startOfWeek();
                $dateTo = $now->copy()->endOfWeek();
                $prevDateFrom = $now->copy()->subWeek()->startOfWeek();
                $prevDateTo = $now->copy()->subWeek()->endOfWeek();
                break;
            case 'bulan_ini':
                $dateFrom = $now->copy()->startOfMonth();
                $dateTo = $now->copy()->endOfMonth();
                $prevDateFrom = $now->copy()->subMonth()->startOfMonth();
                $prevDateTo = $now->copy()->subMonth()->endOfMonth();
                break;
            case 'tahun_ini':
                $dateFrom = $now->copy()->startOfYear();
                $dateTo = $now->copy()->endOfYear();
                $prevDateFrom = $now->copy()->subYear()->startOfYear();
                $prevDateTo = $now->copy()->subYear()->endOfYear();
                break;
            case 'custom':
                $dateFrom = Carbon::parse($request->start_date)->startOfDay();
                $dateTo = Carbon::parse($request->end_date)->endOfDay();
                $diff = $dateFrom->diffInDays($dateTo) + 1;
                $prevDateFrom = $dateFrom->copy()->subDays($diff);
                $prevDateTo = $dateTo->copy()->subDays($diff);
                break;
        }

        $worksheetId = $request->worksheet_id;
        if ($worksheetId === 'all' || $worksheetId === '') {
            $worksheetId = null;
        }
        $sections = $request->sections;
        $data = [];

        // 2. Base Summaries & Growth
        $currentSummary = $this->financialService->getSummary($dateFrom, $dateTo, $worksheetId);
        $prevSummary = $this->financialService->getSummary($prevDateFrom, $prevDateTo, $worksheetId);
        
        $data['summary_data'] = $currentSummary;
        $data['growth'] = [
            'income' => $this->calculateGrowth($currentSummary->total_income, $prevSummary->total_income),
            'expense' => $this->calculateGrowth($currentSummary->total_expense, $prevSummary->total_expense),
            'profit' => $this->calculateGrowth($currentSummary->net_profit, $prevSummary->net_profit),
        ];

        // 3. Section Data
        $data = array_merge($data, $this->gatherSectionData($sections, $dateFrom, $dateTo, $worksheetId));

        // 4. Internal Mutations (Mutasi Laci/Bank)
        if (in_array('internal_mutations', $sections)) {
            $data['internal_mutations'] = Cashflow::withoutGlobalScopes()->whereBetween('transaction_date', [$dateFrom, $dateTo])
                ->where(function($q) {
                    $q->where('category', 'like', '%Transfer%')
                      ->orWhere('description', 'like', '%Transfer%');
                })
                ->when($worksheetId, fn($q) => $q->where('worksheet_id', $worksheetId))
                ->latest()->get();
        }

        // 5. Invoice Analytics
        if (in_array('invoice_analytics', $sections)) {
            $invoiceQuery = Transaction::withoutGlobalScopes()->whereBetween('created_at', [$dateFrom, $dateTo])
                ->when($worksheetId, fn($q) => $q->where('worksheet_id', $worksheetId));
            
            $data['invoices'] = [
                'lunas' => (clone $invoiceQuery)->completed()->count(),
                'piutang' => (clone $invoiceQuery)->piutang()->count(),
                'total_piutang' => (clone $invoiceQuery)->piutang()->sum(DB::raw('total - paid_so_far')),
                'dp' => (clone $invoiceQuery)->where('status', 'pending')->where('paid_so_far', '>', 0)->count(),
            ];
        }

        // 6. AI Insights
        if (in_array('ai_insights', $sections)) {
            $data['ai_insights'] = $this->generateAiInsights($currentSummary, $data);
        }

        // 7. Metadata
        $settings = Setting::getMultiple(['store_name', 'store_address', 'store_phone', 'store_email', 'store_website', 'store_logo', 'store_footer']);
        $data['chart_images'] = $request->chart_images ?? [];
        $data['meta'] = [
            'period_label' => str_replace('_', ' ', strtoupper($request->period)),
            'date_range' => $dateFrom->format('d/m/Y') . ' - ' . $dateTo->format('d/m/Y'),
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'export_date' => now()->format('d/m/Y H:i'),
            'admin_name' => auth()->user()->name,
            'orientation' => $request->orientation,
            'theme' => $request->theme,
            'sections' => $sections,
            'settings' => $settings,
            'worksheet_name' => ($worksheetId) ? Worksheet::find($worksheetId)->name : 'Semua Cabang',
            'include_signature' => $request->has('include_signature'),
            'signature_roles' => $request->signature_roles ?? ['Owner', 'Manager', 'Kasir'],
            'hash' => hash('crc32', now() . auth()->id()),
        ];

        return $data;
    }

    protected function gatherSectionData($sections, $dateFrom, $dateTo, $worksheetId)
    {
        $data = [];
        $queryTrx = Transaction::withoutGlobalScopes()->completed()->whereBetween('created_at', [$dateFrom, $dateTo])
            ->when($worksheetId, fn($q) => $q->where('worksheet_id', $worksheetId));

        if (in_array('history_trx', $sections)) {
            $data['transactions'] = (clone $queryTrx)->with(['user', 'items'])->latest()->get();
        }
        if (in_array('top_products', $sections)) {
            $data['top_products'] = TransactionItem::whereIn('transaction_id', (clone $queryTrx)->pluck('id'))
                ->selectRaw('product_name, SUM(quantity) as total_qty, SUM(subtotal) as total_revenue')
                ->groupBy('product_name')->orderByDesc('total_revenue')->take(10)->get();
        }
        if (in_array('payment_methods', $sections)) {
            $data['payment_methods'] = (clone $queryTrx)
                ->selectRaw('payment_method, COUNT(*) as count, SUM(total) as total')
                ->groupBy('payment_method')->get();
        }
        if (in_array('category_analysis', $sections)) {
            $data['expense_categories'] = Cashflow::withoutGlobalScopes()->where('transaction_category', 'expense')
                ->whereBetween('transaction_date', [$dateFrom, $dateTo])
                ->when($worksheetId, fn($q) => $q->where('worksheet_id', $worksheetId))
                ->selectRaw('category, SUM(amount) as total')
                ->groupBy('category')->orderByDesc('total')->get();
        }
        if (in_array('balances', $sections)) {
            $data['saldo_laci'] = Cashflow::withoutGlobalScopes()->where('source', 'pos_cash')->where('bank_sync_status', 'synced')->where('type', 'income')->sum('amount')
                                - Cashflow::withoutGlobalScopes()->where('source', 'pos_cash')->where('bank_sync_status', 'synced')->where('type', 'expense')->sum('amount');
            $data['saldo_bank'] = Cashflow::withoutGlobalScopes()->whereIn('source', ['pos_bank', 'transfer'])->where('bank_sync_status', 'synced')->where('type', 'income')->sum('amount')
                                - Cashflow::withoutGlobalScopes()->whereIn('source', ['pos_bank', 'transfer'])->where('bank_sync_status', 'synced')->where('type', 'expense')->sum('amount');
        }
        if (in_array('roi', $sections)) {
            $totalCapital = Capital::sum('total_amount');
            $allTimeNetProfit = $this->financialService->getAllTimeNetProfit($worksheetId);
            $data['roi_data'] = [
                'total_capital' => $totalCapital,
                'total_profit' => $allTimeNetProfit,
                'status' => ($allTimeNetProfit >= $totalCapital) ? 'SUDAH BEP' : 'DALAM PROSES'
            ];
        }
        if (in_array('shift_details', $sections)) {
            $data['shifts'] = Shift::withoutGlobalScopes()->whereBetween('opened_at', [$dateFrom, $dateTo])
                ->when($worksheetId, fn($q) => $q->where('worksheet_id', $worksheetId))
                ->with(['opener', 'closer'])->latest()->get();
        }
        if (in_array('full_cashflow', $sections)) {
            $data['full_cashflow'] = Cashflow::withoutGlobalScopes()->whereBetween('transaction_date', [$dateFrom, $dateTo])
                ->when($worksheetId, fn($q) => $q->where('worksheet_id', $worksheetId))
                ->latest()->get();
        }
        if (in_array('expense_details', $sections)) {
            $data['expense_details'] = Cashflow::withoutGlobalScopes()->where('transaction_category', 'expense')
                ->whereBetween('transaction_date', [$dateFrom, $dateTo])
                ->when($worksheetId, fn($q) => $q->where('worksheet_id', $worksheetId))
                ->latest()->get();
        }

        return $data;
    }

    protected function calculateGrowth($current, $previous)
    {
        if ($previous == 0) return $current > 0 ? 100 : 0;
        return round((($current - $previous) / $previous) * 100, 1);
    }

    protected function generateAiInsights($summary, $data)
    {
        $insights = [];
        $income = $summary->total_income;
        $expense = $summary->total_expense;
        $profit = $summary->net_profit;

        // 1. Profitability Insight
        if ($income > 0) {
            $margin = ($profit / $income) * 100;
            if ($margin > 30) {
                $insights[] = [
                    'type' => 'success',
                    'title' => 'Margin Laba Sangat Sehat',
                    'text' => "Bisnis Anda mencatat margin laba bersih " . round($margin) . "%. Ini jauh di atas rata-rata industri."
                ];
            } elseif ($margin < 10 && $margin > 0) {
                $insights[] = [
                    'type' => 'warning',
                    'title' => 'Margin Laba Tipis',
                    'text' => "Margin laba bersih hanya " . round($margin) . "%. Pertimbangkan untuk meninjau kembali efisiensi operasional."
                ];
            } elseif ($margin <= 0) {
                $insights[] = [
                    'type' => 'danger',
                    'title' => 'Bisnis Mengalami Kerugian',
                    'text' => "Pengeluaran melebihi pemasukan bulan ini. Segera identifikasi pengeluaran non-esensial."
                ];
            }
        }

        // 2. Expense Analysis
        if ($expense > 0) {
            $topExpense = collect($data['expense_categories'] ?? [])->first();
            if ($topExpense) {
                $ratio = ($topExpense->total / $expense) * 100;
                $insights[] = [
                    'type' => 'info',
                    'title' => 'Fokus Pengeluaran',
                    'text' => "Kategori '" . $topExpense->category . "' menyerap " . round($ratio) . "% dari total biaya Anda."
                ];
            }
        }

        // 3. Product Performance
        if (isset($data['top_products']) && count($data['top_products']) > 0) {
            $best = $data['top_products'][0];
            $insights[] = [
                'type' => 'success',
                'title' => 'Produk Primadona',
                'text' => "Produk '" . $best->product_name . "' adalah kontributor profit terbesar periode ini."
            ];
        }

        // 4. Operational Recommendations
        if ($income > 0 && ($expense / $income) > 0.7) {
            $insights[] = [
                'type' => 'warning',
                'title' => 'Rekomendasi Efisiensi',
                'text' => "Rasio biaya operasional terlalu tinggi (70%+). Disarankan melakukan audit pada pengeluaran variabel."
            ];
        }

        return $insights;
    }
}

