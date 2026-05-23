<?php

namespace App\Http\Controllers;

use App\Models\MonthlyUsage;
use App\Models\Product;
use App\Models\Worksheet;
use App\Models\Cashflow;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MonthlyExpenseController extends Controller
{
    protected $financialService;

    public function __construct(\App\Services\FinancialReportService $financialService)
    {
        $this->financialService = $financialService;
    }

    public function index(Request $request)
    {
        $filter = $request->filter ?? $request->period ?? 'month';
        $start = is_array($request->start ?? $request->date_from) ? null : ($request->start ?? $request->date_from);
        $end = is_array($request->end ?? $request->date_to) ? null : ($request->end ?? $request->date_to);
        if ($start && $end) {
            $filter = 'custom';
        }
        
        $now = now();
        switch ($filter) {
            case 'today':
                $dateFrom = $now->copy()->startOfDay();
                $dateTo = $now->copy()->endOfDay();
                break;
            case 'yesterday':
                $dateFrom = $now->copy()->subDay()->startOfDay();
                $dateTo = $now->copy()->subDay()->endOfDay();
                break;
            case 'week':
                $dateFrom = $now->copy()->startOfWeek();
                $dateTo = $now->copy()->endOfWeek();
                break;
            case 'month':
                $dateFrom = $now->copy()->startOfMonth();
                $dateTo = $now->copy()->endOfMonth();
                break;
            case 'year':
                $dateFrom = $now->copy()->startOfYear();
                $dateTo = $now->copy()->endOfYear();
                break;
            case 'custom':
                $dateFrom = $start ? \Carbon\Carbon::parse($start)->startOfDay() : $now->copy()->startOfMonth();
                $dateTo = $end ? \Carbon\Carbon::parse($end)->endOfDay() : $now->copy()->endOfMonth();
                break;
            default:
                $dateFrom = $now->copy()->startOfDay();
                $dateTo = $now->copy()->endOfDay();
                break;
        }

        $worksheetId = session('active_worksheet_id');
        
        // 1. Unified Financial Summary (System Total)
        $finSummary = $this->financialService->getSummary($dateFrom, $dateTo, $worksheetId);
        $totalOmzet = $finSummary->total_income;
        $systemTotalExpense = $finSummary->total_expense;
        $labaBersih = $finSummary->net_profit;

        // 2. Local MonthlyUsage Query
        $query = MonthlyUsage::whereBetween('expense_date', [$dateFrom, $dateTo])
            ->when($worksheetId, fn($q) => $q->where('worksheet_id', $worksheetId));
            
        if ($request->expense_type && $request->expense_type !== 'all') {
            $query->where('expense_type', $request->expense_type);
        }

        $expenses = (clone $query)->orderBy('expense_date', 'desc')->paginate(15)->withQueryString();

        // Module-specific Summary Statistics
        $summary = (clone $query)->where('expense_name', 'not like', '%Transfer%')
            ->selectRaw('
            SUM(usage_amount) as total,
            SUM(CASE WHEN expense_type = "operasional" THEN usage_amount ELSE 0 END) as operasional_total,
            SUM(CASE WHEN expense_type = "consumable" THEN usage_amount ELSE 0 END) as consumable_total,
            SUM(CASE WHEN expense_type = "bahan_baku" THEN usage_amount ELSE 0 END) as bahan_baku_total,
            SUM(CASE WHEN expense_type = "variabel" THEN usage_amount ELSE 0 END) as variabel_total
        ')->first();

        // TOP Pengeluaran (Berdasarkan Barang/Sub Kategori)
        $topExpenses = (clone $query)
            ->selectRaw('expense_name, SUM(usage_amount) as total_amount, COUNT(*) as trans_count')
            ->groupBy('expense_name')
            ->orderBy('total_amount', 'desc')
            ->limit(5)
            ->get();

        // Calculate Percentages for Top Expenses
        $grandTotal = $summary->total ?: 1;
        $topExpenses->each(function($item) use ($grandTotal) {
            $item->percentage = round(($item->total_amount / $grandTotal) * 100);
        });

        // Chart Data (Daily)
        $chartData = (clone $query)
            ->selectRaw('DATE(expense_date) as date, SUM(usage_amount) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Pengeluaran Berdasarkan Jenis Biaya (Sub Kategori) untuk Grafik
        $expenseBySubCategory = (clone $query)
            ->selectRaw('sub_category, SUM(usage_amount) as total')
            ->groupBy('sub_category')
            ->orderBy('total', 'desc')
            ->limit(10)
            ->get();

        // 3. Full System Expense Breakdown (for the interactive modal)
        $fullBreakdown = Cashflow::where('transaction_category', 'expense')
            ->whereBetween('transaction_date', [$dateFrom, $dateTo])
            ->when($worksheetId, fn($q) => $q->where('worksheet_id', $worksheetId))
            ->selectRaw('category, SUM(amount) as total, COUNT(*) as count')
            ->groupBy('category')
            ->orderByDesc('total')
            ->get();

        // Also get the top 10 individual system expenses
        $topSystemExpenses = Cashflow::where('transaction_category', 'expense')
            ->whereBetween('transaction_date', [$dateFrom, $dateTo])
            ->when($worksheetId, fn($q) => $q->where('worksheet_id', $worksheetId))
            ->latest('amount')
            ->limit(10)
            ->get();

        return view('monthly_expenses.index', compact(
            'expenses', 'summary', 'dateFrom', 'dateTo', 'chartData', 
            'totalOmzet', 'labaBersih', 'topExpenses', 'expenseBySubCategory',
            'filter', 'start', 'end', 'systemTotalExpense', 'fullBreakdown', 'topSystemExpenses'
        ));
    }

    public function create()
    {
        $worksheetId = session('worksheet_id') ?: Worksheet::first()->id;
        $categories = \App\Models\ExpenseCategory::where('worksheet_id', $worksheetId)->where('is_active', true)->get();
        return view('monthly_expenses.create', compact('categories'));
    }



    public function store(Request $request)
    {
        $request->validate([
            'expense_type' => 'required',
            'expense_name' => 'required',
            'usage_amount' => 'required|numeric',
            'expense_date' => 'required|date',
            'payment_method' => 'required'
        ]);

        $date = new \DateTime($request->expense_date);
        $worksheetId = session('worksheet_id') ?: \App\Models\Worksheet::first()->id;

        $expense = MonthlyUsage::create([
            'worksheet_id' => $worksheetId,
            'expense_type' => $request->expense_type,
            'expense_name' => $request->expense_name,
            'sub_category' => $request->sub_category,
            'quantity' => $request->quantity ?? 1,
            'unit' => $request->unit ?? 'Pcs',
            'payment_method' => $request->payment_method,
            'usage_amount' => $request->usage_amount,
            'expense_date' => $request->expense_date,
            'month' => (int)$date->format('m'),
            'year' => (int)$date->format('Y'),
            'description' => $request->description,
            'status' => 'dibayar',
            'sync_status' => 'synced',
        ]);

        // INTEGRASI CASHFLOW
        $source = in_array(strtolower($request->payment_method), ['tunai', 'cash']) ? 'pos_cash' : 'pos_bank';
        
        \App\Models\Cashflow::create([
            'user_id' => auth()->id(),
            'worksheet_id' => $worksheetId,
            'type' => 'expense',
            'transaction_category' => 'expense',
            'category' => 'Pengeluaran Bulanan', 
            'description' => 'Pengeluaran (' . $request->expense_type . '): ' . $request->expense_name,
            'source' => $source,
            'bank_sync_status' => 'synced',
            'amount' => $request->usage_amount,
            'transaction_date' => $request->expense_date,
            'reference_id' => $expense->id,
            'reference_type' => 'MonthlyUsage'
        ]);

        return redirect()->route('monthly_expenses.index')->with('success', 'Pengeluaran berhasil dicatat!');
    }

    public function edit(MonthlyUsage $monthly_expense)
    {
        $worksheetId = session('worksheet_id') ?: Worksheet::first()->id;
        $categories = \App\Models\ExpenseCategory::where('worksheet_id', $worksheetId)->where('is_active', true)->get();
        return view('monthly_expenses.create', [
            'expense' => $monthly_expense,
            'categories' => $categories,
            'isEdit' => true
        ]);
    }

    public function update(Request $request, MonthlyUsage $monthly_expense)
    {
        $request->validate([
            'expense_type' => 'required',
            'expense_name' => 'required',
            'usage_amount' => 'required|numeric',
            'expense_date' => 'required|date',
            'payment_method' => 'required'
        ]);

        $monthly_expense->update([
            'expense_type' => $request->expense_type,
            'expense_name' => $request->expense_name,
            'sub_category' => $request->sub_category,
            'quantity' => $request->quantity ?? 1,
            'unit' => $request->unit ?? 'Pcs',
            'payment_method' => $request->payment_method,
            'usage_amount' => $request->usage_amount,
            'expense_date' => $request->expense_date,
            'description' => $request->description,
        ]);

        // Update corresponding Cashflow
        \App\Models\Cashflow::where('reference_id', $monthly_expense->id)
            ->where('reference_type', 'MonthlyUsage')
            ->update([
                'amount' => $request->usage_amount,
                'description' => 'Pengeluaran (' . $request->expense_type . '): ' . $request->expense_name,
                'transaction_date' => $request->expense_date,
                'source' => in_array(strtolower($request->payment_method), ['tunai', 'cash']) ? 'pos_cash' : 'pos_bank',
            ]);

        return redirect()->route('monthly_expenses.index')->with('success', 'Pengeluaran berhasil diperbarui!');
    }

    public function destroy(MonthlyUsage $monthly_expense)
    {
        // Delete corresponding Cashflow
        \App\Models\Cashflow::where('reference_id', $monthly_expense->id)
            ->where('reference_type', 'MonthlyUsage')
            ->delete();

        $monthly_expense->delete();

        return redirect()->route('monthly_expenses.index')->with('success', 'Pengeluaran berhasil dihapus!');
    }
}
