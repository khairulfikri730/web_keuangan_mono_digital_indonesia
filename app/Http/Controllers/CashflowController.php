<?php

namespace App\Http\Controllers;

use App\Models\Cashflow;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CashflowController extends Controller
{
    private function baseQuery($filter, $source, $start = null, $end = null)
    {
        $query = Cashflow::with('user');

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

        return $query;
    }

    public function index(Request $request)
    {
        $filter = $request->filter ?? 'today';
        $start = $request->start;
        $end = $request->end;
        $source = $request->source ?? 'all';
        $query = $this->baseQuery($filter, $source, $start, $end);

        $cashflows = (clone $query)->latest('transaction_date')->paginate(20)->withQueryString();
        $totalIncome = (clone $query)->where('type', 'income')->sum('amount');
        $totalExpense = (clone $query)->where('type', 'expense')->sum('amount');
        $netProfit = $totalIncome - $totalExpense;

        $incomeByCategory = (clone $query)->where('type', 'income')
            ->selectRaw('category, SUM(amount) as total')->groupBy('category')->orderByDesc('total')->get();

        $expenseByCategory = (clone $query)->where('type', 'expense')
            ->selectRaw('category, SUM(amount) as total')->groupBy('category')->orderByDesc('total')->get();

        $chartQuery = (clone $query)
            ->selectRaw("DATE(transaction_date) as date")
            ->selectRaw("SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) as income")
            ->selectRaw("SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) as expense")
            ->groupBy(DB::raw("DATE(transaction_date)"))
            ->orderBy('date', 'asc')
            ->get();

        $chartDates = $chartQuery->map(fn($r) => Carbon::parse($r->date)->format('d M'))->toArray();
        $chartIncome = $chartQuery->pluck('income')->toArray();
        $chartExpense = $chartQuery->pluck('expense')->toArray();

        $daysCount = max($chartQuery->count(), 1);
        $avgIncome = $totalIncome / $daysCount;

        $biggestExpense = (clone $query)->where('type', 'expense')
            ->orderByDesc('amount')->first();

        $trend = $this->calculateTrend($filter, $source, $start, $end);

        return view('cashflow.index', compact(
            'cashflows', 'totalIncome', 'totalExpense', 'netProfit',
            'filter', 'start', 'end', 'source', 'incomeByCategory', 'expenseByCategory',
            'chartDates', 'chartIncome', 'chartExpense',
            'biggestExpense', 'avgIncome', 'trend'
        ));
    }

    public function getData(Request $request)
    {
        $filter = $request->filter ?? 'today';
        $start = $request->start;
        $end = $request->end;
        $source = $request->source ?? 'all';
        $page = $request->page ?? 1;
        $query = $this->baseQuery($filter, $source, $start, $end);

        $totalIncome = (clone $query)->where('type', 'income')->sum('amount');
        $totalExpense = (clone $query)->where('type', 'expense')->sum('amount');
        $netProfit = $totalIncome - $totalExpense;

        $chartQuery = (clone $query)
            ->selectRaw("DATE(transaction_date) as date")
            ->selectRaw("SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) as income")
            ->selectRaw("SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) as expense")
            ->groupBy(DB::raw("DATE(transaction_date)"))
            ->orderBy('date', 'asc')
            ->get();

        $chartDates = $chartQuery->map(fn($r) => Carbon::parse($r->date)->format('d M'))->toArray();
        $chartIncome = $chartQuery->pluck('income')->toArray();
        $chartExpense = $chartQuery->pluck('expense')->toArray();

        $daysCount = max($chartQuery->count(), 1);
        $avgIncome = $totalIncome / $daysCount;

        $expenseByCategory = (clone $query)->where('type', 'expense')
            ->selectRaw('category, SUM(amount) as total')->groupBy('category')->orderByDesc('total')->get();

        $incomeByCategory = (clone $query)->where('type', 'income')
            ->selectRaw('category, SUM(amount) as total')->groupBy('category')->orderByDesc('total')->get();

        $biggestExpense = (clone $query)->where('type', 'expense')
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

        $currentIncome = Cashflow::where('type', 'income')
            ->whereBetween('transaction_date', [$currentStart, $currentEnd])
            ->when($source !== 'all', fn($q) => $q->where('source', $source))
            ->sum('amount');

        $prevIncome = Cashflow::where('type', 'income')
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
            'type' => 'required|in:income,expense',
            'category' => 'required|string|max:100',
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:1',
            'transaction_date' => 'required|date',
            'notes' => 'nullable|string',
            'source' => 'required|string|in:pos_cash,pos_bank,transfer,manual',
        ]);

        Cashflow::create([
            'user_id' => auth()->id(),
            'type' => $request->type,
            'category' => $request->category,
            'description' => $request->description,
            'amount' => $request->amount,
            'transaction_date' => $request->transaction_date,
            'source' => $request->source,
            'notes' => $request->notes,
        ]);

        return back()->with('success', 'Cashflow berhasil ditambahkan!');
    }

    public function destroy(Cashflow $cashflow)
    {
        if ($cashflow->reference) {
            return back()->with('error', 'Cashflow dari transaksi POS tidak bisa dihapus manual!');
        }

        $cashflow->delete();

        return back()->with('success', 'Data cashflow dihapus!');
    }
}
