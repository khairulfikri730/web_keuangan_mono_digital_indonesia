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
        $dateFrom = $request->date_from ? Carbon::parse($request->date_from) : now();
        $dateTo = $request->date_to ? Carbon::parse($request->date_to) : now();

        $baseQuery = Transaction::completed()
            ->whereBetween('created_at', [$dateFrom->copy()->startOfDay(), $dateTo->copy()->endOfDay()])
            ->when($request->payment_method, fn($q) => $q->where('payment_method', $request->payment_method))
            ->when($request->filled('user_id'), fn($q) => $q->where('user_id', $request->user_id));

        $worksheetId = session('active_worksheet_id');
        if ($worksheetId && $worksheetId !== 'all') {
            $baseQuery->where('worksheet_id', $worksheetId);
        }

        $transactions = (clone $baseQuery)->with(['user', 'items'])->latest()->paginate(20)->withQueryString();

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

        $topProducts = TransactionItem::whereIn('transaction_id', (clone $baseQuery)->pluck('id'))
            ->selectRaw('product_name, SUM(quantity) as total_qty, SUM(subtotal) as total_revenue, SUM(quantity * cost_price) as total_cost')
            ->groupBy('product_name')->orderByDesc('total_revenue')->take(10)->get();

        $peakHours = (clone $baseQuery)
            ->selectRaw('HOUR(created_at) as hour, COUNT(*) as count')
            ->groupBy('hour')->orderByDesc('count')->take(5)->get();

        // SALDO LACI & BANK
        $saldoLaci = Cashflow::where('source', 'pos_cash')->where('bank_sync_status', 'synced')->where('type', 'income')->sum('amount')
                   - Cashflow::where('source', 'pos_cash')->where('bank_sync_status', 'synced')->where('type', 'expense')->sum('amount');

        $saldoBank = Cashflow::whereIn('source', ['pos_bank', 'transfer'])->where('bank_sync_status', 'synced')->where('type', 'income')->sum('amount')
                   - Cashflow::whereIn('source', ['pos_bank', 'transfer'])->where('bank_sync_status', 'synced')->where('type', 'expense')->sum('amount');


        // Unified Financial Summary
        $finSummary = $this->financialService->getSummary($dateFrom, $dateTo, $worksheetId);
        $totalExpense = $finSummary->total_expense;
        $netProfit = $finSummary->net_profit;

        $users = \App\Models\User::all();

        return view('reports.sales', compact(
            'transactions', 'summary', 'byPayment', 'topProducts', 'dateFrom', 'dateTo', 'salesPerDay', 
            'byCategory', 'peakHours', 'users', 'saldoLaci', 'saldoBank', 'totalExpense', 'netProfit'
        ));
    }

    public function financial(Request $request)
    {
        $month = $request->month ?? now()->month;
        $year = $request->year ?? now()->year;
        
        $dateFrom = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $dateTo = $dateFrom->copy()->endOfMonth();
        
        $worksheetId = session('active_worksheet_id');
        
        // 1. Core Summary
        $summary = $this->financialService->getSummary($dateFrom, $dateTo, $worksheetId);
        $totalIncome = $summary->total_income;
        $totalExpense = $summary->total_expense;
        $profit = $summary->net_profit;

        // 2. Sales & COGS
        $salesQuery = Transaction::completed()
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->when($worksheetId && $worksheetId !== 'all', fn($q) => $q->where('worksheet_id', $worksheetId));
        
        $salesTotal = (clone $salesQuery)->sum('total');
        $cogs = TransactionItem::whereIn('transaction_id', (clone $salesQuery)->pluck('id'))
            ->sum(DB::raw('quantity * cost_price'));
        $grossProfit = $salesTotal - $cogs;

        // 3. Details for View
        $incomeDetails = (clone $salesQuery)
            ->selectRaw('payment_method, SUM(total) as total')
            ->groupBy('payment_method')
            ->get();

        $expenseDetails = Cashflow::where('type', 'expense')
            ->whereBetween('transaction_date', [$dateFrom, $dateTo])
            ->when($worksheetId && $worksheetId !== 'all', fn($q) => $q->where('worksheet_id', $worksheetId))
            ->whereNotIn('category', ['Transfer Internal', 'Refund / Retur', 'Transfer Bank'])
            ->where(function($q) {
                $q->where('category', 'not like', '%Transfer%')
                  ->where('description', 'not like', '%Transfer%');
            })
            ->selectRaw('category, SUM(amount) as total')
            ->groupBy('category')
            ->orderByDesc('total')
            ->get();

        $monthlyUsagesSum = \App\Models\MonthlyUsage::whereBetween('expense_date', [$dateFrom, $dateTo])
            ->when($worksheetId && $worksheetId !== 'all', fn($q) => $q->where('worksheet_id', $worksheetId))
            ->sum('usage_amount');

        // 4. ROI Analytics
        $totalCapital = Capital::sum('total_amount');
        $allTimeNetProfit = $this->financialService->getAllTimeNetProfit($worksheetId);
        
        $firstTx = Cashflow::orderBy('transaction_date', 'asc')->first();
        $monthsActive = $firstTx ? max(1, $firstTx->transaction_date->diffInMonths(now()) + 1) : 1;
        $avgMonthlyProfit = $allTimeNetProfit / $monthsActive;
        $remainingToPayback = max(0, $totalCapital - $allTimeNetProfit);
        $paybackPeriodMonths = $avgMonthlyProfit > 0 ? $remainingToPayback / $avgMonthlyProfit : null;

        // Map variables to view expectations
        $income = $totalIncome;
        $expense = $totalExpense;

        return view('reports.financial', compact(
            'totalIncome', 'totalExpense', 'profit', 'incomeDetails', 'expenseDetails', 'month', 'year',
            'salesTotal', 'cogs', 'grossProfit', 'income', 'expense', 'monthlyUsagesSum',
            'totalCapital', 'allTimeNetProfit', 'avgMonthlyProfit', 'paybackPeriodMonths'
        ));
    }

    public function shifts(Request $request)
    {
        $dateFrom = $request->date_from ? Carbon::parse($request->date_from)->startOfDay() : now()->startOfMonth();
        $dateTo = $request->date_to ? Carbon::parse($request->date_to)->endOfDay() : now()->endOfDay();
        
        $worksheetId = session('active_worksheet_id');

        $query = Shift::with(['opener', 'closer'])
            ->whereBetween('opened_at', [$dateFrom, $dateTo])
            ->when($worksheetId && $worksheetId !== 'all', fn($q) => $q->where('worksheet_id', $worksheetId))
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->user_id, fn($q) => $q->where('user_id', $request->user_id));

        $shifts = $query->latest()->paginate(20)->withQueryString();

        $activeShiftsCount = (clone $query)->where('status', 'open')->count();
        $closedShifts = (clone $query)->where('status', 'closed')->get();
        $totalClosingCash = $closedShifts->sum('closing_cash');
        
        $totalSalesToday = Transaction::completed()
            ->whereBetween('created_at', [now()->startOfDay(), now()->endOfDay()])
            ->when($worksheetId && $worksheetId !== 'all', fn($q) => $q->where('worksheet_id', $worksheetId))
            ->sum('total');

        $totalDiscrepancy = 0;
        foreach ($closedShifts as $s) {
            $expected = $s->opening_cash + Transaction::where('shift_id', $s->id)->where('payment_method', 'cash')->where('status', 'completed')->sum('total');
            $totalDiscrepancy += ($s->closing_cash - $expected);
        }

        $avgDiscrepancy = $closedShifts->count() > 0 ? $totalDiscrepancy / $closedShifts->count() : 0;
        $highestShift = $closedShifts->sortByDesc('total_sales')->first();

        $bestCashier = Transaction::completed()
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->when($worksheetId && $worksheetId !== 'all', fn($q) => $q->where('worksheet_id', $worksheetId))
            ->join('users', 'transactions.user_id', '=', 'users.id')
            ->selectRaw('users.name, SUM(transactions.total) as total')
            ->groupBy('users.name')->orderByDesc('total')->first();

        $activeShift = Shift::with('opener')->where('status', 'open')->latest()->first();
        $users = \App\Models\User::all();

        return view('reports.shifts', compact(
            'shifts', 'activeShiftsCount', 'totalClosingCash', 'totalSalesToday', 'totalDiscrepancy',
            'highestShift', 'avgDiscrepancy', 'bestCashier', 'activeShift', 'users'
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
            $data['internal_mutations'] = Cashflow::whereBetween('transaction_date', [$dateFrom, $dateTo])
                ->where(function($q) {
                    $q->where('category', 'like', '%Transfer%')
                      ->orWhere('description', 'like', '%Transfer%');
                })
                ->when($worksheetId && $worksheetId !== 'all', fn($q) => $q->where('worksheet_id', $worksheetId))
                ->latest()->get();
        }

        // 5. Invoice Analytics
        if (in_array('invoice_analytics', $sections)) {
            $invoiceQuery = Transaction::whereBetween('created_at', [$dateFrom, $dateTo])
                ->when($worksheetId && $worksheetId !== 'all', fn($q) => $q->where('worksheet_id', $worksheetId));
            
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
            'export_date' => now()->format('d/m/Y H:i'),
            'admin_name' => auth()->user()->name,
            'orientation' => $request->orientation,
            'theme' => $request->theme,
            'sections' => $sections,
            'settings' => $settings,
            'worksheet_name' => ($worksheetId && $worksheetId !== 'all') ? Worksheet::find($worksheetId)->name : 'Semua Cabang',
            'include_signature' => $request->has('include_signature'),
            'signature_roles' => $request->signature_roles ?? ['Owner', 'Manager', 'Kasir'],
            'hash' => hash('crc32', now() . auth()->id()),
        ];

        return $data;
    }

    protected function gatherSectionData($sections, $dateFrom, $dateTo, $worksheetId)
    {
        $data = [];
        $queryTrx = Transaction::completed()->whereBetween('created_at', [$dateFrom, $dateTo])
            ->when($worksheetId && $worksheetId !== 'all', fn($q) => $q->where('worksheet_id', $worksheetId));

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
            $data['expense_categories'] = Cashflow::where('type', 'expense')
                ->whereBetween('transaction_date', [$dateFrom, $dateTo])
                ->whereNotIn('category', ['Transfer Internal', 'Refund / Retur', 'Transfer Bank'])
                ->when($worksheetId && $worksheetId !== 'all', fn($q) => $q->where('worksheet_id', $worksheetId))
                ->selectRaw('category, SUM(amount) as total')
                ->groupBy('category')->orderByDesc('total')->get();
        }
        if (in_array('balances', $sections)) {
            $data['saldo_laci'] = Cashflow::where('source', 'pos_cash')->where('bank_sync_status', 'synced')->where('type', 'income')->sum('amount')
                                - Cashflow::where('source', 'pos_cash')->where('bank_sync_status', 'synced')->where('type', 'expense')->sum('amount');
            $data['saldo_bank'] = Cashflow::whereIn('source', ['pos_bank', 'transfer'])->where('bank_sync_status', 'synced')->where('type', 'income')->sum('amount')
                                - Cashflow::whereIn('source', ['pos_bank', 'transfer'])->where('bank_sync_status', 'synced')->where('type', 'expense')->sum('amount');
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
            $data['shifts'] = Shift::whereBetween('opened_at', [$dateFrom, $dateTo])
                ->when($worksheetId && $worksheetId !== 'all', fn($q) => $q->where('worksheet_id', $worksheetId))
                ->with(['opener', 'closer'])->latest()->get();
        }
        if (in_array('full_cashflow', $sections)) {
            $data['full_cashflow'] = Cashflow::whereBetween('transaction_date', [$dateFrom, $dateTo])
                ->when($worksheetId && $worksheetId !== 'all', fn($q) => $q->where('worksheet_id', $worksheetId))
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
        
        // Income Insight
        if ($summary->total_income > 0) {
            $bestPayment = collect($data['payment_methods'] ?? [])->sortByDesc('total')->first();
            if ($bestPayment) {
                $insights[] = "Metode pembayaran " . strtoupper($bestPayment->payment_method) . " mendominasi transaksi sebesar " . round(($bestPayment->total / $summary->total_income) * 100) . "%.";
            }
        }

        // Expense Insight
        if ($summary->total_expense > 0) {
            $topExpense = collect($data['expense_categories'] ?? [])->sortByDesc('total')->first();
            if ($topExpense) {
                $insights[] = "Pengeluaran terbesar berasal dari kategori " . $topExpense->category . " (" . round(($topExpense->total / $summary->total_expense) * 100) . "% dari total biaya).";
            }
        }

        // Product Insight
        if (isset($data['top_products']) && $data['top_products']->count() > 0) {
            $top = $data['top_products']->first();
            $insights[] = "Produk paling laris periode ini adalah '" . $top->product_name . "' dengan penjualan sebanyak " . number_format($top->total_qty) . " unit.";
        }

        // Profit Insight
        if ($summary->net_profit > 0) {
            $insights[] = "Bisnis mencatatkan margin laba bersih yang positif. Pertahankan efisiensi biaya operasional.";
        } else {
            $insights[] = "Laba bersih berada di angka negatif/nol. Disarankan meninjau kembali biaya variabel dan strategi harga.";
        }

        return $insights;
    }
}
