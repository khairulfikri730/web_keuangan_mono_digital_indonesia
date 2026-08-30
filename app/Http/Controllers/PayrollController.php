<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Payroll;
use App\Models\ScheduleAssignment;
use Carbon\Carbon;

class PayrollController extends Controller
{
    public function index(Request $request)
    {
        $data = $this->getSalaryData($request);
        return view('schedules.payrolls.index', $data);
    }

    private function getSalaryData(Request $request)
    {
        $month = $request->input('month', now()->format('Y-m'));
        $periodStart = Carbon::parse($month . '-01')->startOfMonth();
        $periodEnd = $periodStart->copy()->endOfMonth();
        $daysInMonth = $periodStart->daysInMonth;

        $usersQuery = User::where('is_active', true)->orderBy('name');
        
        if ($request->filled('users')) {
            $userIds = explode(',', $request->users);
            $usersQuery->whereIn('id', $userIds);
        }

        $users = $usersQuery->get();

        // Get all shift assignments for this month
        $assignments = ScheduleAssignment::with('shift.location')
            ->whereBetween('date', [$periodStart->format('Y-m-d'), $periodEnd->format('Y-m-d')])
            ->get();

        // Get payroll manual adjustments
        $payrolls = Payroll::where('period', $month)->get()->keyBy('user_id');

        $salaryData = [];
        $totalSistem = 0;

        foreach ($users as $user) {
            $userAsgn = $assignments->where('user_id', $user->id);
            $totalShifts = $userAsgn->count();

            // 1. Hitung Komisi Shift
            $komisiShift = 0;
            foreach ($userAsgn as $asgn) {
                if ($asgn->shift && $asgn->shift->location) {
                    $locId = $asgn->shift->schedule_location_id;
                    if (is_array($user->custom_rates) && isset($user->custom_rates[$locId])) {
                        $komisiShift += $user->custom_rates[$locId];
                    } else {
                        $komisiShift += $asgn->shift->location->shift_rate;
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

            // 3. Tambahan Lain dari tabel Payroll
            $payroll = $payrolls->get($user->id);
            $motret = $payroll ? $payroll->photographer_fee : 0;
            $lembur = $payroll ? $payroll->overtime_fee : 0;
            $bonus = $payroll ? $payroll->bonus : 0;
            $kasbon = $payroll ? $payroll->deduction : 0;

            $totalKotor = $komisiShift + $allowance + $motret + $lembur + $bonus;
            $totalBersih = $totalKotor - $kasbon;
            $totalSistem += $totalBersih;

            $salaryData[] = [
                'user' => $user,
                'total_shifts' => $totalShifts,
                'komisi_shift' => $komisiShift,
                'allowance' => $allowance,
                'payroll' => $payroll,
                'motret' => $motret,
                'lembur' => $lembur,
                'bonus' => $bonus,
                'kasbon' => $kasbon,
                'total_bersih' => $totalBersih,
            ];
        }
        
        return compact('month', 'salaryData', 'totalSistem');
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'period' => 'required|date_format:Y-m',
            'photographer_fee' => 'nullable|numeric|min:0',
            'overtime_fee' => 'nullable|numeric|min:0',
            'bonus' => 'nullable|numeric|min:0',
            'deduction' => 'nullable|numeric|min:0',
            'photographer_fee_note' => 'nullable|string',
            'overtime_fee_note' => 'nullable|string',
            'bonus_note' => 'nullable|string',
            'deduction_note' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        Payroll::updateOrCreate(
            ['user_id' => $request->user_id, 'period' => $request->period],
            [
                'photographer_fee' => $request->photographer_fee ?? 0,
                'overtime_fee' => $request->overtime_fee ?? 0,
                'bonus' => $request->bonus ?? 0,
                'deduction' => $request->deduction ?? 0,
                'photographer_fee_note' => $request->photographer_fee_note,
                'overtime_fee_note' => $request->overtime_fee_note,
                'bonus_note' => $request->bonus_note,
                'deduction_note' => $request->deduction_note,
                'notes' => $request->notes,
            ]
        );

        return back()->with('success', 'Data tambahan gaji berhasil disimpan!');
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
}
