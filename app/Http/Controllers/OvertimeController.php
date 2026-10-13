<?php

namespace App\Http\Controllers;

use App\Models\OvertimeRequest;
use App\Models\PayrollEntry;
use App\Models\ScheduleLocation;
use Illuminate\Http\Request;

class OvertimeController extends Controller
{
    public function index(Request $request)
    {
        $month = $request->input('month', now()->format('Y-m'));
        $periodStart = \Carbon\Carbon::parse($month . '-01')->startOfMonth();
        $periodEnd = $periodStart->copy()->endOfMonth();

        $query = OvertimeRequest::with(['user', 'location', 'payrollEntry'])
            ->whereBetween('date', [$periodStart->format('Y-m-d'), $periodEnd->format('Y-m-d')]);

        if (in_array(auth()->user()->role, ['crew', 'kasir'])) {
            $query->where('user_id', auth()->id());
        }

        if ($request->filled('location_id')) {
            $query->where('schedule_location_id', $request->location_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $requests = $query->orderByDesc('date')->orderBy('created_at')->get();

        // Absen keluar sebulan diambil sekali, lalu dipasangkan ke pengajuannya supaya
        // Rekap Lembur dapat menampilkan GPS, jarak, dan foto tanpa query per baris.
        $checkouts = \App\Models\Attendance::with('user')
            ->where('type', 'out')
            ->whereBetween('date', [$periodStart->format('Y-m-d'), $periodEnd->format('Y-m-d')])
            ->orderBy('created_at')
            ->get()
            ->keyBy(fn ($row) => $row->user_id . '-' . $row->schedule_location_id . '-' . $row->date->toDateString());

        foreach ($requests as $overtime) {
            $overtime->setRelation(
                'checkoutAttendance',
                $checkouts->get($overtime->user_id . '-' . $overtime->schedule_location_id . '-' . $overtime->date->toDateString())
            );
        }

        $summary = [
            'pending' => $requests->where('status', OvertimeRequest::STATUS_PENDING),
            'approved' => $requests->where('status', OvertimeRequest::STATUS_APPROVED),
            'rejected' => $requests->where('status', OvertimeRequest::STATUS_REJECTED),
        ];

        return view('schedules.overtimes.index', [
            'month' => $month,
            'requests' => $requests,
            'summary' => $summary,
            'locations' => ScheduleLocation::active()->get(),
            'users' => \App\Models\User::where('is_active', true)->orderBy('name')->get(),
            'ratePerHour' => \App\Services\AttendanceRuleService::overtimeRate(),
        ]);
    }

    /**
     * Owner mengisi lembur manual, misalnya untuk crew yang absen keluar tercatat
     * tepat waktu tetapi pekerjaannya berlanjut.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'user_id' => 'required|exists:users,id',
            'schedule_location_id' => 'required|exists:schedule_locations,id',
            'date' => 'required|date',
            'minutes' => 'required|integer|min:1|max:1440',
            'amount' => 'nullable|integer|min:0',
            'note' => 'required|string|max:500',
        ], [], ['note' => 'catatan lembur']);

        $rate = \App\Services\AttendanceRuleService::overtimeRate();
        $minutes = (int) $data['minutes'];
        $amount = isset($data['amount']) ? (int) $data['amount'] : OvertimeRequest::amountFor($minutes, $rate);

        $user = \App\Models\User::findOrFail($data['user_id']);
        $location = \App\Models\ScheduleLocation::findOrFail($data['schedule_location_id']);

        $window = \App\Services\AttendanceRuleService::sessionWindow(
            \App\Services\AttendanceRuleService::assignmentsFor($user, $location, $data['date']),
            $data['date']
        );

        $overtime = OvertimeRequest::updateOrCreate(
            [
                'user_id' => $user->id,
                'date' => $data['date'],
                'schedule_location_id' => $location->id,
            ],
            [
                'window_end_at' => $window['out'][1] ?? \Carbon\Carbon::parse($data['date'])->endOfDay(),
                'check_out_at' => $window['out'][1] ?? \Carbon\Carbon::parse($data['date'])->endOfDay(),
                'minutes' => $minutes,
                'rate_per_hour' => $rate,
                'amount' => $amount,
                'note' => $data['note'],
                'status' => OvertimeRequest::STATUS_PENDING,
                'source' => OvertimeRequest::SOURCE_MANUAL,
                'reviewed_by' => null,
                'reviewed_at' => null,
                'payroll_entry_id' => null,
            ]
        );

        return back()->with('success', 'Lembur ' . $user->name . ' (' . $overtime->durationLabel()
            . ', Rp ' . number_format($amount, 0, ',', '.') . ') tercatat dan menunggu acc.');
    }

    public function approve(Request $request, OvertimeRequest $overtime)
    {
        $payload = $request->validate([
            'amount' => 'nullable|integer|min:0',
        ]);

        if ($overtime->status !== OvertimeRequest::STATUS_PENDING) {
            return back()->with('error', 'Pengajuan lembur ini sudah selesai diproses.');
        }

        $amount = isset($payload['amount']) ? (int) $payload['amount'] : $overtime->amount;

        $entry = PayrollEntry::updateOrCreate(
            ['source' => 'overtime_auto', 'reference_id' => $overtime->id],
            [
                'user_id' => $overtime->user_id,
                'date' => $overtime->date->toDateString(),
                'component' => 'overtime_fee',
                'amount' => $amount,
                'note' => 'Lembur otomatis ' . $overtime->durationLabel()
                    . ' di ' . ($overtime->location->name ?? '-')
                    . ($overtime->note ? ' — ' . $overtime->note : ''),
            ]
        );

        $overtime->update([
            'amount' => $amount,
            'status' => OvertimeRequest::STATUS_APPROVED,
            'reviewed_by' => auth()->user()->name,
            'reviewed_at' => now(),
            'payroll_entry_id' => $entry->id,
        ]);

        return back()->with('success', 'Lembur ' . $overtime->user->name . ' di-acc dan masuk komponen gaji.');
    }

    public function reject(OvertimeRequest $overtime)
    {
        if ($overtime->status !== OvertimeRequest::STATUS_PENDING) {
            return back()->with('error', 'Pengajuan lembur ini sudah selesai diproses.');
        }

        $overtime->update([
            'status' => OvertimeRequest::STATUS_REJECTED,
            'reviewed_by' => auth()->user()->name,
            'reviewed_at' => now(),
            'payroll_entry_id' => null,
        ]);

        return back()->with('success', 'Pengajuan lembur ditolak.');
    }

    /**
     * Mengoreksi pengajuan lembur yang sudah selesai diproses. Komponen gaji yang
     * lahir dari pengajuan ini ikut diperbarui, dihapus, atau dibuat ulang supaya
     * nominal di payroll selalu sama dengan lembur yang disetujui.
     */
    public function update(Request $request, OvertimeRequest $overtime)
    {
        $payload = $request->validate([
            'minutes' => 'required|integer|min:1|max:1440',
            'amount' => 'nullable|integer|min:0',
            'note' => 'nullable|string|max:500',
            'status' => 'required|in:' . OvertimeRequest::STATUS_PENDING . ',' . OvertimeRequest::STATUS_APPROVED . ',' . OvertimeRequest::STATUS_REJECTED,
        ], [], ['minutes' => 'durasi lembur']);

        $minutes = (int) $payload['minutes'];
        $rate = (int) ($overtime->rate_per_hour ?: \App\Services\AttendanceRuleService::overtimeRate());
        $amount = isset($payload['amount']) ? (int) $payload['amount'] : OvertimeRequest::amountFor($minutes, $rate);
        $status = $payload['status'];
        $note = $request->input('note');

        $overtime->update([
            'minutes' => $minutes,
            'rate_per_hour' => $rate,
            'amount' => $amount,
            'note' => $note ?: null,
            // Setelah dikoreksi owner, sistem tidak boleh menghitungnya ulang.
            'source' => OvertimeRequest::SOURCE_MANUAL,
        ]);

        $entry = PayrollEntry::where('source', 'overtime_auto')->where('reference_id', $overtime->id)->first();

        if ($status === OvertimeRequest::STATUS_APPROVED) {
            $entry = PayrollEntry::updateOrCreate(
                ['source' => 'overtime_auto', 'reference_id' => $overtime->id],
                [
                    'user_id' => $overtime->user_id,
                    'date' => $overtime->date->toDateString(),
                    'component' => 'overtime_fee',
                    'amount' => $amount,
                    'note' => 'Lembur otomatis ' . $overtime->durationLabel()
                        . ' di ' . ($overtime->location->name ?? '-')
                        . ($note ? ' — ' . $note : ''),
                ]
            );
        } elseif ($entry) {
            $entry->delete();
            $entry = null;
        }

        $overtime->update([
            'status' => $status,
            'reviewed_by' => auth()->user()->name,
            'reviewed_at' => now(),
            'payroll_entry_id' => $entry?->id,
        ]);

        return back()->with('success', 'Lembur ' . $overtime->user->name . ' diperbarui ('
            . $overtime->durationLabel() . ', Rp ' . number_format($amount, 0, ',', '.')
            . ', ' . OvertimeRequest::statusLabel($status) . ').');
    }
}
