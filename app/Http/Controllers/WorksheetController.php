<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Worksheet;

class WorksheetController extends Controller
{
    public function switch(Request $request)
    {
        $id = $request->worksheet_id;
        
        if ($id === 'all' && auth()->user()->isOwner()) {
            session(['active_worksheet_id' => 'all']);
        } else {
            $worksheet = Worksheet::findOrFail($id);
            // Ensure kasir has access
            if (!auth()->user()->isOwner() && !auth()->user()->worksheets->contains($worksheet->id)) {
                abort(403, 'Unauthorized action.');
            }
            session(['active_worksheet_id' => $worksheet->id]);
        }

        return back();
    }

    public function store(Request $request)
    {
        if (!auth()->user()->isOwner()) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'initial_balance' => 'required|numeric|min:0',
        ]);

        $worksheet = Worksheet::create($validated);

        // Auto-assign owner and switch to it if needed
        session(['active_worksheet_id' => $worksheet->id]);

        return back()->with('success', 'Worksheet baru berhasil dibuat!');
    }

    public function update(Request $request, Worksheet $worksheet)
    {
        if (!auth()->user()->isOwner()) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'initial_balance' => 'required|numeric|min:0',
        ]);

        $worksheet->update($validated);

        return back()->with('success', 'Worksheet berhasil diperbarui!');
    }

    public function destroy(Worksheet $worksheet)
    {
        if (!auth()->user()->isOwner()) {
            abort(403);
        }

        $worksheet->delete();

        // If active is deleted, reset session
        if (session('active_worksheet_id') == $worksheet->id) {
            session()->forget('active_worksheet_id');
        }

        return back()->with('success', 'Worksheet berhasil dihapus!');
    }
}
