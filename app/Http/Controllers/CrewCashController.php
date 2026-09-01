<?php

namespace App\Http\Controllers;

use App\Models\CrewCashTransaction;
use Illuminate\Http\Request;
use Carbon\Carbon;

class CrewCashController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required|in:income,expense_operational,expense_personal',
            'amount' => 'required|numeric|min:1',
            'description' => 'required|string|max:255',
            'date' => 'required|date',
        ]);

        // If the user is crew or kasir, they can only insert for themselves.
        // If superadmin, they might pass a user_id. For now, assume auth()->id() or they pass user_id.
        $userId = auth()->id();
        if (auth()->user()->isOwner() && $request->filled('user_id')) {
            $userId = $request->user_id;
        }

        CrewCashTransaction::create([
            'user_id' => $userId,
            'type' => $request->type,
            'amount' => $request->amount,
            'description' => $request->description,
            'date' => $request->date,
        ]);

        return back()->with('success', 'Transaksi uang cash berhasil disimpan.');
    }

    public function destroy(CrewCashTransaction $crewCash)
    {
        // Only superadmin (owner) can delete
        if (!auth()->user()->isOwner()) {
            return back()->with('error', 'Anda tidak memiliki izin untuk menghapus data ini.');
        }

        $crewCash->delete();

        return back()->with('success', 'Transaksi uang cash berhasil dihapus.');
    }
}
