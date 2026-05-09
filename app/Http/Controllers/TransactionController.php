<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Payment;
use App\Models\Cashflow;
use App\Models\Shift;
use App\Models\StockMutation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TransactionController extends Controller
{
    protected $financialService;

    public function __construct(\App\Services\FinancialReportService $financialService)
    {
        $this->financialService = $financialService;
    }

    public function index(Request $request)
    {
        $users = \App\Models\User::all();
        $worksheetId = session('active_worksheet_id');

        // --- Strict Date Filter (Today Only) ---
        $dateFrom = today()->toDateString();
        $dateTo = today()->toDateString();

        // --- Filter by status pill ---
        $sParam = $request->status;
        $statusFilter = is_array($sParam) ? null : $sParam; // 'piutang', 'lunas', or null

        // --- Build transaction items ---
        $txQuery = Transaction::with(['user', 'items'])
            ->whereDate('created_at', '>=', $dateFrom)
            ->whereDate('created_at', '<=', $dateTo)
            ->when($request->user_id, fn($q) => $q->where('user_id', $request->user_id))
            ->when($request->payment_method, fn($q) => $q->where('payment_method', $request->payment_method))
            ->when($statusFilter === 'piutang', fn($q) => $q->where('status', 'pending'))
            ->when($statusFilter === 'lunas', fn($q) => $q->where('status', 'completed')->where('payment_method', 'piutang'))
            ->when($request->search, fn($q) => $q->where(function($sq) use ($request) {
                $sq->where('invoice_number', 'like', "%{$request->search}%")
                   ->orWhere('customer_name', 'like', "%{$request->search}%");
            }))
            ->when($worksheetId && $worksheetId !== 'all', fn($q) => $q->where('worksheet_id', $worksheetId));

        // --- Build expense items ---
        $expenseQuery = Cashflow::with('user')
            ->where('transaction_category', 'expense')
            ->whereNull('reference')
            ->whereDate('transaction_date', '>=', $dateFrom)
            ->whereDate('transaction_date', '<=', $dateTo)
            ->when($request->user_id, fn($q) => $q->where('user_id', $request->user_id))
            ->when($request->search, fn($q) => $q->where('description', 'like', "%{$request->search}%"))
            ->when($worksheetId && $worksheetId !== 'all', fn($q) => $q->where('worksheet_id', $worksheetId));

        // --- Compile based on type filter ---
        $items = collect();
        $type = $request->type;

        if (!$type || $type === 'penjualan') {
            $txItems = $txQuery->get()->map(function($t) {
                return (object)[
                    'sort_date' => $t->created_at,
                    'type' => 'penjualan',
                    'model' => $t,
                ];
            });
            $items = $items->merge($txItems);
        }

        if ((!$type || $type === 'expense') && !$statusFilter) {
            $expItems = $expenseQuery->get()->map(function($e) {
                return (object)[
                    'sort_date' => $e->transaction_date,
                    'type' => 'expense',
                    'model' => $e,
                ];
            });
            $items = $items->merge($expItems);
        }

        // Sort descending by date
        $items = $items->sortByDesc('sort_date')->values();

        // Manual pagination
        $page = $request->input('page', 1);
        $perPage = 20;
        $total = $items->count();
        $paginatedItems = $items->slice(($page - 1) * $perPage, $perPage)->values();
        $transactions = new \Illuminate\Pagination\LengthAwarePaginator(
            $paginatedItems, $total, $perPage, $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        // --- Pill filter counts ---
        $baseTx = Transaction::when($worksheetId && $worksheetId !== 'all', fn($q) => $q->where('worksheet_id', $worksheetId));
        $baseExp = Cashflow::where('transaction_category', 'expense')->whereNull('reference')
            ->when($worksheetId && $worksheetId !== 'all', fn($q) => $q->where('worksheet_id', $worksheetId));

        $countPenjualan = (clone $baseTx)->count();
        $countExpense = (clone $baseExp)->count();
        $countAll = $countPenjualan + $countExpense;

        $countPiutang = (clone $baseTx)->where('status', 'pending')->count();
        $countLunas = (clone $baseTx)->where('status', 'completed')->where('payment_method', 'piutang')->count();
        $countCash = (clone $baseTx)->where('payment_method', 'cash')->count();
        $countQris = (clone $baseTx)->where('payment_method', 'qris')->count();
        $countTransfer = (clone $baseTx)->where('payment_method', 'transfer')->count();
        $countDebit = (clone $baseTx)->where('payment_method', 'debit')->count();

        // --- Stats ---
        $activeShift = Shift::activeShift();

        $saldoLaci = 0;
        $saldoLaciAwal = 0;
        if ($activeShift) {
            $cashSalesInShift = Transaction::where('shift_id', $activeShift->id)->completed()->where('payment_method', 'cash')->sum('total');
            $cashExpInShift = Cashflow::where('shift_id', $activeShift->id)->where('transaction_category', 'expense')->where('source', 'pos_cash')->sum('amount');
            $saldoLaciAwal = (float) $activeShift->opening_cash;
            $saldoLaci = $saldoLaciAwal + $cashSalesInShift - $cashExpInShift;
        }

        $baseBank = Cashflow::whereIn('source', ['pos_bank', 'transfer'])
            ->when($worksheetId && $worksheetId !== 'all', fn($q) => $q->where('worksheet_id', $worksheetId));
        $bankIncome = (clone $baseBank)->where('type', 'income')->sum('amount');
        $bankExpense = (clone $baseBank)->where('type', 'expense')->sum('amount');
        $saldoBank = $bankIncome - $bankExpense;

        // --- Stats from Unified Service ---
        $todaySummary = $this->financialService->getSummary(\Carbon\Carbon::parse($dateFrom), \Carbon\Carbon::parse($dateTo), $worksheetId);
        $todayTotalSales = $todaySummary->total_income;
        $todayExpenses = $todaySummary->total_expense;
        $todayNet = $todaySummary->net_profit;
        $totalPiutang = (clone $baseTx)->piutang()->get()->sum(fn($t) => $t->total - $t->paid_so_far);

        $todayQris = (clone $baseTx)->completed()->whereDate('created_at', today())->where('payment_method', 'qris')->sum('total');
        $todayCash = (clone $baseTx)->completed()->whereDate('created_at', today())->where('payment_method', 'cash')->sum('total');
        $todayTransfer = (clone $baseTx)->completed()->whereDate('created_at', today())->where('payment_method', 'transfer')->sum('total');

        return view('transactions.index', compact(
            'transactions', 'users', 'todayTotalSales', 'todayExpenses', 'todayNet',
            'saldoLaci', 'saldoLaciAwal', 'saldoBank', 'totalPiutang', 'activeShift',
            'countAll', 'countPenjualan', 'countExpense', 'countPiutang', 'countLunas', 'countCash', 'countQris', 'countTransfer', 'countDebit',
            'todayQris', 'todayCash', 'todayTransfer'
        ));
    }

    public function show(Transaction $transaction)
    {
        $transaction->load(['items.product', 'user', 'shift', 'payments.user']);
        
        if (request()->wantsJson()) {
            $settings = \App\Models\Setting::getMultiple([
                'store_name', 'store_address', 'store_phone', 'store_footer'
            ]);

            return response()->json([
                'invoice_number' => $transaction->invoice_number,
                'created_at' => $transaction->created_at->format('d/m/y H:i'),
                'store_name' => $settings['store_name'] ?? 'MONOFRAME STUDIO',
                'store_address' => $settings['store_address'] ?? '',
                'store_phone' => $settings['store_phone'] ?? '',
                'store_footer' => $settings['store_footer'] ?? 'Terima kasih telah berbelanja!',
                'subtotal' => $transaction->subtotal,
                'discount' => $transaction->discount,
                'tax' => $transaction->tax,
                'total' => $transaction->total,
                'paid_amount' => $transaction->paid_amount,
                'change_amount' => $transaction->change_amount,
                'payment_method' => strtoupper($transaction->payment_method),
                'status' => $transaction->status === 'completed' ? 'LUNAS' : 'PENDING',
                'customer_name' => $transaction->customer_name,
                'customer_phone' => $transaction->customer_phone,
                'notes' => $transaction->notes,
                'items' => $transaction->items->map(fn($i) => [
                    'product_name' => $i->product_name,
                    'quantity' => $i->quantity,
                    'price' => $i->price,
                    'subtotal' => $i->subtotal
                ])
            ]);
        }
        
        return view('transactions.show', compact('transaction'));
    }

    /**
     * Pay piutang (partial or full)
     */
    public function payPiutang(Request $request, Transaction $transaction)
    {
        if ($transaction->status !== 'pending') {
            return back()->with('error', 'Transaksi ini bukan piutang!');
        }

        $remaining = $transaction->total - $transaction->paid_so_far;

        $request->validate([
            'amount' => 'required|numeric|min:1|max:' . $remaining,
            'payment_method' => 'required|in:cash,transfer,qris,debit',
            'notes' => 'nullable|string|max:255',
        ]);

        DB::beginTransaction();
        try {
            // Record the payment
            Payment::create([
                'transaction_id' => $transaction->id,
                'user_id' => auth()->id(),
                'amount' => $request->amount,
                'payment_method' => $request->payment_method,
                'notes' => $request->notes,
            ]);

            $newPaidSoFar = $transaction->paid_so_far + $request->amount;
            $transaction->update(['paid_so_far' => $newPaidSoFar]);

            // If fully paid, mark as completed
            if ($newPaidSoFar >= $transaction->total) {
                $transaction->update(['status' => 'completed']);
            }

            // Record income in cashflow
            $sourceMap = [
                'cash' => 'pos_cash',
                'qris' => 'pos_bank',
                'debit' => 'pos_bank',
                'transfer' => 'transfer',
            ];
            $source = $sourceMap[$request->payment_method] ?? 'pos';

            Cashflow::create([
                'user_id' => auth()->id(),
                'shift_id' => Shift::activeShift()?->id,
                'type' => 'income',
                'category' => 'Pelunasan Piutang',
                'description' => 'Pelunasan ' . $transaction->invoice_number . ($newPaidSoFar >= $transaction->total ? ' (LUNAS)' : ' (Sebagian)'),
                'amount' => $request->amount,
                'reference' => $transaction->invoice_number,
                'reference_id' => $transaction->id,
                'source' => $source,
                'transaction_date' => today(),
            ]);

            DB::commit();
            return back()->with('success', 'Pembayaran piutang berhasil dicatat!' . ($newPaidSoFar >= $transaction->total ? ' Piutang LUNAS.' : ''));
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal: ' . $e->getMessage());
        }
    }

    public function cancel(Transaction $transaction)
    {
        if ($transaction->status !== 'completed') {
            return back()->with('error', 'Transaksi tidak bisa dibatalkan!');
        }

        // Kembalikan stok
        foreach ($transaction->items as $item) {
            $product = $item->product;
            if ($product && !$product->isStockless()) {
                $stockBefore = $product->stock;
                $product->increment('stock', $item->quantity);
                StockMutation::create([
                    'product_id' => $product->id,
                    'user_id' => auth()->id(),
                    'type' => 'in',
                    'quantity' => $item->quantity,
                    'stock_before' => $stockBefore,
                    'stock_after' => $product->fresh()->stock,
                    'reference' => $transaction->invoice_number,
                    'notes' => 'Pengembalian stok - transaksi dibatalkan',
                ]);
            }
        }

        $transaction->update(['status' => 'cancelled']);

        $sourceMap = [
            'cash' => 'pos_cash',
            'qris' => 'pos_bank',
            'debit' => 'pos_bank',
            'transfer' => 'transfer',
        ];
        $source = $sourceMap[$transaction->payment_method] ?? 'pos';

        Cashflow::create([
            'user_id' => auth()->id(),
            'shift_id' => $transaction->shift_id,
            'worksheet_id' => $transaction->worksheet_id,
            'type' => 'expense',
            'transaction_category' => 'expense',
            'category' => 'Refund / Retur',
            'description' => 'Refund POS - Batal ' . $transaction->invoice_number,
            'amount' => $transaction->total,
            'reference' => $transaction->invoice_number,
            'reference_id' => $transaction->id,
            'source' => $source,
            'transaction_date' => today(),
        ]);

        return back()->with('success', 'Transaksi berhasil dibatalkan dan stok dikembalikan!');
    }

    public function destroy(Transaction $transaction)
    {
        if ($transaction->status !== 'cancelled') {
            return back()->with('error', 'Hanya transaksi batal yang dapat dihapus permanen.');
        }

        DB::beginTransaction();
        try {
            Cashflow::where('reference', $transaction->invoice_number)->delete();
            StockMutation::where('reference', $transaction->invoice_number)->delete();
            $transaction->delete();

            DB::commit();
            return back()->with('success', 'Transaksi ' . $transaction->invoice_number . ' berhasil dihapus permanen!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menghapus transaksi: ' . $e->getMessage());
        }
    }
}
