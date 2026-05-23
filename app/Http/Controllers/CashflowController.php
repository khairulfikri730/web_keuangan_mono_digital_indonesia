<?php

namespace App\Http\Controllers;

use App\Models\Cashflow;
use App\Models\CashTransaction;
use App\Models\Transaction;
use App\Services\FinancialReportService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CashflowController extends Controller
{
    protected $financialService;

    public function __construct(FinancialReportService $financialService)
    {
        $this->financialService = $financialService;
    }

    private function baseQuery($filter, $source, $start = null, $end = null)
    {
        $query = Cashflow::with(['user', 'worksheet']);

        switch ($filter) {
            case 'today':
                $query->whereDate('transaction_date', Carbon::today());
                break;
            case 'yesterday':
                $query->whereDate('transaction_date', Carbon::yesterday());
                break;
            case 'week':
                $query->whereBetween('transaction_date', [
                    Carbon::now()->startOfWeek(),
                    Carbon::now()->endOfWeek(),
                ]);
                break;
            case 'month':
                $query->whereMonth('transaction_date', Carbon::now()->month)
                    ->whereYear('transaction_date', Carbon::now()->year);
                break;
            case 'year':
                $query->whereYear('transaction_date', Carbon::now()->year);
                break;
            case 'custom':
                if ($start && $end) {
                    $query->whereBetween('transaction_date', [$start, $end]);
                }
                break;
        }

        if ($source !== 'all') {
            $query->where('source', $source);
        }

        // Worksheet Filter
        if ($activeWorksheetId = session('active_worksheet_id')) {
            $query->where('worksheet_id', $activeWorksheetId);
        }

        return $query;
    }

    public function index(Request $request)
    {
        $filter = $request->filter ?? $request->period ?? 'today';
        $start = is_array($request->start ?? $request->date_from) ? null : ($request->start ?? $request->date_from);
        $end = is_array($request->end ?? $request->date_to) ? null : ($request->end ?? $request->date_to);
        if ($start && $end) {
            $filter = 'custom';
        }
        $source = $request->source ?? 'all';
        $query = $this->baseQuery($filter, $source, $start, $end);

        $cashflows = (clone $query)->latest('transaction_date')->paginate(20)->withQueryString();
        
        // 1. Get Unified Summary
        $dateRange = $this->getDateRange($filter, $start, $end);
        $worksheetId = session('active_worksheet_id');
        
        $finSummary = $this->financialService->getSummary($dateRange['from'], $dateRange['to'], $worksheetId);
        
        $totalIncome = $finSummary->total_income;
        $totalExpense = $finSummary->total_expense;
        $netProfit = $finSummary->net_profit;

        // ROI Analysis Data (always all-time for payback tracking)
        $totalInvestment = \App\Models\Capital::sum('total_amount');
        $latestCapital = \App\Models\Capital::latest()->first();
        $allTimeNetProfit = $this->financialService->getAllTimeNetProfit($worksheetId);
        $totalCollectedProfit = $allTimeNetProfit;
        $remainingCapital = max(0, $totalInvestment - $totalCollectedProfit);
        
        $firstTx = Cashflow::orderBy('transaction_date', 'asc')->first();
        $monthsActive = $firstTx ? max(1, $firstTx->transaction_date->diffInMonths(now()) + 1) : 1;
        $avgMonthlyProfit = $totalCollectedProfit / $monthsActive;
        $paybackMonths = $avgMonthlyProfit > 0 ? ceil($remainingCapital / $avgMonthlyProfit) : null;

        // ROI for current period
        $periodROI = $totalInvestment > 0 ? round(($netProfit / $totalInvestment) * 100, 1) : 0;

        // Target Payback Analysis
        $targetPaybackMonths = 12;
        if ($worksheetId) {
            $ws = \App\Models\Worksheet::find($worksheetId);
            $targetPaybackMonths = $ws->target_payback_months ?? 12;
        }

        $requiredMonthlyProfit = $targetPaybackMonths > 0 ? $remainingCapital / $targetPaybackMonths : 0;
        $requiredDailyProfit = $requiredMonthlyProfit / 30;
        $profitGap = $avgMonthlyProfit - $requiredMonthlyProfit;
        $incomeByCategory = (clone $query)->where('type', 'income')
            ->selectRaw('category, SUM(amount) as total')->groupBy('category')->orderByDesc('total')->get();

        $expenseByCategory = (clone $query)->where('transaction_category', 'expense')
            ->selectRaw('category, SUM(amount) as total')->groupBy('category')->orderByDesc('total')->get();

        $totalAdjIn = (clone $query)->where('transaction_category', 'adjustment')->where('type', 'income')->sum('amount');
        $totalAdjOut = (clone $query)->where('transaction_category', 'adjustment')->where('type', 'expense')->sum('amount');

        $chartQuery = (clone $query)
            ->selectRaw("DATE(transaction_date) as date")
            ->selectRaw("SUM(CASE WHEN transaction_category = 'income' THEN amount ELSE 0 END) as income")
            ->selectRaw("SUM(CASE WHEN transaction_category = 'expense' THEN amount ELSE 0 END) as expense")
            ->groupBy(DB::raw("DATE(transaction_date)"))
            ->orderBy('date', 'asc')
            ->get();

        $chartDates = $chartQuery->map(fn($r) => Carbon::parse($r->date)->format('d M'))->toArray();
        $chartIncome = $chartQuery->pluck('income')->toArray();
        $chartExpense = $chartQuery->pluck('expense')->toArray();

        $daysCount = max($chartQuery->count(), 1);
        $avgIncome = $totalIncome / $daysCount;

        $biggestExpense = (clone $query)->where('transaction_category', 'expense')
            ->orderByDesc('amount')->first();

        $trend = $this->calculateTrend($filter, $source, $start, $end);

        // SALDO = ALL-TIME synced (kondisi uang di laci/bank saat ini — TIDAK berubah saat filter diganti)
        $saldoLaciSyncedIncome  = Cashflow::where('source', 'pos_cash')->where('bank_sync_status', 'synced')->where('type', 'income')->sum('amount');
        $saldoLaciSyncedExpense = Cashflow::where('source', 'pos_cash')->where('bank_sync_status', 'synced')->where('type', 'expense')->sum('amount');
        $saldoLaciSynced = $saldoLaciSyncedIncome - $saldoLaciSyncedExpense;

        $saldoLaciPending = Cashflow::where('source', 'pos_cash')->where('bank_sync_status', 'pending')->where('type', 'income')->sum('amount');
        $pendingLaciCount = Cashflow::where('source', 'pos_cash')->where('bank_sync_status', 'pending')->where('type', 'income')->count();

        $saldoBankSyncedIncome  = Cashflow::whereIn('source', ['pos_bank', 'transfer'])->where('bank_sync_status', 'synced')->where('type', 'income')->sum('amount');
        $saldoBankSyncedExpense = Cashflow::whereIn('source', ['pos_bank', 'transfer'])->where('bank_sync_status', 'synced')->where('type', 'expense')->sum('amount');
        $saldoBankSynced = $saldoBankSyncedIncome - $saldoBankSyncedExpense;

        $saldoBankPending = Cashflow::whereIn('source', ['pos_bank', 'transfer'])->where('bank_sync_status', 'pending')->where('type', 'income')->sum('amount');
        $pendingBankCount = Cashflow::whereIn('source', ['pos_bank', 'transfer'])->where('bank_sync_status', 'pending')->where('type', 'income')->count();

        $pendingQris     = Cashflow::where('source', 'pos_bank')->where('bank_sync_status', 'pending')->where('type', 'income')->sum('amount');
        $pendingTransfer = Cashflow::where('source', 'transfer')->where('bank_sync_status', 'pending')->where('type', 'income')->sum('amount');

        $saldoLaci = $saldoLaciSynced;
        $saldoBank = $saldoBankSynced;

        // Omset Metrics
        $txQuery = $this->baseTransactionQuery($filter, $start, $end);
        $incomeQris     = (clone $txQuery)->whereIn('payment_method', ['qris', 'debit'])->sum('total');
        $incomeCash     = (clone $txQuery)->where('payment_method', 'cash')->sum('total');
        $incomeTransfer = (clone $txQuery)->where('payment_method', 'transfer')->sum('total');

        return view('cashflow.index', compact(
            'cashflows', 'totalIncome', 'totalExpense', 'netProfit',
            'filter', 'start', 'end', 'source', 'incomeByCategory', 'expenseByCategory',
            'chartDates', 'chartIncome', 'chartExpense',
            'biggestExpense', 'avgIncome', 'trend',
            'saldoLaci', 'saldoBank', 'saldoLaciSynced', 'saldoLaciPending', 'saldoBankSynced', 'saldoBankPending',
            'pendingQris', 'pendingTransfer', 'pendingBankCount', 'pendingLaciCount',
            'totalInvestment', 'totalCollectedProfit', 'remainingCapital', 'paybackMonths',
            'targetPaybackMonths', 'requiredMonthlyProfit', 'requiredDailyProfit', 'profitGap', 'avgMonthlyProfit',
            'incomeQris', 'incomeCash', 'incomeTransfer', 'totalAdjIn', 'totalAdjOut',
            'latestCapital', 'periodROI'
        ));
    }

    private function getDateRange($filter, $start = null, $end = null)
    {
        $now = now();
        switch ($filter) {
            case 'today': return ['from' => $now->copy()->startOfDay(), 'to' => $now->copy()->endOfDay()];
            case 'yesterday': return ['from' => $now->copy()->subDay()->startOfDay(), 'to' => $now->copy()->subDay()->endOfDay()];
            case 'week': return ['from' => $now->copy()->startOfWeek(), 'to' => $now->copy()->endOfWeek()];
            case 'month': return ['from' => $now->copy()->startOfMonth(), 'to' => $now->copy()->endOfMonth()];
            case 'year': return ['from' => $now->copy()->startOfYear(), 'to' => $now->copy()->endOfYear()];
            case 'custom': return [
                'from' => $start ? Carbon::parse($start)->startOfDay() : $now->copy()->startOfMonth(),
                'to' => $end ? Carbon::parse($end)->endOfDay() : $now->copy()->endOfMonth()
            ];
            default: return ['from' => $now->copy()->startOfMonth(), 'to' => $now->copy()->endOfMonth()];
        }
    }

    public function getData(Request $request)
    {
        $filter = $request->filter ?? $request->period ?? 'today';
        $start = is_array($request->start ?? $request->date_from) ? null : ($request->start ?? $request->date_from);
        $end = is_array($request->end ?? $request->date_to) ? null : ($request->end ?? $request->date_to);
        if ($start && $end) {
            $filter = 'custom';
        }
        $source = $request->source ?? 'all';
        $page = $request->page ?? 1;
        
        $dateRange = $this->getDateRange($filter, $start, $end);
        $worksheetId = session('active_worksheet_id');
        
        $finSummary = $this->financialService->getSummary($dateRange['from'], $dateRange['to'], $worksheetId);
        
        $totalIncome = $finSummary->total_income;
        $totalExpense = $finSummary->total_expense;
        $netProfit = $finSummary->net_profit;

        $txQuery = $this->baseTransactionQuery($filter, $start, $end);
        $incomeQris = (clone $txQuery)->whereIn('payment_method', ['qris', 'debit'])->sum('total');
        $incomeCash = (clone $txQuery)->where('payment_method', 'cash')->sum('total');
        $incomeTransfer = (clone $txQuery)->where('payment_method', 'transfer')->sum('total');

        // SALDO = ALL-TIME synced (tidak berubah saat filter diganti)
        $saldoLaci = Cashflow::where('source', 'pos_cash')->where('bank_sync_status', 'synced')->where('type', 'income')->sum('amount')
                   - Cashflow::where('source', 'pos_cash')->where('bank_sync_status', 'synced')->where('type', 'expense')->sum('amount');

        $saldoBankSynced = Cashflow::whereIn('source', ['pos_bank', 'transfer'])->where('bank_sync_status', 'synced')->where('type', 'income')->sum('amount')
                         - Cashflow::whereIn('source', ['pos_bank', 'transfer'])->where('bank_sync_status', 'synced')->where('type', 'expense')->sum('amount');

        $query = $this->baseQuery($filter, $source, $start, $end);
        $chartQuery = (clone $query)
            ->selectRaw("DATE(transaction_date) as date")
            ->selectRaw("SUM(CASE WHEN transaction_category = 'income' THEN amount ELSE 0 END) as income")
            ->selectRaw("SUM(CASE WHEN transaction_category = 'expense' THEN amount ELSE 0 END) as expense")
            ->groupBy(DB::raw("DATE(transaction_date)"))
            ->orderBy('date', 'asc')
            ->get();

        $totalAdjIn = (clone $query)->where('transaction_category', 'adjustment')->where('type', 'income')->sum('amount');
        $totalAdjOut = (clone $query)->where('transaction_category', 'adjustment')->where('type', 'expense')->sum('amount');

        $chartDates = $chartQuery->map(fn($r) => Carbon::parse($r->date)->format('d M'))->toArray();
        $chartIncome = $chartQuery->pluck('income')->toArray();
        $chartExpense = $chartQuery->pluck('expense')->toArray();

        $daysCount = max($chartQuery->count(), 1);
        $avgIncome = $totalIncome / $daysCount;

        $expenseByCategory = (clone $query)->where('transaction_category', 'expense')
            ->selectRaw('category, SUM(amount) as total')->groupBy('category')->orderByDesc('total')->get();

        $incomeByCategory = (clone $query)->where('transaction_category', 'income')
            ->selectRaw('category, SUM(amount) as total')->groupBy('category')->orderByDesc('total')->get();

        $biggestExpense = (clone $query)->where('transaction_category', 'expense')
            ->orderByDesc('amount')->first();

        $trend = $this->calculateTrend($filter, $source, $start, $end);

        $cashflows = (clone $query)->latest('transaction_date')->paginate(20, page: $page)->withQueryString();
        $transactionsHtml = view('cashflow._transactions', [
            'cashflows' => $cashflows,
        ])->render();

        $paginationHtml = '';
        if ($cashflows->hasPages()) {
            $paginationHtml = $cashflows->links('pagination::tailwind')->render();
        }

        return response()->json([
            'summary' => [
                'totalIncome' => $totalIncome,
                'totalExpense' => $totalExpense,
                'netProfit' => $netProfit,
                'totalIncomeFmt' => number_format($totalIncome, 0, ',', '.'),
                'totalExpenseFmt' => number_format($totalExpense, 0, ',', '.'),
                'netProfitFmt' => number_format(abs($netProfit), 0, ',', '.'),
                'netProfitNegative' => $netProfit < 0,
                'incomeQrisFmt' => number_format($incomeQris, 0, ',', '.'),
                'incomeCashFmt' => number_format($incomeCash, 0, ',', '.'),
                'incomeTransferFmt' => number_format($incomeTransfer, 0, ',', '.'),
                'saldoLaciFmt' => number_format($saldoLaci, 0, ',', '.'),
                'saldoBankSyncedFmt' => number_format($saldoBankSynced, 0, ',', '.'),
                'totalAdjIn' => (float) $totalAdjIn,
                'totalAdjOut' => (float) $totalAdjOut,
                'totalAdjInFmt' => number_format($totalAdjIn, 0, ',', '.'),
                'totalAdjOutFmt' => number_format($totalAdjOut, 0, ',', '.'),
            ],
            'chart' => [
                'labels' => $chartDates,
                'income' => $chartIncome,
                'expense' => $chartExpense,
            ],
            'insights' => [
                'avgIncome' => $avgIncome,
                'avgIncomeFmt' => number_format($avgIncome, 0, ',', '.'),
                'totalTransactions' => $cashflows->total(),
                'trend' => $trend,
                'biggestExpense' => $biggestExpense ? [
                    'category' => $biggestExpense->category,
                    'description' => $biggestExpense->description,
                    'amount' => (float) $biggestExpense->amount,
                    'amountFmt' => number_format($biggestExpense->amount, 0, ',', '.'),
                ] : null,
                'expenseCategories' => $expenseByCategory->map(fn($c) => [
                    'category' => $c->category,
                    'total' => (float) $c->total,
                    'totalFmt' => number_format($c->total, 0, ',', '.'),
                ]),
                'incomeCategories' => $incomeByCategory->map(fn($c) => [
                    'category' => $c->category,
                    'total' => (float) $c->total,
                    'totalFmt' => number_format($c->total, 0, ',', '.'),
                ]),
                'ratio' => $totalExpense > 0
                    ? round($totalIncome / $totalExpense, 2)
                    : ($totalIncome > 0 ? '∞' : '0'),
            ],
            'transactions' => $transactionsHtml,
            'pagination' => $paginationHtml,
        ]);
    }

    protected function baseTransactionQuery($filter, $start = null, $end = null)
    {
        $query = Transaction::completed();

        if ($filter === 'today') {
            $query->whereDate('created_at', today());
        } elseif ($filter === 'yesterday') {
            $query->whereDate('created_at', today()->subDay());
        } elseif ($filter === 'week') {
            $query->whereBetween('created_at', [
                Carbon::now()->startOfWeek(),
                Carbon::now()->endOfWeek(),
            ]);
        } elseif ($filter === 'month') {
            $query->whereMonth('created_at', date('m'))->whereYear('created_at', date('Y'));
        } elseif ($filter === 'year') {
            $query->whereYear('created_at', date('Y'));
        } elseif ($filter === 'custom' && $start && $end) {
            $query->whereBetween('created_at', [$start . ' 00:00:00', $end . ' 23:59:59']);
        }

        // Worksheet Filter
        if ($activeWorksheetId = session('active_worksheet_id')) {
            $query->where('worksheet_id', $activeWorksheetId);
        }

        return $query;
    }

    private function calculateTrend($filter, $source, $start = null, $end = null)
    {
        $now = Carbon::now();

        switch ($filter) {
            case 'today':
                $currentStart = $now->copy()->startOfDay();
                $currentEnd = $now->copy()->endOfDay();
                $prevStart = $now->copy()->subDay()->startOfDay();
                $prevEnd = $now->copy()->subDay()->endOfDay();
                break;
            case 'yesterday':
                $currentStart = $now->copy()->subDay()->startOfDay();
                $currentEnd = $now->copy()->subDay()->endOfDay();
                $prevStart = $now->copy()->subDays(2)->startOfDay();
                $prevEnd = $now->copy()->subDays(2)->endOfDay();
                break;
            case 'week':
                $currentStart = $now->copy()->startOfWeek();
                $currentEnd = $now->copy()->endOfWeek();
                $prevStart = $now->copy()->subWeek()->startOfWeek();
                $prevEnd = $now->copy()->subWeek()->endOfWeek();
                break;
            case 'month':
                $currentStart = $now->copy()->startOfMonth();
                $currentEnd = $now->copy()->endOfMonth();
                $prevStart = $now->copy()->subMonth()->startOfMonth();
                $prevEnd = $now->copy()->subMonth()->endOfMonth();
                break;
            case 'year':
                $currentStart = $now->copy()->startOfYear();
                $currentEnd = $now->copy()->endOfYear();
                $prevStart = $now->copy()->subYear()->startOfYear();
                $prevEnd = $now->copy()->subYear()->endOfYear();
                break;
            case 'custom':
                if ($start && $end) {
                    $currentStart = Carbon::parse($start)->startOfDay();
                    $currentEnd = Carbon::parse($end)->endOfDay();
                    $diffInDays = $currentStart->diffInDays($currentEnd);
                    $prevStart = $currentStart->copy()->subDays($diffInDays + 1)->startOfDay();
                    $prevEnd = $currentStart->copy()->subDay()->endOfDay();
                } else {
                    return 'stable';
                }
                break;
            default:
                return 'stable';
        }

        $currentIncome = Cashflow::where('transaction_category', 'income')
            ->whereBetween('transaction_date', [$currentStart, $currentEnd])
            ->when($source !== 'all', fn($q) => $q->where('source', $source))
            ->sum('amount');

        $prevIncome = Cashflow::where('transaction_category', 'income')
            ->whereBetween('transaction_date', [$prevStart, $prevEnd])
            ->when($source !== 'all', fn($q) => $q->where('source', $source))
            ->sum('amount');

        if ($prevIncome > 0) {
            $change = (($currentIncome - $prevIncome) / $prevIncome) * 100;
            if ($change > 5) return 'up';
            if ($change < -5) return 'down';
            return 'stable';
        }

        return $currentIncome > 0 ? 'up' : 'stable';
    }

    public function export(Request $request)
    {
        $filter = $request->filter ?? 'today';
        $start = $request->start;
        $end = $request->end;
        $source = $request->source ?? 'all';
        $query = $this->baseQuery($filter, $source, $start, $end);

        $cashflows = (clone $query)->orderBy('transaction_date', 'desc')->get();
        $filename = 'cashflow-' . $filter . '-' . now()->format('Ymd') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($cashflows) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($file, ['Tanggal', 'Tipe', 'Kategori', 'Deskripsi', 'Sumber', 'Nominal', 'Referensi', 'Catatan']);

            $labels = Cashflow::sourceLabels();
            foreach ($cashflows as $c) {
                fputcsv($file, [
                    $c->transaction_date->format('Y-m-d'),
                    $c->type === 'income' ? 'Pemasukan' : 'Pengeluaran',
                    $c->category,
                    $c->description,
                    $labels[$c->source] ?? $c->source,
                    $c->amount,
                    $c->reference ?? '-',
                    $c->notes ?? '-',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function store(Request $request)
    {
        $request->validate([
            'type'             => 'required|in:income,expense',
            'category'         => 'required|string|max:100',
            'description'      => 'required|string|max:255',
            'amount'           => 'required|numeric|min:1',
            'transaction_date' => 'required|date',
            'notes'            => 'nullable|string',
            'source'           => 'required|string|in:pos_cash,pos_bank,transfer,manual',
        ]);

        // Manual entries from dashboard are immediately "synced" because they are performed by the owner
        $bankSyncStatus = 'synced';

        $activeShift = \App\Models\Shift::activeShiftForUser(auth()->id());
        $shiftId = $activeShift ? $activeShift->id : null;

        Cashflow::create([
            'user_id'          => auth()->id(),
            'shift_id'         => $shiftId,
            'type'             => $request->type,
            'transaction_category' => $request->type, // Manual store from dashboard usually follows type
            'category'         => $request->category,
            'description'      => $request->description,
            'amount'           => $request->amount,
            'transaction_date' => Carbon::parse($request->transaction_date)->setTimeFrom(now()),
            'source'           => $request->source,
            'bank_sync_status' => $bankSyncStatus,
            'notes'            => $request->notes,
            'worksheet_id'     => session('active_worksheet_id'),
        ]);

        return back()->with('success', 'Cashflow berhasil ditambahkan!');
    }

    public function destroy(Cashflow $cashflow)
    {
        if ($cashflow->reference && !str_starts_with($cashflow->reference, 'TRF-')) {
            return back()->with('error', 'Cashflow dari transaksi POS tidak bisa dihapus manual!');
        }

        if ($cashflow->category === 'Transfer Internal' && $cashflow->reference) {
            // Delete both pairs of the transfer
            Cashflow::where('reference', $cashflow->reference)->delete();
            return back()->with('success', 'Riwayat transfer berhasil dihapus dan saldo telah dikembalikan!');
        }

        $cashflow->delete();
        return back()->with('success', 'Data cashflow dihapus!');
    }

    public function syncBank(Request $request)
    {
        $count = Cashflow::syncAllPendingBank();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'count'   => $count,
                'message' => "{$count} transaksi bank berhasil disinkronkan ke Saldo Bank.",
            ]);
        }

        return back()->with('success', "{$count} transaksi bank berhasil disinkronkan!");
    }

    public function syncLaci(Request $request)
    {
        $count = Cashflow::syncAllPendingLaci();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'count'   => $count,
                'message' => "{$count} transaksi tunai berhasil disinkronkan ke Saldo Laci.",
            ]);
        }

        return back()->with('success', "{$count} transaksi tunai berhasil disinkronkan!");
    }

    public function update(Request $request, Cashflow $cashflow)
    {
        if ($cashflow->reference && !str_starts_with($cashflow->reference, 'TRF-')) {
            return back()->with('error', 'Cashflow dari transaksi POS tidak bisa diubah manual!');
        }

        $request->validate([
            'type'             => 'required|in:income,expense',
            'category'         => 'required|string|max:100',
            'description'      => 'required|string|max:255',
            'amount'           => 'required|numeric|min:1',
            'transaction_date' => 'required|date',
            'notes'            => 'nullable|string',
            'source'           => 'required|string|in:pos_cash,pos_bank,transfer,manual',
        ]);

        if ($cashflow->category === 'Transfer Internal' && $cashflow->reference) {
            // Update both pairs
            $pairs = Cashflow::where('reference', $cashflow->reference)->get();
            foreach ($pairs as $p) {
                $p->update([
                    'amount' => $request->amount,
                    'transaction_date' => Carbon::parse($request->transaction_date)->setTimeFrom($p->transaction_date),
                    'notes' => $request->notes ?? $request->description,
                ]);
            }
            return back()->with('success', 'Data transfer diperbarui!');
        }

        $data = $request->only(['type', 'category', 'description', 'amount', 'source', 'notes']);
        $data['transaction_date'] = Carbon::parse($request->transaction_date)->setTimeFrom($cashflow->transaction_date);
        
        // If it was already an adjustment, keep it as adjustment
        // If it was income/expense, update it to the new type
        if ($cashflow->transaction_category !== 'adjustment') {
            $data['transaction_category'] = $request->type;
        } else {
            $data['transaction_category'] = 'adjustment';
        }
        
        $cashflow->update($data);

        return back()->with('success', 'Cashflow berhasil diperbarui!');
    }

    public function updateTarget(Request $request)
    {
        $request->validate([
            'target_payback_months' => 'required|integer|min:1|max:120',
        ]);

        $activeWorksheetId = session('active_worksheet_id');
        if ($activeWorksheetId) {
            \App\Models\Worksheet::where('id', $activeWorksheetId)->update([
                'target_payback_months' => $request->target_payback_months
            ]);
            return back()->with('success', 'Target balik modal diperbarui!');
        }

        return back()->with('error', 'Pilih satu worksheet terlebih dahulu!');
    }

    /**
     * Update Modal Investasi (Capital) - AJAX endpoint dari cashflow page
     */
    public function updateCapital(Request $request)
    {
        $request->validate([
            'total_amount' => 'required|numeric|min:0',
            'notes'        => 'nullable|string|max:255',
            'date'         => 'nullable|date',
        ]);

        $capital = \App\Models\Capital::latest()->first();

        if (!$capital) {
            // Create a new capital record if none exists
            $capital = \App\Models\Capital::create([
                'date'         => $request->date ?? now()->toDateString(),
                'is_detailed'  => false,
                'total_amount' => $request->total_amount,
                'worksheet_id' => session('active_worksheet_id'),
            ]);
        } else {
            $capital->update([
                'total_amount' => $request->total_amount,
                'date'         => $request->date ?? $capital->date,
            ]);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success'      => true,
                'message'      => 'Modal investasi berhasil diperbarui!',
                'total_amount' => $capital->total_amount,
                'total_fmt'    => number_format($capital->total_amount, 0, ',', '.'),
                'new_total'    => \App\Models\Capital::sum('total_amount'),
                'new_total_fmt'=> number_format(\App\Models\Capital::sum('total_amount'), 0, ',', '.'),
            ]);
        }

        return back()->with('success', 'Modal investasi berhasil diperbarui!');
    }

    public function transfer(Request $request)
    {
        $request->validate([
            'direction' => 'required|in:laci_to_bank,bank_to_laci',
            'amount' => 'required|numeric|min:1',
            'notes' => 'nullable|string|max:255',
        ]);

        $ref = 'TRF-' . now()->format('YmdHis');
        $activeShift = \App\Models\Shift::activeShiftForUser(auth()->id());
        $shiftId = $activeShift ? $activeShift->id : null;
        $worksheetId = session('active_worksheet_id');

        if ($request->direction === 'laci_to_bank') {
            // Expense from laci
            Cashflow::create([
                'user_id' => auth()->id(),
                'shift_id' => $shiftId,
                'worksheet_id' => $worksheetId,
                'type' => 'expense',
                'transaction_category' => 'adjustment',
                'category' => 'Transfer Internal',
                'description' => 'Transfer Laci → Bank',
                'amount' => $request->amount,
                'source' => 'pos_cash',
                'bank_sync_status' => 'synced',
                'reference' => $ref,
                'transaction_date' => now(),
                'notes' => $request->notes,
            ]);
            // Income to bank
            Cashflow::create([
                'user_id' => auth()->id(),
                'shift_id' => $shiftId,
                'worksheet_id' => $worksheetId,
                'type' => 'income',
                'transaction_category' => 'adjustment',
                'category' => 'Transfer Internal',
                'description' => 'Transfer Laci → Bank',
                'amount' => $request->amount,
                'source' => 'transfer',
                'bank_sync_status' => 'synced',
                'reference' => $ref,
                'transaction_date' => now(),
                'notes' => $request->notes,
            ]);
        } else {
            // Expense from bank
            Cashflow::create([
                'user_id' => auth()->id(),
                'shift_id' => $shiftId,
                'worksheet_id' => $worksheetId,
                'type' => 'expense',
                'transaction_category' => 'adjustment',
                'category' => 'Transfer Internal',
                'description' => 'Transfer Bank → Laci',
                'amount' => $request->amount,
                'source' => 'transfer',
                'bank_sync_status' => 'synced',
                'reference' => $ref,
                'transaction_date' => now(),
                'notes' => $request->notes,
            ]);
            // Income to laci
            Cashflow::create([
                'user_id' => auth()->id(),
                'shift_id' => $shiftId,
                'worksheet_id' => $worksheetId,
                'type' => 'income',
                'transaction_category' => 'adjustment',
                'category' => 'Transfer Internal',
                'description' => 'Transfer Bank → Laci',
                'amount' => $request->amount,
                'source' => 'pos_cash',
                'bank_sync_status' => 'synced',
                'reference' => $ref,
                'transaction_date' => now(),
                'notes' => $request->notes,
            ]);
        }

        return back()->with('success', 'Transfer berhasil dicatat!');
    }

    public function storeQuick(Request $request)
    {
        $request->validate([
            'type' => 'required|in:income,expense',
            'source' => 'required|in:pos_cash,pos_bank',
            'amount' => 'required|numeric|min:1',
            'transaction_date' => 'required|date',
            'notes' => 'required|string|max:255',
        ]);

        $type = $request->type;
        $source = $request->source;
        $amount = $request->amount;

        // Validation for "Saldo tidak mencukupi"
        if ($type === 'expense') {
            $currentBalance = 0;
            if ($source === 'pos_cash') {
                $income = Cashflow::where('source', 'pos_cash')->where('bank_sync_status', 'synced')->where('type', 'income')->sum('amount');
                $expense = Cashflow::where('source', 'pos_cash')->where('bank_sync_status', 'synced')->where('type', 'expense')->sum('amount');
                $currentBalance = $income - $expense;
            } else {
                $income = Cashflow::whereIn('source', ['pos_bank', 'transfer'])->where('bank_sync_status', 'synced')->where('type', 'income')->sum('amount');
                $expense = Cashflow::whereIn('source', ['pos_bank', 'transfer'])->where('bank_sync_status', 'synced')->where('type', 'expense')->sum('amount');
                $currentBalance = $income - $expense;
            }

            if ($amount > $currentBalance) {
                return back()->with('error', 'Saldo tidak mencukupi! Saldo tersedia: Rp ' . number_format($currentBalance, 0, ',', '.'));
            }
        }

        $activeShift = \App\Models\Shift::activeShiftForUser(auth()->id());
        $shiftId = $activeShift ? $activeShift->id : null;

        DB::transaction(function () use ($request, $type, $source, $amount, $shiftId) {
            // 1. Record to cash_transactions (New dedicated history table)
            CashTransaction::create([
                'type' => $type === 'income' ? 'masuk' : 'keluar',
                'source' => $source === 'pos_cash' ? 'cash' : 'bank',
                'amount' => $amount,
                'note' => $request->notes,
                'created_by' => auth()->id(),
                'transaction_date' => $request->transaction_date,
            ]);

            // 2. Record to cashflows (Main history for reports & stats)
            Cashflow::create([
                'user_id' => auth()->id(),
                'shift_id' => $shiftId,
                'type' => $type,
                'transaction_category' => 'adjustment',
                'category' => 'Input Saldo Manual',
                'description' => $request->notes,
                'amount' => $amount,
                'transaction_date' => Carbon::parse($request->transaction_date)->setTimeFrom(now()),
                'source' => $source,
                'bank_sync_status' => 'synced',
                'notes' => $request->notes,
                'worksheet_id' => session('active_worksheet_id'),
            ]);
        });

        return back()->with('success', 'Transaksi saldo berhasil dicatat!');
    }
}
