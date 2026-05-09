<?php

namespace App\Http\Controllers;

use App\Models\Shift;
use App\Models\Transaction;
use App\Models\Cashflow;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ShiftController extends Controller
{
    public function index(Request $request)
    {
        // Handle Quick Period Filter
        if ($request->period && $request->period !== 'custom') {
            switch ($request->period) {
                case 'today':
                    $request->merge(['date_from' => today()->toDateString(), 'date_to' => today()->toDateString()]);
                    break;
                case 'yesterday':
                    $yesterday = today()->subDay()->toDateString();
                    $request->merge(['date_from' => $yesterday, 'date_to' => $yesterday]);
                    break;
                case 'week':
                    $request->merge(['date_from' => now()->startOfWeek()->toDateString(), 'date_to' => now()->endOfWeek()->toDateString()]);
                    break;
                case 'month':
                    $request->merge(['date_from' => now()->startOfMonth()->toDateString(), 'date_to' => now()->endOfMonth()->toDateString()]);
                    break;
                case 'year':
                    $request->merge(['date_from' => now()->startOfYear()->toDateString(), 'date_to' => now()->endOfYear()->toDateString()]);
                    break;
            }
        }

        $query = Shift::with(['opener', 'closer'])
            ->when($request->date_from && !is_array($request->date_from), fn($q) => $q->whereDate('opened_at', '>=', $request->date_from))
            ->when($request->date_to && !is_array($request->date_to), fn($q) => $q->whereDate('opened_at', '<=', $request->date_to))
            ->when($request->status && !is_array($request->status), fn($q) => $q->where('status', $request->status))
            ->when($request->user_id && !is_array($request->user_id), fn($q) => $q->where('opened_by', $request->user_id));

        $shifts = $query->latest()->paginate(15)->withQueryString();
        
        $activeShiftsCount = Shift::where('status', 'open')->count();
        $totalClosingCash = Shift::where('status', 'closed')
            ->when($request->date_from, fn($q) => $q->whereDate('opened_at', '>=', $request->date_from))
            ->when($request->date_to, fn($q) => $q->whereDate('opened_at', '<=', $request->date_to))
            ->sum('closing_cash');
            
        $totalSalesToday = Transaction::withoutGlobalScopes()
            ->completed()
            ->when($request->date_from, fn($q) => $q->whereDate('created_at', '>=', $request->date_from))
            ->when($request->date_to, fn($q) => $q->whereDate('created_at', '<=', $request->date_to))
            ->when(!$request->date_from && !$request->date_to, fn($q) => $q->whereDate('created_at', today()))
            ->sum('total');
        
        $totalDiscrepancy = Shift::where('status', 'closed')
            ->when($request->date_from, fn($q) => $q->whereDate('opened_at', '>=', $request->date_from))
            ->when($request->date_to, fn($q) => $q->whereDate('opened_at', '<=', $request->date_to))
            ->sum('discrepancy');

        $activeShift = Shift::activeShift();
        $users = \App\Models\User::where('is_active', true)->get();
        $laciBalance = $this->getCurrentLaciBalance();

        // Extra stats for insights
        $bestCashier = Shift::select('opened_by', DB::raw('SUM(total_sales) as total'))
            ->where('status', 'closed')
            ->whereDate('opened_at', today())
            ->groupBy('opened_by')
            ->orderByDesc('total')
            ->first()?->opener;
            
        if($bestCashier) {
            $bestCashier->total = Shift::where('opened_by', $bestCashier->id)->whereDate('opened_at', today())->sum('total_sales');
        }

        $highestShift = Shift::where('status', 'closed')->orderByDesc('total_sales')->first();
        $avgDiscrepancy = Shift::where('status', 'closed')->avg('discrepancy') ?: 0;

        return view('reports.shifts', compact(
            'shifts', 'activeShift', 'users', 'laciBalance', 
            'activeShiftsCount', 'totalClosingCash', 'totalSalesToday', 'totalDiscrepancy',
            'bestCashier', 'highestShift', 'avgDiscrepancy'
        ));
    }

    private function getCurrentLaciBalance()
    {
        return (float) Cashflow::where('source', 'pos_cash')
            ->where('bank_sync_status', 'synced')
            ->sum(\Illuminate\Support\Facades\DB::raw('CASE WHEN type = "income" THEN amount ELSE -amount END'));
    }

    public function open(Request $request)
    {
        if (Shift::activeShift()) {
            return back()->with('error', 'Sudah ada shift yang sedang berjalan!');
        }

        $request->validate([
            'opening_cash' => 'required|numeric|min:0',
            'user_id' => 'nullable|exists:users,id',
            'notes' => 'nullable|string|max:255',
        ]);

        // Owner bisa buka shift untuk user lain
        $userId = $request->user_id ?: auth()->id();

        $shift = Shift::create([
            'opened_by' => $userId,
            'opening_cash' => $request->opening_cash,
            'status' => 'open',
            'notes' => $request->notes,
            'opened_at' => now(),
        ]);

        if ($request->opening_cash >= 0) {
            \App\Models\Cashflow::create([
                'user_id' => $userId,
                'shift_id' => $shift->id,
                'worksheet_id' => $shift->worksheet_id,
                'type' => 'income',
                'transaction_category' => 'adjustment',
                'category' => 'Modal Awal Kasir',
                'description' => 'Kas Awal Shift',
                'amount' => $request->opening_cash,
                'source' => 'pos_cash',
                'bank_sync_status' => 'synced',
                'transaction_date' => today(),
            ]);
        }

        return back()->with('success', 'Shift berhasil dibuka!');
    }

    public function close(Request $request, Shift $shift)
    {
        if ($shift->status !== 'open') {
            return back()->with('error', 'Shift sudah ditutup!');
        }

        $request->validate([
            'closing_cash' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:500',
        ]);

        // Calculate all financial data for the shift
        $cashSales = Transaction::withoutGlobalScopes()->where('shift_id', $shift->id)
            ->completed()
            ->where('payment_method', 'cash')
            ->sum('total');

        $bankSales = Transaction::withoutGlobalScopes()->where('shift_id', $shift->id)
            ->completed()
            ->whereIn('payment_method', ['transfer', 'qris', 'debit'])
            ->sum('total');

        $totalSales = Transaction::withoutGlobalScopes()->where('shift_id', $shift->id)
            ->completed()->sum('total');

        $totalTransactions = Transaction::withoutGlobalScopes()->where('shift_id', $shift->id)
            ->completed()->count();

        // Cash expenses within this shift
        $cashExpenses = Cashflow::withoutGlobalScopes()->where('shift_id', $shift->id)
            ->where('type', 'expense')
            ->where('source', 'pos_cash')
            ->sum('amount');

        // Expected cash = Modal Awal + Penjualan Tunai - Pengeluaran Tunai
        $expectedCash = $shift->opening_cash + $cashSales - $cashExpenses;
        $discrepancy = $request->closing_cash - $expectedCash;

        $shift->update([
            'closed_by' => auth()->id(),
            'closing_cash' => $request->closing_cash,
            'expected_cash' => $expectedCash,
            'discrepancy' => $discrepancy,
            'cash_sales' => $cashSales,
            'bank_sales' => $bankSales,
            'cash_expenses' => $cashExpenses,
            'total_sales' => $totalSales,
            'total_transactions' => $totalTransactions,
            'status' => 'closed',
            'notes' => $request->notes ?? $shift->notes,
            'closed_at' => now(),
        ]);

        return redirect()->route('shifts.index')->with('success', 'Shift berhasil ditutup!');
    }

    /**
     * Cash Out: Catat pengeluaran tunai dari laci kasir saat shift aktif.
     * Dicatat sebagai cashflow expense dengan source pos_cash.
     */
    public function cashOut(Request $request, Shift $shift)
    {
        if ($shift->status !== 'open') {
            if ($request->expectsJson()) return response()->json(['error' => 'Shift sudah tidak aktif!'], 422);
            return back()->with('error', 'Shift sudah tidak aktif!');
        }

        $request->validate([
            'amount'      => 'required|numeric|min:1',
            'description' => 'required|string|max:255',
            'category'    => 'nullable|string|max:100', // Ini sekarang berisi Main Category (Operasional, etc)
        ]);

        $worksheetId = session('worksheet_id') ?: \App\Models\Worksheet::first()->id;
        
        // Map category to lowercase for expense_type
        $expenseType = strtolower($request->category ?: 'operasional');

        // 1. SIMPAN KE BIAYA BULANAN (MonthlyUsage)
        $monthlyUsage = \App\Models\MonthlyUsage::create([
            'worksheet_id'   => $worksheetId,
            'expense_type'   => $expenseType, 
            'expense_name'   => $request->description, // Berisi SubCategory + Catatan
            'sub_category'   => $request->category ?: 'Operasional',
            'quantity'       => 1,
            'unit'           => 'Pcs',
            'payment_method' => 'tunai',
            'usage_amount'   => $request->amount,
            'expense_date'   => today(),
            'month'          => now()->month,
            'year'           => now()->year,
            'description'    => $request->description,
            'status'         => 'dibayar',
            'sync_status'    => 'synced',
        ]);

        // 2. SIMPAN KE CASHFLOW (Arus Kas)
        Cashflow::create([
            'user_id'          => auth()->id(),
            'shift_id'         => $shift->id,
            'worksheet_id'     => $worksheetId,
            'type'             => 'expense',
            'transaction_category' => 'expense',
            'category'         => $request->category ?: 'Operasional',
            'description'      => 'POS Cash Out: ' . $request->description,
            'amount'           => $request->amount,
            'source'           => 'pos_cash',
            'bank_sync_status' => 'synced',
            'transaction_date' => today(),
            'reference_id'     => $monthlyUsage->id,
            'reference_type'   => 'MonthlyUsage'
        ]);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Cash Out berhasil dicatat.']);
        }

        $dispAmount = is_array($request->amount) ? 0 : $request->amount;
        return back()->with('success', 'Cash Out sebesar Rp ' . number_format($dispAmount, 0, ',', '.') . ' berhasil dicatat!');
    }

    public function show(Shift $shift)
    {
        $shift->load(['opener', 'closer', 'transactions.items']);
        $transactions = $shift->transactions()->with(['user', 'items'])->paginate(20);

        // Recalculate for the view
        $cashSales = Transaction::where('shift_id', $shift->id)->completed()->where('payment_method', 'cash')->sum('total');
        $bankSales = Transaction::where('shift_id', $shift->id)->completed()->whereIn('payment_method', ['transfer', 'qris', 'debit'])->sum('total');
        $cashExpenses = Cashflow::where('shift_id', $shift->id)->where('type', 'expense')->where('source', 'pos_cash')->sum('amount');
        $expectedCash = $shift->opening_cash + $cashSales - $cashExpenses;

        return view('shifts.show', compact('shift', 'transactions', 'cashSales', 'bankSales', 'cashExpenses', 'expectedCash'));
    }

    /**
     * API: Get shift summary data for close-shift modal
     */
    public function getSummary(Shift $shift)
    {
        $cashSales = Transaction::withoutGlobalScopes()->where('shift_id', $shift->id)->completed()->where('payment_method', 'cash')->sum('total');
        $qrisSales = Transaction::withoutGlobalScopes()->where('shift_id', $shift->id)->completed()->where('payment_method', 'qris')->sum('total');
        $transferSales = Transaction::withoutGlobalScopes()->where('shift_id', $shift->id)->completed()->where('payment_method', 'transfer')->sum('total');
        $debitSales = Transaction::withoutGlobalScopes()->where('shift_id', $shift->id)->completed()->where('payment_method', 'debit')->sum('total');
        
        $bankSales = $qrisSales + $transferSales + $debitSales;
        $totalSales = $cashSales + $bankSales;
        $totalTransactions = Transaction::withoutGlobalScopes()->where('shift_id', $shift->id)->completed()->count();
        $cashExpenses = Cashflow::withoutGlobalScopes()->where('shift_id', $shift->id)->where('type', 'expense')->where('source', 'pos_cash')->sum('amount');
        $expectedCash = $shift->opening_cash + $cashSales - $cashExpenses;

        $duration = '';
        if ($shift->closed_at) {
            $duration = $shift->opened_at->diff($shift->closed_at)->format('%h j %i m');
        } else {
            $duration = $shift->opened_at->diff(now())->format('%h j %i m') . ' (berjalan)';
        }

        return response()->json([
            'id' => $shift->id,
            'opener' => $shift->opener->name ?? 'N/A',
            'status' => $shift->status,
            'opened_at' => $shift->opened_at->format('d M Y H:i'),
            'closed_at' => $shift->closed_at ? $shift->closed_at->format('d M Y H:i') : '-',
            'duration' => str_replace(['j', 'm'], ['jam', 'menit'], $duration),
            'opening_cash' => (float)$shift->opening_cash,
            'closing_cash' => (float)($shift->closing_cash ?? 0),
            'cash_sales' => (float)$cashSales,
            'qris_sales' => (float)$qrisSales,
            'transfer_sales' => (float)$transferSales,
            'debit_sales' => (float)$debitSales,
            'bank_sales' => (float)$bankSales,
            'total_sales' => (float)$totalSales,
            'total_transactions' => $totalTransactions,
            'cash_expenses' => (float)$cashExpenses,
            'expected_cash' => (float)$expectedCash,
            'discrepancy' => $shift->status === 'closed' ? (float)(($shift->closing_cash ?? 0) - $expectedCash) : null,
            'notes' => $shift->notes,
        ]);
    }

    public function update(Request $request, Shift $shift)
    {
        $request->validate([
            'opening_cash' => 'required|numeric|min:0',
            'closing_cash' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:500',
        ]);

        $openingCash = $request->opening_cash;
        $closingCash = $request->closing_cash ?? $shift->closing_cash;

        $cashSales = Transaction::withoutGlobalScopes()->where('shift_id', $shift->id)->completed()->where('payment_method', 'cash')->sum('total');
        $cashExpenses = Cashflow::withoutGlobalScopes()->where('shift_id', $shift->id)->where('type', 'expense')->where('source', 'pos_cash')->sum('amount');
        
        $expectedCash = $openingCash + $cashSales - $cashExpenses;
        $discrepancy = null;
        
        if ($shift->status === 'closed') {
            $discrepancy = $closingCash - $expectedCash;
        }

        $shift->update([
            'opening_cash' => $openingCash,
            'closing_cash' => $closingCash,
            'expected_cash' => $expectedCash,
            'discrepancy' => $discrepancy,
            'notes' => $request->notes,
        ]);

        return back()->with('success', 'Data shift berhasil diperbarui!');
    }

    public function destroy(Shift $shift)
    {
        try {
            \Illuminate\Support\Facades\DB::transaction(function () use ($shift) {
                // Ambil semua transaksi tanpa mempedulikan global scope cabang
                $transactions = \App\Models\Transaction::withoutGlobalScopes()->where('shift_id', $shift->id)->get();
                
                foreach ($transactions as $trx) {
                    \App\Models\Payment::where('transaction_id', $trx->id)->delete();
                    \App\Models\StockMutation::where('reference', $trx->invoice_number)->delete();
                    $trx->items()->delete();
                    $trx->delete();
                }
                
                // Delete related cashflows (juga ignore global scope)
                \App\Models\Cashflow::withoutGlobalScopes()->where('shift_id', $shift->id)->delete();
                
                // Delete the shift
                $shift->delete();
            });

            return back()->with('success', 'Data shift beserta seluruh transaksi di dalamnya berhasil dihapus!');
        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan saat menghapus shift: ' . $e->getMessage());
        }
    }
}
