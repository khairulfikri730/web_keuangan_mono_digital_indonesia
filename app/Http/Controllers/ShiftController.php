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
    protected $financialService;

    public function __construct(\App\Services\FinancialReportService $financialService)
    {
        $this->financialService = $financialService;
    }

    public function index(Request $request)
    {
        $activeShift = Shift::activeShiftForUser(auth()->id());
        $isLiveShift = $request->shift === 'live' || $request->period === 'live';
        $period = $request->period ?? 'today';
        
        $dfParam = is_array($request->date_from) ? null : $request->date_from;
        $dtParam = is_array($request->date_to) ? null : $request->date_to;

        if ($isLiveShift && $activeShift) {
            $dateFrom = $activeShift->opened_at->copy()->startOfDay();
            $dateTo = now()->endOfDay();
        } else {
            if ($dfParam && $dtParam) {
                $dateFrom = Carbon::parse($dfParam)->startOfDay();
                $dateTo = Carbon::parse($dtParam)->endOfDay();
            } else {
                switch ($period) {
                    case 'yesterday':
                        $dateFrom = now()->subDay()->startOfDay();
                        $dateTo = now()->subDay()->endOfDay();
                        break;
                    case 'week':
                        $dateFrom = now()->startOfWeek();
                        $dateTo = now()->endOfWeek();
                        break;
                    case 'month':
                        $dateFrom = now()->startOfMonth();
                        $dateTo = now()->endOfMonth();
                        break;
                    case 'year':
                        $dateFrom = now()->startOfYear();
                        $dateTo = now()->endOfYear();
                        break;
                    case 'today':
                    default:
                        $dateFrom = now()->startOfDay();
                        $dateTo = now()->endOfDay();
                        break;
                }
            }
        }

        $worksheetId = session('active_worksheet_id');

        $query = Shift::with(['opener', 'closer'])
            ->whereBetween('opened_at', [$dateFrom, $dateTo])
            ->when($worksheetId, fn($q) => $q->where('worksheet_id', $worksheetId))
            ->when($request->status && !is_array($request->status), fn($q) => $q->where('status', $request->status))
            ->when($request->user_id && !is_array($request->user_id), fn($q) => $q->where('opened_by', $request->user_id));

        $shifts = $query->latest()->paginate(15)->withQueryString();
        
        $activeShiftsCount = Shift::where('status', 'open')->count();
        $closedShifts = (clone $query)->where('status', 'closed')->get();
        $totalClosingCash = $closedShifts->sum('closing_cash');
            
        $shiftIds = $closedShifts->pluck('id')->toArray();
        if ($activeShift) {
            $shiftIds[] = $activeShift->id;
        }

        $totalSalesToday = Transaction::withoutGlobalScopes()->completed()
            ->when($isLiveShift && $activeShift, fn($q) => $q->where('shift_id', $activeShift->id))
            ->when(!($isLiveShift && $activeShift), fn($q) => $q->whereIn('shift_id', $shiftIds))
            ->when($worksheetId, fn($q) => $q->where('worksheet_id', $worksheetId))
            ->sum('total');
        
        $totalDiscrepancy = 0;
        foreach ($closedShifts as $s) {
            $cashSales = Transaction::withoutGlobalScopes()->where('shift_id', $s->id)->where('payment_method', 'cash')->where('status', 'completed')->sum('total');
            $cashExpenses = Cashflow::withoutGlobalScopes()->where('shift_id', $s->id)->where('transaction_category', 'expense')->where('source', 'pos_cash')->sum('amount');
            $expected = $s->opening_cash + $cashSales - $cashExpenses;
            $totalDiscrepancy += ($s->closing_cash - $expected);
        }

        // Total Expenses & Net Profit
        $currentSales = 0;
        $currentCashExpenses = 0;
        $currentBankSales = 0;
        $currentBankExpenses = 0;
        $currentTotalExpenses = 0;
        $laciBalance = $this->getCurrentLaciBalance();
        $currentExpected = $laciBalance;

        $laciMovements = collect(); // transfers/adjustments during active shift

        $recentTransactions = collect();
        $recentExpenses = collect();
        
        if ($activeShift) {
            $sumLive = $this->financialService->getShiftSummary($activeShift->id, $worksheetId);
            $currentSales = $sumLive->cash_sales;
            $currentCashExpenses = $sumLive->cash_expense;
            $currentBankSales = $sumLive->bank_sales;
            $currentBankExpenses = $sumLive->bank_expense;
            $currentTotalExpenses = $sumLive->total_expense;

            $nonPosNet = (float) Cashflow::withoutGlobalScopes()
                ->where('shift_id', $activeShift->id)
                ->where('source', 'pos_cash')
                ->where('category', '!=', 'Penjualan')
                ->where('transaction_category', '!=', 'expense')
                ->sum(DB::raw('CASE WHEN type = "income" THEN amount ELSE -amount END'));

            $currentExpected = $activeShift->opening_cash + $currentSales - $currentCashExpenses + $nonPosNet;

            // Fetch non-POS cashflow movements during active shift (transfers, manual adjustments)
            $laciMovements = Cashflow::withoutGlobalScopes()
                ->where('shift_id', $activeShift->id)
                ->where('source', 'pos_cash')
                ->where('category', '!=', 'Penjualan')
                ->orderBy('created_at')
                ->get();
                
            // Fetch recent transactions for timeline
            $recentTransactions = Transaction::withoutGlobalScopes()
                ->where('shift_id', $activeShift->id)
                ->with('user')
                ->latest()
                ->take(10)
                ->get();
                
            // Fetch recent expenses for timeline
            $recentExpenses = Cashflow::withoutGlobalScopes()
                ->where('shift_id', $activeShift->id)
                ->where('type', 'expense')
                ->latest()
                ->take(10)
                ->get();
        }

        $laciBalance = $this->getCurrentLaciBalance();
        $users = \App\Models\User::all();
        $activeCrewShifts = Shift::with('opener')->where('status', 'open')->get();
        $todayTarget = \App\Models\Setting::get('daily_target') ?? 3000000;
        $drawerPulsePin = (int) (\App\Models\Setting::get('drawer_pulse_pin', '0'));
        
        // Calculate total sales for today to compare with target
        $todaySalesTotal = Transaction::completed()
            ->whereBetween('created_at', [now()->startOfDay(), now()->endOfDay()])
            ->when($worksheetId, fn($q) => $q->where('worksheet_id', $worksheetId))
            ->sum('total');

        return view('shifts.index', compact(
            'activeShift', 'users', 'laciBalance',
            'currentSales', 'currentCashExpenses', 'currentBankSales', 'currentBankExpenses', 'currentTotalExpenses', 'currentExpected',
            'laciMovements', 'recentTransactions', 'recentExpenses', 'activeCrewShifts', 
            'todayTarget', 'todaySalesTotal', 'drawerPulsePin'
        ));
    }

    private function getCurrentLaciBalance()
    {
        // Always mirror the Cashflow dashboard "Tunai/Laci" value exactly.
        // This ensures real-time reflection of:
        //   - POS sales (cash)
        //   - Cash-out expenses during shift
        //   - Transfers: laci → bank (reduces laci)
        //   - Transfers: bank → laci (increases laci)
        //   - Any manual saldo adjustments
        // BelongsToWorksheet global scope on Cashflow handles worksheet filtering automatically.
        return (float) Cashflow::where('source', 'pos_cash')
            ->where('bank_sync_status', 'synced')
            ->sum(\Illuminate\Support\Facades\DB::raw('CASE WHEN type = "income" THEN amount ELSE -amount END'));
    }

    public function open(Request $request)
    {
        $activeShift = Shift::activeShift();
        if ($activeShift) {
            // Jika user punya akses buka shift, mereka bisa otomatis "bergabung" ke shift yang ada
            if (auth()->user()->hasPermission('shifts.manage')) {
                if (!$activeShift->isUserAssigned(auth()->id())) {
                    $assigned = $activeShift->assigned_users ?? [];
                    $assigned[] = auth()->id();
                    $activeShift->update(['assigned_users' => array_unique(array_map('intval', $assigned))]);
                    return back()->with('success', 'Berhasil bergabung dengan shift yang sedang aktif!');
                } else {
                    return back()->with('error', 'Anda sudah tergabung dalam shift ini!');
                }
            }
            return back()->with('error', 'Sudah ada shift yang sedang berjalan!');
        }

        $request->validate([
            'opening_cash' => 'required|numeric|min:0',
            'user_ids'     => 'nullable|array',
            'user_ids.*'   => 'exists:users,id',
            'notes'        => 'nullable|string|max:255',
            'opened_at'    => 'nullable|date',
        ]);

        // Collect assigned users: support both old single user_id and new multi user_ids[]
        $assignedUserIds = $request->input('user_ids', []);
        if (empty($assignedUserIds) && $request->filled('user_id')) {
            $assignedUserIds = [$request->user_id];
        }
        if (empty($assignedUserIds)) {
            $assignedUserIds = [auth()->id()];
        }
        // Always make sure opener is included
        if (!in_array(auth()->id(), $assignedUserIds)) {
            $assignedUserIds[] = auth()->id();
        }
        $assignedUserIds = array_map('intval', array_unique($assignedUserIds));

        // Primary opener is the first selected user (or current auth user)
        $primaryUserId = $assignedUserIds[0];

        $shift = Shift::create([
            'opened_by'      => $primaryUserId,
            'assigned_users' => $assignedUserIds,
            'opening_cash'   => $request->opening_cash,
            'status'         => 'open',
            'notes'          => $request->notes,
            'opened_at'      => $request->opened_at ? \Carbon\Carbon::parse($request->opened_at) : now(),
        ]);



        return back()->with('success', 'Shift berhasil dibuka!');
    }


    public function close(Request $request, Shift $shift)
    {
        if (!in_array($shift->status, ['open', 'pending_approval'])) {
            return back()->with('error', 'Shift sudah tidak aktif atau sudah ditutup!');
        }

        if (!$shift->isUserAssigned(auth()->id())) {
            return back()->with('error', 'Anda tidak memiliki akses untuk menutup shift ini!');
        }

        $request->validate([
            'closing_cash' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:500',
        ]);

        // Calculate all financial data for the shift using service for consistency
        $worksheetId = session('active_worksheet_id');
        $summary = $this->financialService->getShiftSummary($shift->id, $worksheetId);

        $cashSales = $summary->cash_sales;
        $bankSales = $summary->bank_sales;
        $totalSales = $summary->total_income;
        $totalTransactions = Transaction::withoutGlobalScopes()->where('shift_id', $shift->id)->completed()->count();
        $cashExpenses = $summary->cash_expense;
        $bankExpenses = $summary->bank_expense;

        // Non-POS transfers and adjustments affecting pos_cash during the shift
        $transfers = (float) \App\Models\Cashflow::withoutGlobalScopes()
            ->where('shift_id', $shift->id)
            ->where('source', 'pos_cash')
            ->where('category', '!=', 'Penjualan')
            ->where('transaction_category', '!=', 'expense')
            ->sum(\Illuminate\Support\Facades\DB::raw('CASE WHEN type = "income" THEN amount ELSE -amount END'));

        // Expected cash = Modal Awal + Penjualan Tunai - Pengeluaran Tunai + Transfers/Adjustments
        $expectedCash = $shift->opening_cash + $cashSales - $cashExpenses + $transfers;
        $discrepancy = $request->closing_cash - $expectedCash;

        $approvalRequired = \App\Models\Setting::get('shift_approval_required', '1') == '1';
        $isKasir = auth()->user()->isKasir();
        $newStatus = ($approvalRequired && $isKasir) ? 'pending_approval' : 'closed';

        $shift->update([
            'closed_by' => auth()->id(),
            'closing_cash' => $request->closing_cash,
            'expected_cash' => $expectedCash,
            'discrepancy' => $discrepancy,
            'cash_sales' => $cashSales,
            'bank_sales' => $bankSales,
            'cash_expenses' => $cashExpenses,
            'bank_expenses' => $bankExpenses,
            'total_sales' => $totalSales,
            'total_transactions' => $totalTransactions,
            'status' => $newStatus,
            'notes' => $request->notes ?? $shift->notes,
            'closed_at' => now(),
        ]);

        $this->syncShiftTransactionsToCashflow($shift);

        if ($newStatus === 'pending_approval') {
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect()->route('login')->with('success', 'Laporan shift berhasil dikirim. Menunggu persetujuan Owner.');
        }

        return redirect()->route('shifts.index')->with('success', 'Shift berhasil ditutup!');
    }

    public function approve(Shift $shift)
    {
        if ($shift->status !== 'pending_approval') {
            return back()->with('error', 'Shift tidak dalam status menunggu persetujuan!');
        }

        if (!auth()->user()->hasPermission('shifts.manage')) {
            return back()->with('error', 'Anda tidak memiliki akses untuk menyetujui shift!');
        }

        $shift->update([
            'status' => 'closed',
            'closed_at' => $shift->closed_at ?? now(), // Ensure closed_at is set
        ]);

        $this->syncShiftTransactionsToCashflow($shift);

        return back()->with('success', 'Shift berhasil disetujui dan ditutup!');
    }

    private function syncShiftTransactionsToCashflow(Shift $shift)
    {
        $isAutoSync = \App\Models\Setting::get('auto_sync_cashflow', '1') === '1';
        $syncStatus = $isAutoSync ? 'synced' : 'pending';

        $transactions = \App\Models\Transaction::withoutGlobalScopes()
            ->where('shift_id', $shift->id)
            ->whereIn('status', ['completed', 'pending'])
            ->get();
        
        foreach ($transactions as $tx) {
            if ($tx->status === 'completed' || ($tx->status === 'pending' && $tx->paid_so_far > 0)) {
                $amount = $tx->status === 'completed' && $tx->payment_method !== 'piutang' ? $tx->total : $tx->paid_so_far;
                if ($amount > 0) {
                    $sourceMap = [
                        'cash'     => 'pos_cash',
                        'qris'     => 'pos_bank',
                        'debit'    => 'pos_bank',
                        'transfer' => 'transfer',
                    ];
                    $source = $sourceMap[$tx->payment_method] ?? 'pos_cash';
                    if ($tx->payment_method === 'piutang') {
                        $source = 'pos_cash';
                    }

                    $cashflow = \App\Models\Cashflow::firstOrCreate([
                        'reference_id' => $tx->id,
                        'category' => $tx->payment_method === 'piutang' ? 'Uang Muka (DP)' : 'Penjualan'
                    ], [
                        'user_id'          => $tx->user_id,
                        'shift_id'         => $tx->shift_id,
                        'type'             => 'income',
                        'transaction_category' => 'income',
                        'description'      => ($tx->payment_method === 'piutang' ? 'DP pesanan #' : 'Penjualan POS #') . $tx->invoice_number,
                        'amount'           => $amount,
                        'reference'        => $tx->invoice_number,
                        'source'           => $source,
                        'bank_sync_status' => $syncStatus,
                        'transaction_date' => $tx->created_at ? $tx->created_at->format('Y-m-d') : today(),
                        'worksheet_id'     => $tx->worksheet_id,
                    ]);
                    
                    if ($cashflow->bank_sync_status !== $syncStatus) {
                        $cashflow->update(['bank_sync_status' => $syncStatus]);
                    }
                }
            }
        }
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
            'source'      => 'nullable|string|in:cash,bank',
        ]);

        $sourceParam = $request->source === 'bank' ? 'bank' : 'cash';
        $sourceType = $sourceParam === 'bank' ? 'pos_bank' : 'pos_cash';
        $paymentMethod = $sourceParam === 'bank' ? 'transfer' : 'tunai';

        $worksheetId = $shift->worksheet_id ?: session('active_worksheet_id') ?: \App\Models\Worksheet::first()->id;
        
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
            'payment_method' => $paymentMethod,
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
            'source'           => $sourceType,
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
        $cashExpenses = Cashflow::where('shift_id', $shift->id)->where('transaction_category', 'expense')->where('source', 'pos_cash')->sum('amount');
        
        $transfers = (float) \App\Models\Cashflow::withoutGlobalScopes()
            ->where('shift_id', $shift->id)
            ->where('source', 'pos_cash')
            ->where('category', '!=', 'Penjualan')
            ->where('transaction_category', '!=', 'expense')
            ->sum(\Illuminate\Support\Facades\DB::raw('CASE WHEN type = "income" THEN amount ELSE -amount END'));

        $expectedCash = $shift->opening_cash + $cashSales - $cashExpenses + $transfers;

        return view('shifts.show', compact('shift', 'transactions', 'cashSales', 'bankSales', 'cashExpenses', 'expectedCash'));
    }

    /**
     * API: Get shift summary data for close-shift modal
     */
    public function getSummary(Shift $shift)
    {
        $worksheetId = session('active_worksheet_id');
        $summary = $this->financialService->getShiftSummary($shift->id, $worksheetId);
        $incomeQuery = Transaction::withoutGlobalScopes()->where('shift_id', $shift->id)->completed();
        $qrisSales = (clone $incomeQuery)->where('payment_method', 'qris')->sum('total');
        $transferSales = (clone $incomeQuery)->where('payment_method', 'transfer')->sum('total');
        $debitSales = (clone $incomeQuery)->where('payment_method', 'debit')->sum('total');
        
        $cashSales = $summary->cash_sales;
        $bankSales = $summary->bank_sales;
        $totalSales = $summary->pos_income;
        $totalTransactions = Transaction::withoutGlobalScopes()->where('shift_id', $shift->id)->completed()->count();
        $cashExpenses = $summary->cash_expense;

        $transfers = (float) \App\Models\Cashflow::withoutGlobalScopes()
             ->where('shift_id', $shift->id)
             ->where('source', 'pos_cash')
             ->where('category', '!=', 'Penjualan')
             ->where('transaction_category', '!=', 'expense')
             ->sum(\Illuminate\Support\Facades\DB::raw('CASE WHEN type = "income" THEN amount ELSE -amount END'));

        $expectedCash = $shift->opening_cash + $cashSales - $cashExpenses + $transfers;

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
            'discrepancy' => in_array($shift->status, ['closed', 'pending_approval']) ? (float)(($shift->closing_cash ?? 0) - $expectedCash) : null,
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
        $cashExpenses = Cashflow::withoutGlobalScopes()->where('shift_id', $shift->id)->where('transaction_category', 'expense')->where('source', 'pos_cash')->sum('amount');
        
        $transfers = (float) \App\Models\Cashflow::withoutGlobalScopes()
            ->where('shift_id', $shift->id)
            ->where('source', 'pos_cash')
            ->where('category', '!=', 'Penjualan')
            ->where('transaction_category', '!=', 'expense')
            ->sum(\Illuminate\Support\Facades\DB::raw('CASE WHEN type = "income" THEN amount ELSE -amount END'));

        $expectedCash = $openingCash + $cashSales - $cashExpenses + $transfers;
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
                
                // Cari dan hapus MonthlyUsage (Pengeluaran Kasir) yang terkait dengan shift ini
                $expenseCashflows = \App\Models\Cashflow::withoutGlobalScopes()
                    ->where('shift_id', $shift->id)
                    ->where('reference_type', 'MonthlyUsage')
                    ->whereNotNull('reference_id')
                    ->get();
                
                $monthlyUsageIds = $expenseCashflows->pluck('reference_id')->toArray();
                if (!empty($monthlyUsageIds)) {
                    \App\Models\MonthlyUsage::whereIn('id', $monthlyUsageIds)->delete();
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
