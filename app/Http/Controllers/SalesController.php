<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SalesController extends Controller
{
    public function index(Request $request)
    {
        $dateFrom = $request->date_from ? Carbon::parse($request->date_from)->startOfDay() : Carbon::today()->startOfDay();
        $dateTo = $request->date_to ? Carbon::parse($request->date_to)->endOfDay() : Carbon::today()->endOfDay();

        $query = Transaction::with(['user', 'items'])->completed()
            ->whereBetween('created_at', [$dateFrom, $dateTo]);

        if ($request->payment_method) {
            $query->where('payment_method', $request->payment_method);
        }

        if ($request->user_id) {
            $query->where('user_id', $request->user_id);
        }

        // Clone the query for aggregations before pagination
        $aggQuery = clone $query;

        // Metrik 1: Total Omzet Kotor (sebelum diskon)
        $totalOmzetKotor = $aggQuery->sum('subtotal');

        // Metrik 2: Total Diskon
        // Perlu diingat bahwa pada POS kita, jika diskon %, field discount sudah berisi nominal setelah dikalkulasi
        // Jadi kita bisa sum langsung
        $totalDiskon = $aggQuery->sum('discount');

        // Metrik 3: Total Omzet Bersih
        $totalOmzetBersih = $totalOmzetKotor - $totalDiskon;

        // Metrik 4: Uang Laci Kasir (Hanya tunai / cash)
        $uangLaciQuery = clone $query;
        $uangLaci = $uangLaciQuery->where('payment_method', 'cash')->sum('total');

        // Metrik 5: Margin Profit
        // Profit = Total Bersih - Total HPP (Cost Price * Qty)
        // Kita hitung total HPP dari seluruh item transaksi yang masuk query
        $totalHpp = DB::table('transaction_items')
            ->join('transactions', 'transaction_items.transaction_id', '=', 'transactions.id')
            ->where('transactions.status', 'completed')
            ->whereBetween('transactions.created_at', [$dateFrom, $dateTo])
            ->when($request->payment_method, function ($q) use ($request) {
                return $q->where('transactions.payment_method', $request->payment_method);
            })
            ->when($request->user_id, function ($q) use ($request) {
                return $q->where('transactions.user_id', $request->user_id);
            })
            ->sum(DB::raw('transaction_items.cost_price * transaction_items.quantity'));
        
        $totalProfit = $totalOmzetBersih - $totalHpp;

        // Chart Data
        $salesPerDay = Transaction::completed()
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->when($request->payment_method, fn($q) => $q->where('payment_method', $request->payment_method))
            ->when($request->user_id, fn($q) => $q->where('user_id', $request->user_id))
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(total) as total'))
            ->groupBy('date')->orderBy('date')->get();

        $byPayment = Transaction::completed()
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->when($request->user_id, fn($q) => $q->where('user_id', $request->user_id))
            ->select('payment_method', DB::raw('SUM(total) as total'))
            ->groupBy('payment_method')->get();

        $topProducts = DB::table('transaction_items')
            ->join('transactions', 'transaction_items.transaction_id', '=', 'transactions.id')
            ->where('transactions.status', 'completed')
            ->whereBetween('transactions.created_at', [$dateFrom, $dateTo])
            ->when($request->payment_method, fn($q) => $q->where('transactions.payment_method', $request->payment_method))
            ->when($request->user_id, fn($q) => $q->where('transactions.user_id', $request->user_id))
            ->select('transaction_items.product_name', DB::raw('SUM(transaction_items.quantity) as total_qty'))
            ->groupBy('transaction_items.product_name')
            ->orderByDesc('total_qty')
            ->limit(3)->get();

        $transactions = $query->latest()->paginate(15)->withQueryString();
        $kasirUsers = User::whereIn('role', ['owner', 'operator'])->get();

        return view('sales.index', compact(
            'transactions', 'dateFrom', 'dateTo', 'kasirUsers',
            'totalOmzetKotor', 'totalDiskon', 'totalOmzetBersih',
            'uangLaci', 'totalHpp', 'totalProfit',
            'salesPerDay', 'byPayment', 'topProducts'
        ));
    }
}
