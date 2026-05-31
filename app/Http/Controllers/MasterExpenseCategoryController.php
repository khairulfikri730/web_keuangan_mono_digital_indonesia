<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MasterExpenseCategory;
use App\Models\Worksheet;

class MasterExpenseCategoryController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'color' => 'required|string|max:50',
        ]);

        $worksheetId = session('worksheet_id') ?: Worksheet::first()->id;

        MasterExpenseCategory::create([
            'worksheet_id' => $worksheetId,
            'name' => $request->name,
            'color' => $request->color,
        ]);

        return redirect()->route('expense_categories.index')->with('success', 'Master jenis biaya berhasil ditambahkan!');
    }

    public function update(Request $request, MasterExpenseCategory $master_expense_category)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'color' => 'required|string|max:50',
        ]);

        $oldName = $master_expense_category->name;
        // The old categories used lowercase keys like 'operasional', 'consumable', 'bahan_baku', 'variabel'
        // or whatever the name was. We should update both the exact match and the lowercase/snake_case match.
        $possibleOldNames = [
            $oldName, 
            strtolower($oldName), 
            str_replace(' ', '_', strtolower($oldName))
        ];

        $master_expense_category->update([
            'name' => $request->name,
            'color' => $request->color,
        ]);

        $newNameSnake = str_replace(' ', '_', strtolower($request->name));

        \App\Models\ExpenseCategory::whereIn('parent_category', $possibleOldNames)
            ->where('worksheet_id', $master_expense_category->worksheet_id)
            ->update(['parent_category' => $newNameSnake]);

        return redirect()->route('expense_categories.index')->with('success', 'Master jenis biaya berhasil diperbarui!');
    }

    public function destroy(MasterExpenseCategory $master_expense_category)
    {
        $oldName = $master_expense_category->name;
        $possibleOldNames = [
            $oldName, 
            strtolower($oldName), 
            str_replace(' ', '_', strtolower($oldName))
        ];

        \App\Models\ExpenseCategory::whereIn('parent_category', $possibleOldNames)
            ->where('worksheet_id', $master_expense_category->worksheet_id)
            ->delete();

        $master_expense_category->delete();
        return redirect()->route('expense_categories.index')->with('success', 'Master jenis biaya berhasil dihapus!');
    }
}
