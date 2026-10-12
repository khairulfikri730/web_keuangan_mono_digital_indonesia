<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Payroll;
use App\Models\PayrollEntry;
use App\Models\ScheduleAssignment;
use Carbon\Carbon;

class PayrollController extends Controller
{
    public function index(Request $request)
    {
        $data = $this->getSalaryData($request);
        return view('schedules.payrolls.index', $data);
    }

    /**
     * Komponen gaji dibaca dari entri per tanggal (payroll_entries) sehingga menambah
     * nominal di tanggal baru tidak menimpa entri sebelumnya.
     */
    private function getSalaryData(Request $request)
    {
        $month = $request->input('month', now()->format('Y-m'));
        $periodStart = Carbon::parse($month . '-01')->startOfMonth();
        $periodEnd = $periodStart->copy()->endOfMonth();
        $daysInMonth = $periodStart->daysInMonth;
        $monthStart = $periodStart->format('Y-m-d');
        $monthEnd = $periodEnd->format('Y-m-d');

        $usersQuery = User::where('is_active', true)->orderBy('name');
        
        if (in_array(auth()->user()->role, ['crew', 'kasir'])) {
            $request->merge(['users' => auth()->id()]);
        }

        if ($request->filled('users')) {
            $userIds = explode(',', $request->users);
            $usersQuery->whereIn('id', $userIds);
        }

        $users = $usersQuery->get();

        // Get all shift assignments for this month
        $assignments = ScheduleAssignment::with('shift.location')
            ->whereBetween('date', [$monthStart, $monthEnd])
            ->get();

        // Status & catatan keseluruhan per crew per periode
        $payrolls = Payroll::where('period', $month)->get()->keyBy('user_id');

        $entries = PayrollEntry::with('user')
            ->whereBetween('date', [$monthStart, $monthEnd])
            ->orderBy('date')
            ->orderBy('id')
            ->get()
            ->groupBy('user_id');

        $crewCash = \App\Models\CrewCashTransaction::whereBetween('date', [$monthStart, $monthEnd])
            ->get()
            ->groupBy('user_id');

        $salaryData = [];
        $totalSistem = 0;

        foreach ($users as $user) {
            $userAsgn = $assignments->where('user_id', $user->id);
            $totalShifts = $userAsgn->count();

            // 1. Hitung Komisi Shift
            $komisiShift = 0;
            $todayStr = Carbon::today()->format('Y-m-d');
            
            foreach ($userAsgn as $asgn) {
                if ($asgn->date < $todayStr) {
                    if ($asgn->shift && $asgn->shift->location) {
                        $locId = $asgn->shift->schedule_location_id;
                        if (is_array($user->custom_rates) && isset($user->custom_rates[$locId])) {
                            $komisiShift += $user->custom_rates[$locId];
                        } else {
                            $komisiShift += $asgn->shift->location->shift_rate;
                        }
                    }
                }
            }

            // 2. Hitung Tunjangan (Gaji Pokok / Allowance)
            $allowance = 0;
            if ($user->allowance_type === 'daily') {
                $allowance = $user->allowance_amount * $daysInMonth;
            } elseif ($user->allowance_type === 'monthly') {
                $allowance = $user->allowance_amount;
            }

            // 3. Komponen tambahan dari entri gaji
            $userEntries = $entries->get($user->id, collect());
            $totals = $userEntries->groupBy('component')
                ->map(fn ($group) => (int) $group->sum('amount'));

            $motret = (int) $totals->get('photographer_fee', 0);
            $lembur = (int) $totals->get('overtime_fee', 0);
            $bonus = (int) $totals->get('bonus', 0);
            $deductionEntry = (int) $totals->get('deduction', 0);

            // 4. Crew Cash Photobox
            $userCash = $crewCash->get($user->id, collect());
            $crewCashIncome = (int) $userCash->where('type', 'income')->sum('amount');
            $crewCashExpenseOperational = (int) $userCash->where('type', 'expense_operational')->sum('amount');
            $crewCashExpensePersonal = (int) $userCash->where('type', 'expense_personal')->sum('amount');

            $crewCashExpense = $crewCashExpenseOperational + $crewCashExpensePersonal;
            $kasbon = $deductionEntry + $crewCashExpensePersonal;

            $totalKotor = $komisiShift + $allowance + $motret + $lembur + $bonus;
            $totalBersih = $totalKotor - $kasbon;
            $totalSistem += $totalBersih;

            $salaryData[] = [
                'user' => $user,
                'total_shifts' => $totalShifts,
                'komisi_shift' => $komisiShift,
                'allowance' => $allowance,
                'payroll' => $payrolls->get($user->id),
                'entries' => $userEntries,
                'motret' => $motret,
                'lembur' => $lembur,
                'bonus' => $bonus,
                'kasbon' => $kasbon,
                'total_bersih' => $totalBersih,
                'cash_income' => $crewCashIncome,
                'cash_expense' => $crewCashExpense,
                'cash_balance' => $crewCashIncome - $crewCashExpense,
            ];
        }
        
        return compact('month', 'salaryData', 'totalSistem');
    }

    /**
     * Menambahkan satu entri komponen gaji pada satu tanggal. Entri lain tidak tersentuh,
     * jadi lembur tanggal 10 tetap ada ketika lembur tanggal 12 ditambahkan.
     */
    public function storeEntry(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'period' => 'required|date_format:Y-m',
            'component' => 'required|in:' . implode(',', array_keys(PayrollEntry::COMPONENTS)),
            'date' => 'required|date',
            'amount' => 'required|numeric|min:1',
            'note' => 'nullable|string|max:500',
        ]);

        if (Carbon::parse($request->date)->format('Y-m') !== $request->period) {
            return back()->with('error', 'Tanggal entri harus berada di periode ' . $request->period . '.');
        }

        PayrollEntry::create([
            'user_id' => $request->user_id,
            'date' => $request->date,
            'component' => $request->component,
            'amount' => (int) $request->amount,
            'note' => $request->note ?: null,
            'source' => 'manual',
        ]);

        Payroll::firstOrCreate(['user_id' => $request->user_id, 'period' => $request->period]);

        return back()->with('success', PayrollEntry::componentLabel($request->component)
            . ' Rp ' . number_format($request->amount, 0, ',', '.')
            . ' dicatat untuk ' . Carbon::parse($request->date)->translatedFormat('d F Y') . '.');
    }

    public function destroyEntry(PayrollEntry $entry)
    {
        $entry->delete();

        return back()->with('success', 'Entri komponen gaji dihapus.');
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'period' => 'required|date_format:Y-m',
            'notes' => 'nullable|string',
        ]);

        Payroll::updateOrCreate(
            ['user_id' => $request->user_id, 'period' => $request->period],
            ['notes' => $request->notes]
        );

        return back()->with('success', 'Catatan keseluruhan gaji disimpan!');
    }

    public function exportPdf(Request $request)
    {
        $data = $this->getSalaryData($request);
        $monthText = Carbon::parse($data['month'].'-01')->translatedFormat('F Y');
        
        if ($request->has('preview')) {
            return view('schedules.payrolls.pdf', $data);
        }

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('schedules.payrolls.pdf', $data)
                  ->setPaper('a4', 'landscape');

        $filename = 'LAPORAN_GAJI_' . strtoupper(str_replace(' ', '_', $monthText)) . '_' . now()->format('YmdHis') . '.pdf';
        
        return $pdf->download($filename);
    }

    public function exportExcel(Request $request)
    {
        $data = $this->getSalaryData($request);
        $monthText = Carbon::parse($data['month'].'-01')->translatedFormat('F Y');
        $filename = 'LAPORAN_GAJI_' . strtoupper(str_replace(' ', '_', $monthText)) . '_' . now()->format('YmdHis') . '.xlsx';
        
        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\PayrollExport($data), $filename);
    }

    public function toggleStatus(Request $request, \App\Models\Payroll $payroll)
    {
        $payroll->update([
            'is_finalized' => !$payroll->is_finalized
        ]);

        return back()->with('success', 'Status penggajian berhasil diubah!');
    }
}
