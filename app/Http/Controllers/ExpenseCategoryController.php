<?php

namespace App\Http\Controllers;

use App\Models\ExpenseCategory;
use App\Models\Worksheet;
use Illuminate\Http\Request;

class ExpenseCategoryController extends Controller
{
    public function index()
    {
        $worksheetId = session('worksheet_id') ?: Worksheet::first()->id;

        // Ensure defaults exist for this worksheet
        if (\App\Models\MasterExpenseCategory::where('worksheet_id', $worksheetId)->count() === 0) {
            $defaults = [
                ['name' => 'Operasional', 'color' => 'blue'],
                ['name' => 'Consumable', 'color' => 'emerald'],
                ['name' => 'Bahan Baku', 'color' => 'purple'],
                ['name' => 'Variabel', 'color' => 'amber'],
            ];
            foreach ($defaults as $default) {
                \App\Models\MasterExpenseCategory::create([
                    'worksheet_id' => $worksheetId,
                    'name' => $default['name'],
                    'color' => $default['color'],
                ]);
            }
        }

        $masterCategories = \App\Models\MasterExpenseCategory::where('worksheet_id', $worksheetId)
            ->orderBy('id')
            ->get();

        $categories = ExpenseCategory::where('worksheet_id', $worksheetId)
            ->orderBy('parent_category')
            ->orderBy('name')
            ->get();
            
        return view('expense_categories.index', compact('categories', 'masterCategories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'parent_category' => 'required|string',
            'name' => 'required|string|max:255',
        ]);

        $worksheetId = session('worksheet_id') ?: Worksheet::first()->id;

        ExpenseCategory::create([
            'worksheet_id' => $worksheetId,
            'parent_category' => $request->parent_category,
            'name' => $request->name,
            'is_active' => true,
        ]);

        return redirect()->route('expense_categories.index')->with('success', 'Jenis biaya berhasil ditambahkan!');
    }

    public function update(Request $request, ExpenseCategory $expense_category)
    {
        $request->validate([
            'parent_category' => 'required|string',
            'name' => 'required|string|max:255',
            'is_active' => 'boolean'
        ]);

        $expense_category->update($request->all());

        return redirect()->route('expense_categories.index')->with('success', 'Jenis biaya berhasil diperbarui!');
    }

    public function destroy(ExpenseCategory $expense_category)
    {
        $expense_category->delete();
        return redirect()->route('expense_categories.index')->with('success', 'Jenis biaya berhasil dihapus!');
    }
}
