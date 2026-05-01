<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Carbon\Carbon;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $query = Transaction::with(['user', 'shift', 'items'])->latest();

        if ($request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        if ($request->payment_method) {
            $query->where('payment_method', $request->payment_method);
        }
        if ($request->status) {
            $query->where('status', $request->status);
        }
        if ($request->search) {
            $query->where('invoice_number', 'like', '%' . $request->search . '%')
                  ->orWhere('customer_name', 'like', '%' . $request->search . '%');
        }

        $transactions = $query->paginate(20)->withQueryString();
        $totalFiltered = $query->sum('total');

        // Summary stats
        $todayTotal = Transaction::completed()->whereDate('created_at', today())->sum('total');
        $todayCount = Transaction::completed()->whereDate('created_at', today())->count();

        return view('transactions.index', compact('transactions', 'todayTotal', 'todayCount', 'totalFiltered'));
    }

    public function show(Transaction $transaction)
    {
        $transaction->load(['items.product', 'user', 'shift']);
        return view('transactions.show', compact('transaction'));
    }

    public function cancel(Transaction $transaction)
    {
        if ($transaction->status !== 'completed') {
            return back()->with('error', 'Transaksi tidak bisa dibatalkan!');
        }

        // Kembalikan stok
        foreach ($transaction->items as $item) {
            $product = $item->product;
            if ($product) {
                $stockBefore = $product->stock;
                $product->increment('stock', $item->quantity);
                \App\Models\StockMutation::create([
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

        // Catat pengembalian dana (refund) ke cashflow sebagai pengeluaran (expense)
        \App\Models\Cashflow::create([
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
}
