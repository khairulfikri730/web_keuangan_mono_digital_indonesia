<?php

namespace App\Http\Controllers;

use App\Models\Shift;
use App\Models\Transaction;
use App\Models\Cashflow;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ShiftController extends Controller
{
    public function index()
    {
        $shifts = Shift::with(['opener', 'closer'])->latest()->paginate(15);
        $activeShift = Shift::activeShift();
        $users = \App\Models\User::where('is_active', true)->get();
        return view('shifts.index', compact('shifts', 'activeShift', 'users'));
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

        Shift::create([
            'opened_by' => $userId,
            'opening_cash' => $request->opening_cash,
            'status' => 'open',
            'notes' => $request->notes,
            'opened_at' => now(),
        ]);

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
        $cashSales = Transaction::where('shift_id', $shift->id)
            ->completed()
            ->where('payment_method', 'cash')
            ->sum('total');

        $bankSales = Transaction::where('shift_id', $shift->id)
            ->completed()
            ->whereIn('payment_method', ['transfer', 'qris', 'debit'])
            ->sum('total');

        $totalSales = Transaction::where('shift_id', $shift->id)
            ->completed()->sum('total');

        $totalTransactions = Transaction::where('shift_id', $shift->id)
            ->completed()->count();

        // Cash expenses within this shift
        $cashExpenses = Cashflow::where('shift_id', $shift->id)
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
        $cashSales = Transaction::where('shift_id', $shift->id)->completed()->where('payment_method', 'cash')->sum('total');
        $bankSales = Transaction::where('shift_id', $shift->id)->completed()->whereIn('payment_method', ['transfer', 'qris', 'debit'])->sum('total');
        $totalSales = $cashSales + $bankSales;
        $totalTransactions = Transaction::where('shift_id', $shift->id)->completed()->count();
        $cashExpenses = Cashflow::where('shift_id', $shift->id)->where('type', 'expense')->where('source', 'pos_cash')->sum('amount');
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

        $cashSales = Transaction::where('shift_id', $shift->id)->completed()->where('payment_method', 'cash')->sum('total');
        $cashExpenses = Cashflow::where('shift_id', $shift->id)->where('type', 'expense')->where('source', 'pos_cash')->sum('amount');
        
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
                // Delete related transaction items first
                foreach ($shift->transactions as $trx) {
                    $trx->items()->delete();
                    $trx->delete();
                }
                
                // Delete related cashflows
                \App\Models\Cashflow::where('shift_id', $shift->id)->delete();
                
                // Delete the shift
                $shift->delete();
            });

            return back()->with('success', 'Data shift beserta seluruh transaksi di dalamnya berhasil dihapus!');
        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan saat menghapus shift: ' . $e->getMessage());
        }
    }
}
