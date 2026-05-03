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
    public function index(Request $request)
    {
        $users = \App\Models\User::all();

        // --- Filter by status pill ---
        $statusFilter = $request->status; // 'piutang', 'lunas', or null

        // --- Build transaction items ---
        $txQuery = Transaction::with(['user', 'items'])
            ->when($request->date_from, fn($q) => $q->whereDate('created_at', '>=', $request->date_from))
            ->when($request->date_to, fn($q) => $q->whereDate('created_at', '<=', $request->date_to))
            ->when($request->user_id, fn($q) => $q->where('user_id', $request->user_id))
            ->when($request->payment_method, fn($q) => $q->where('payment_method', $request->payment_method))
            ->when($statusFilter === 'piutang', fn($q) => $q->where('status', 'pending'))
            ->when($statusFilter === 'lunas', fn($q) => $q->where('status', 'completed')->where('payment_method', 'piutang'))
            ->when($request->search, fn($q) => $q->where(function($sq) use ($request) {
                $sq->where('invoice_number', 'like', "%{$request->search}%")
                   ->orWhere('customer_name', 'like', "%{$request->search}%");
            }));

        // --- Build expense items ---
        $expenseQuery = Cashflow::with('user')
            ->where('type', 'expense')
            ->whereNull('reference')
            ->when($request->date_from, fn($q) => $q->whereDate('transaction_date', '>=', $request->date_from))
            ->when($request->date_to, fn($q) => $q->whereDate('transaction_date', '<=', $request->date_to))
            ->when($request->user_id, fn($q) => $q->where('user_id', $request->user_id))
            ->when($request->search, fn($q) => $q->where('description', 'like', "%{$request->search}%"));

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
                    'sort_date' => $e->created_at,
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
        $countAll = Transaction::count();
        $countPiutang = Transaction::where('status', 'pending')->count();
        $countLunas = Transaction::where('status', 'completed')->where('payment_method', 'piutang')->count();
        $countCash = Transaction::where('payment_method', 'cash')->count();
        $countQris = Transaction::where('payment_method', 'qris')->count();
        $countTransfer = Transaction::where('payment_method', 'transfer')->count();
        $countDebit = Transaction::where('payment_method', 'debit')->count();

        // --- Stats ---
        $activeShift = Shift::activeShift();

        $saldoLaci = 0;
        if ($activeShift) {
            $cashSalesInShift = Transaction::where('shift_id', $activeShift->id)->completed()->where('payment_method', 'cash')->sum('total');
            $cashExpInShift = Cashflow::where('shift_id', $activeShift->id)->where('type', 'expense')->where('source', 'pos_cash')->sum('amount');
            $saldoLaci = $activeShift->opening_cash + $cashSalesInShift - $cashExpInShift;
        }

        $bankIncome = Cashflow::whereIn('source', ['pos_bank', 'transfer'])->where('type', 'income')->sum('amount');
        $bankExpense = Cashflow::whereIn('source', ['pos_bank', 'transfer'])->where('type', 'expense')->sum('amount');
        $saldoBank = $bankIncome - $bankExpense;

        $todayTotalSales = Transaction::completed()->whereDate('created_at', today())->sum('total');
        $todayExpenses = Cashflow::where('type', 'expense')->whereDate('transaction_date', today())->sum('amount');
        $todayNet = $todayTotalSales - $todayExpenses;
        $totalPiutang = Transaction::piutang()->get()->sum(fn($t) => $t->total - $t->paid_so_far);

        return view('transactions.index', compact(
            'transactions', 'users', 'todayTotalSales', 'todayExpenses', 'todayNet',
            'saldoLaci', 'saldoBank', 'totalPiutang', 'activeShift',
            'countAll', 'countPiutang', 'countLunas', 'countCash', 'countQris', 'countTransfer', 'countDebit'
        ));
    }

    public function show(Transaction $transaction)
    {
        $transaction->load(['items.product', 'user', 'shift', 'payments.user']);
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
            'type' => 'expense',
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
