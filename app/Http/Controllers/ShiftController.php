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
        return view('shifts.index', compact('shifts', 'activeShift'));
    }

    public function open(Request $request)
    {
        if (Shift::activeShift()) {
            return back()->with('error', 'Sudah ada shift yang sedang berjalan!');
        }

        $request->validate([
            'opening_cash' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:255',
        ]);

        Shift::create([
            'opened_by' => auth()->id(),
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

        $totalSales = Transaction::where('shift_id', $shift->id)
            ->completed()->sum('total');
        $totalTransactions = Transaction::where('shift_id', $shift->id)
            ->completed()->count();

        $shift->update([
            'closed_by' => auth()->id(),
            'closing_cash' => $request->closing_cash,
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
        return view('shifts.show', compact('shift', 'transactions'));
    }
}
