<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\OvertimeRequest;
use App\Models\ScheduleAssignment;
use App\Models\ScheduleLocation;
use App\Models\User;
use App\Services\AttendanceRuleService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        AttendanceRuleService::enforceRecentRules();

        $query = Attendance::with(['user', 'location', 'shift'])
            ->orderByDesc('date')
            ->orderByDesc('created_at');

        if (in_array(auth()->user()->role, ['crew', 'kasir'])) {
            $query->where('user_id', auth()->id());
        }

        if ($request->filled('date')) {
            $query->whereDate('date', $request->date);
        }

        if ($request->filled('location_id')) {
            $query->where('schedule_location_id', $request->location_id);
        }

        if ($request->filled('violation')) {
            $violation = $request->violation;
            if (array_key_exists($violation, AttendanceRuleService::labels())) {
                $query->whereJsonContainsKey('violations->' . $violation);
            }
        }

        $perPage = in_array((int) $request->input('per_page'), [10, 25, 50, 100]) ? (int) $request->input('per_page') : 10;
        $attendances = $query->paginate($perPage)->withQueryString();
        AttendanceRuleService::attachOvertime($attendances->getCollection());

        // Absen masuk yang belum ditutup, agar owner dapat melengkapinya.
        $openIds = $attendances->filter(fn (Attendance $row) => $row->type === 'in')
            ->filter(fn (Attendance $row) => !AttendanceRuleService::closingOut($row))
            ->pluck('id')
            ->all();

        // Absen keluar yang terjadi tanpa absen masuk: owner yang mengisi jam masuknya.
        $orphanOutIds = $attendances->filter(fn (Attendance $row) => $row->type === 'out')
            ->filter(fn (Attendance $row) => !AttendanceRuleService::openingIn($row))
            ->pluck('id')
            ->all();

        $locations = ScheduleLocation::active()->get();
        $users = User::where('is_active', true)->orderBy('name')->get();
        $overtimeRate = AttendanceRuleService::overtimeRate();
        $canManage = $this->isOwner();

        return view('schedules.attendances.index', compact(
            'attendances', 'locations', 'openIds', 'orphanOutIds', 'users', 'overtimeRate', 'canManage', 'perPage'
        ));
    }

    public function create()
    {
        $user = auth()->user();
        $today = now()->toDateString();
        $isCrew = in_array($user->role, ['crew', 'kasir']);

        if ($isCrew) {
            $assignedLocationIds = ScheduleAssignment::with('shift.location')
                ->where('user_id', $user->id)
                ->where('date', $today)
                ->get()
                ->pluck('shift.schedule_location_id')
                ->filter()
                ->unique();

            // Absen keluar setelah tengah malam tetap terbuka untuk shift kemarin.
            $attendedLocationIds = Attendance::where('user_id', $user->id)
                ->whereDate('date', '>=', now()->subDay()->toDateString())
                ->pluck('schedule_location_id')
                ->unique();

            $locationIds = $assignedLocationIds->merge($attendedLocationIds)->unique();

            if ($locationIds->isEmpty()) {
                return redirect()->route('schedules.attendances.index')
                    ->with('error', 'Anda tidak memiliki jadwal shift hari ini. Absensi hanya dapat dilakukan jika Anda memiliki shift aktif.');
            }

            $locations = ScheduleLocation::whereIn('id', $locationIds)->get();
        } else {
            // Admin dapat memilih lokasi mana pun untuk pengujian.
            $locations = ScheduleLocation::active()->get();
        }

        $sessions = $locations->mapWithKeys(function ($location) use ($user, $today, $isCrew) {
            $open = AttendanceRuleService::openSession($user, $location);
            $date = $open ? $open->date->toDateString() : $today;

            $assignments = AttendanceRuleService::assignmentsFor($user, $location, $date);

            // Admin yang menguji absen tidak punya jadwal sendiri, jadi pakai jadwal crew
            // di lokasi itu agar jendela waktu tetap terlihat.
            if ($assignments->isEmpty() && !$isCrew) {
                $assignments = ScheduleAssignment::with('shift')
                    ->where('date', $date)
                    ->whereHas('shift', fn ($q) => $q->where('schedule_location_id', $location->id))
                    ->get();
            }

            $window = AttendanceRuleService::sessionWindow($assignments, $date);
            $stale = AttendanceRuleService::staleOpenSession($user, $location);
            $batas = AttendanceRuleService::missingDeadline($window, $location, $date);

            return [$location->id => [
                'date' => $date,
                'is_carryover' => $date !== $today,
                'has_open_in' => (bool) $open,
                'blocked' => (bool) $stale,
                'blocked_id' => $stale?->id,
                'blocked_note' => $stale?->notes,
                'in_window' => AttendanceRuleService::windowLabel($window, 'in', $date),
                'out_window' => AttendanceRuleService::windowLabel($window, 'out', $date),
                'out_cutoff' => $batas?->format('d/m Y H:i'),
                'in_from' => $window['in'][0]?->toIso8601String(),
                'in_to' => $window['in'][1]?->toIso8601String(),
                'out_from' => $window['out'][0]?->toIso8601String(),
                'out_to' => $window['out'][1]?->toIso8601String(),
                'out_deadline' => $window['out'][1]?->toIso8601String(),
            ]];
        });

        $overtimeRate = AttendanceRuleService::overtimeRate();

        return view('schedules.attendances.create', compact('locations', 'sessions', 'isCrew', 'overtimeRate'));
    }

    public function destroy(Attendance $attendance)
    {
        if (!in_array(auth()->user()->role, ['superadmin', 'owner'])) {
            return back()->with('error', 'Anda tidak memiliki akses untuk menghapus data absensi.');
        }

        if ($attendance->photo_path) {
            Storage::disk('public')->delete($attendance->photo_path);
        }

        // Absen keluar yang dihapus tidak boleh menyisakan pengajuan lembur yang belum di-acc.
        if ($attendance->type === 'out') {
            OvertimeRequest::where('user_id', $attendance->user_id)
                ->where('schedule_location_id', $attendance->schedule_location_id)
                ->whereDate('date', $attendance->date->toDateString())
                ->where('status', OvertimeRequest::STATUS_PENDING)
                ->delete();
        }

        $attendance->delete();

        return back()->with('success', 'Data absensi berhasil dihapus.');
    }

    public function store(Request $request)
    {
        $request->validate([
            'schedule_location_id' => 'required|exists:schedule_locations,id',
            'type' => 'required|in:in,out',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'photo' => 'required|string', // base64 webp
            'note' => 'nullable|string|max:500',
            'overtime_note' => 'nullable|string|max:500',
        ]);

        $user = $request->user();
        $location = ScheduleLocation::findOrFail($request->schedule_location_id);
        $now = now();
        $open = AttendanceRuleService::openSession($user, $location);

        // Sesi kemarin yang belum ditutup sampai lewat batas toleransi menjadi urusan
        // owner, bukan ditutup sendiri oleh crew di hari berikutnya.
        $stale = AttendanceRuleService::staleOpenSession($user, $location, $now);

        if ($stale) {
            $tanggalSesi = $stale->date->toDateString();
            $batas = AttendanceRuleService::missingDeadline(
                AttendanceRuleService::sessionWindow(
                    AttendanceRuleService::assignmentsFor($user, $location, $tanggalSesi),
                    $tanggalSesi
                ),
                $location,
                $tanggalSesi
            );

            return back()->with('error', 'Absensi Anda di ' . $location->name . ' untuk tanggal '
                . $stale->date->translatedFormat('d M Y') . ' belum absen keluar'
                . ($batas ? ' dan sudah lewat batas absen keluar (' . $batas->translatedFormat('d M Y H:i') . ')' : '')
                . ', sehingga tercatat sebagai lupa absen keluar. Absensi berikutnya dikunci sampai owner menutup sesi itu.');
        }

        if ($request->type === 'in') {
            $alreadyInToday = Attendance::where('user_id', $user->id)
                ->where('schedule_location_id', $location->id)
                ->where('type', 'in')
                ->whereDate('date', $now->toDateString())
                ->exists();

            if ($open || $alreadyInToday) {
                return back()->with('error', 'Anda sudah absen masuk di ' . $location->name . ' dan belum absen keluar.');
            }

            $date = $now->toDateString();
        } else {
            $date = $open ? $open->date->toDateString() : $now->toDateString();

            // Absen keluar tanpa absen masuk dibiarkan (crew hanya lupa memotret saat
            // datang), asalkan sesi hari itu belum pernah ditutup.
            if (!$open && Attendance::where('user_id', $user->id)
                ->where('schedule_location_id', $location->id)
                ->where('type', 'out')
                ->whereDate('date', $date)
                ->exists()) {
                return back()->with('error', 'Absensi Anda di ' . $location->name . ' untuk tanggal '
                    . Carbon::parse($date)->translatedFormat('d M Y') . ' sudah lengkap.');
            }
        }

        $assignments = AttendanceRuleService::assignmentsFor($user, $location, $date);

        if (in_array($user->role, ['crew', 'kasir']) && $assignments->isEmpty()) {
            return back()->with('error', 'Anda tidak memiliki jadwal shift pada ' . $date . ' di ' . $location->name . '.');
        }

        $window = AttendanceRuleService::sessionWindow($assignments, $date);
        $shift = $window['shifts'][$request->type] ?? $assignments->first()?->shift;

        [$distance, $status] = $this->locate($request, $location);

        $violations = AttendanceRuleService::timeViolations($request->type, $now, $window);

        if ($status === 'out_of_radius') {
            $violations[AttendanceRuleService::OUT_OF_RADIUS] = $distance;
        }

        $tanpaMasuk = $request->type === 'out' && !$open;

        if ($tanpaMasuk) {
            $violations[AttendanceRuleService::MISSING_CHECK_IN] = $window['in'][1]
                ? max(0, (int) $window['in'][1]->diffInMinutes($now, false))
                : 0;
        }

        // null = absensi ini tidak dinilai (jendela belum diatur / penegakan dimatikan),
        // array kosong = dinilai dan bersih.
        [$windowFrom, $windowTo] = $window[$request->type];
        $dinilai = ($windowFrom && $windowTo && AttendanceRuleService::enforcementEnabled())
            || $status === 'out_of_radius' || $tanpaMasuk;

        Attendance::create([
            'user_id' => $user->id,            'schedule_location_id' => $location->id,
            'schedule_shift_id' => $shift?->id ?? $open?->schedule_shift_id,
            'type' => $request->type,
            'date' => $date,
            'photo_path' => $this->savePhoto($request, $user),
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'distance' => $distance,
            'status' => $status,
            'notes' => $request->input('note') ?: null,
            // Array kosong berarti dinilai dan bersih; null berarti tidak dinilai
            // (jendela waktu belum diatur untuk shift ini, atau penegakan dimatikan).
            'violations' => $dinilai ? $violations : null,
        ]);

        $messages = [$status === 'in_radius'
            ? 'Absen ' . ($request->type === 'in' ? 'masuk' : 'keluar') . ' berhasil dicatat!'
            : 'Absen tercatat, tapi Anda terdeteksi di luar radius lokasi.'];

        foreach ($violations as $code => $value) {
            $messages[] = $this->violationMessage($code, $value, $window);
        }

        if ($request->type === 'out') {
            $overtime = $this->recordOvertime($request, $user, $location, $date, $now, $window);

            if ($overtime) {
                $messages[] = 'Terhitung lembur ' . $overtime->durationLabel() . ' setelah batas jam '
                    . $window['out'][1]->format('H:i') . ', senilai Rp '
                    . number_format($overtime->amount, 0, ',', '.') . '. Menunggu acc owner.';
            }
        }

        return redirect()->route('schedules.attendances.index')->with('success', implode(' ', $messages));
    }

    /**
     * Crew menulis alasan atas keterlambatan atau kelupaan absennya sendiri,
     * termasuk untuk sesi yang sudah dikunci karena lupa absen keluar.
     */
    public function saveNote(Request $request, Attendance $attendance)
    {
        $data = $request->validate([
            'notes' => 'required|string|max:500',
        ], [], ['notes' => 'alasan']);

        if ($attendance->user_id !== $request->user()->id && !$this->isOwner()) {
            return back()->with('error', 'Anda hanya dapat mencatat alasan untuk absensi Anda sendiri.');
        }

        $attendance->update(['notes' => $data['notes']]);

        return back()->with('success', 'Alasan Anda tercatat dan akan dilihat owner bersama absensi ini.');
    }

    /**
     * Owner mengoreksi satu baris absensi: tanggal, jam, keterangan, dan statusnya.
     * Status 'auto' menilai ulang jam baru terhadap jendela absen shift.
     */
    public function update(Request $request, Attendance $attendance)
    {
        if (!$this->isOwner()) {
            return back()->with('error', 'Anda tidak memiliki akses untuk mengubah data absensi.');
        }

        $data = $request->validate([
            'date' => 'required|date',
            'time' => 'required|date_format:H:i',
            'notes' => 'nullable|string|max:500',
            'status' => 'required|in:auto,clean,unjudged,lembur,' . implode(',', array_keys(AttendanceRuleService::labels())),
        ], [], ['status' => 'status absensi']);

        $moment = Carbon::parse($data['date'] . ' ' . $data['time']);
        $assignments = AttendanceRuleService::assignmentsFor($attendance->user, $attendance->location, $data['date']);
        $window = AttendanceRuleService::sessionWindow($assignments, $data['date']);

        if ($data['status'] === 'lembur' && $attendance->type !== 'out') {
            return back()->with('error', 'Status Lembur hanya berlaku untuk absen keluar.');
        }

        $radius = (array) ($attendance->violations ?? []);
        $radius = $radius[AttendanceRuleService::OUT_OF_RADIUS] ?? null;

        $violations = match ($data['status']) {
            'clean', 'lembur' => [],
            'unjudged' => null,
            'auto' => AttendanceRuleService::timeViolations($attendance->type, $moment, $window),
            default => [$data['status'] => $this->violationValue($data['status'], $moment, $window, $attendance)],
        };

        if ($violations !== null && $radius !== null) {
            $violations[AttendanceRuleService::OUT_OF_RADIUS] = $radius;
        }

        $attendance->update([
            'date' => $data['date'],
            'notes' => $request->input('notes') ?: null,
            'violations' => $violations,
            'corrected_by' => auth()->user()->name,
        ]);

        // Jam absen ikut dikoreksi tanpa mengubah kapan barisnya dibuat secara sistem.
        $attendance->created_at = $moment;
        $attendance->save();

        $messages = ['Absensi ' . ($attendance->user->name ?? 'crew') . ' diperbarui.'];

        if ($data['status'] === 'lembur') {
            $minutes = AttendanceRuleService::overtimeMinutes($moment, $window['out'][1]);

            if ($minutes <= 0) {
                return back()->with('error', 'Jam absen keluar ' . $moment->format('H:i')
                    . ' belum lewat batas jendela' . ($window['out'][1] ? ' (' . $window['out'][1]->format('H:i') . ')' : '')
                    . ', jadi tidak bisa dinilai lembur. Atur dulu jendela absen keluarnya di Master Shift.');
            }

            $rate = AttendanceRuleService::overtimeRate();
            $overtime = OvertimeRequest::updateOrCreate(
                [
                    'user_id' => $attendance->user_id,
                    'date' => $data['date'],
                    'schedule_location_id' => $attendance->schedule_location_id,
                ],
                [
                    'window_end_at' => $window['out'][1],
                    'check_out_at' => $moment,
                    'minutes' => $minutes,
                    'rate_per_hour' => $rate,
                    'amount' => OvertimeRequest::amountFor($minutes, $rate),
                    'note' => $request->input('notes') ?: 'Lembur tercatat dari absensi ' . $moment->format('H:i'),
                    'status' => OvertimeRequest::STATUS_PENDING,
                    'source' => OvertimeRequest::SOURCE_AUTO,
                    'reviewed_by' => null,
                    'reviewed_at' => null,
                    'payroll_entry_id' => null,
                ]
            );

            $messages[] = 'Lembur ' . $overtime->durationLabel() . ' senilai Rp '
                . number_format($overtime->amount, 0, ',', '.') . ' langsung masuk ke Rekap Lembur.';
        }

        return back()->with('success', implode(' ', $messages));
    }

    /**
     * Menutup sesi yang absen masuknya tidak pernah ditutup. Owner memilih apakah
     * crew memang lupa absen keluar, atau sesi itu sebenarnya lembur.
     */
    public function closeSession(Request $request, Attendance $attendance)
    {
        if (!$this->isOwner()) {
            return back()->with('error', 'Anda tidak memiliki akses untuk mengubah data absensi.');
        }

        if ($attendance->type !== 'in') {
            return back()->with('error', 'Hanya absen masuk yang dapat ditutup.');
        }

        $data = $request->validate([
            'action' => 'required|in:missing,overtime',
            'check_out_date' => 'required|date',
            'check_out_time' => 'required|date_format:H:i',
            'minutes' => 'nullable|integer|min:1|max:1440',
            'amount' => 'nullable|integer|min:0',
            'note' => 'nullable|string|max:500',
        ]);

        $date = $attendance->date->toDateString();

        $hasOut = AttendanceRuleService::closingOut($attendance);

        if ($hasOut) {
            return back()->with('error', 'Sesi ini sudah memiliki absen keluar.');
        }

        $assignments = AttendanceRuleService::assignmentsFor($attendance->user, $attendance->location, $date);
        $window = AttendanceRuleService::sessionWindow($assignments, $date);
        $moment = Carbon::parse($data['check_out_date'] . ' ' . $data['check_out_time']);
        $minutesLate = $window['out'][1] ? max(0, (int) $window['out'][1]->diffInMinutes($moment, false)) : 0;

        $outViolations = AttendanceRuleService::timeViolations('out', $moment, $window);
        $isOvertime = $data['action'] === 'overtime';

        if ($isOvertime) {
            // Lembur sungguhan, jadi flag lupa absen keluar pada absen masuk dihapus.
            $violations = $attendance->violations ?? [];
            unset($violations[AttendanceRuleService::MISSING_CHECK_OUT]);
            $attendance->update(['violations' => $violations]);
        } else {
            $inViolations = (array) ($attendance->violations ?? []);
            $outViolations[AttendanceRuleService::MISSING_CHECK_OUT] =
                $inViolations[AttendanceRuleService::MISSING_CHECK_OUT] ?? $minutesLate;
        }

        $out = new Attendance([
            'user_id' => $attendance->user_id,
            'schedule_location_id' => $attendance->schedule_location_id,
            'schedule_shift_id' => $window['shifts']['out']?->id ?? $attendance->schedule_shift_id,
            'type' => 'out',
            // Tanggal operasional shift asalnya, bukan tanggal jam dinding.
            'date' => $date,
            'latitude' => $attendance->latitude,
            'longitude' => $attendance->longitude,
            'distance' => $attendance->distance,
            'status' => $attendance->status,
            'source' => Attendance::SOURCE_MANUAL,
            'corrected_by' => auth()->user()->name,
            'violations' => $outViolations,
            'notes' => $request->input('note') ?: ($isOvertime ? 'Dikonversi menjadi lembur oleh owner.' : 'Absen keluar dilengkapi owner (crew lupa absen keluar).'),
        ]);
        $out->created_at = $moment;
        $out->save();

        $messages = ['Absen keluar ' . ($attendance->user->name ?? 'crew') . ' pada ' . $moment->translatedFormat('d M Y') . ' berhasil ditutup.'];

        if ($isOvertime) {
            $ditentukanOwner = (int) $request->input('minutes') > 0;
            $minutes = $ditentukanOwner
                ? (int) $request->input('minutes')
                : AttendanceRuleService::overtimeMinutes($moment, $window['out'][1]);

            if ($minutes <= 0) {
                return back()->with('error', $window['out'][1]
                    ? 'Jam keluar ' . $moment->format('H:i') . ' tidak lewat batas jendela absen keluar ('
                        . $window['out'][1]->format('H:i') . '), jadi tidak ada durasi lembur. Isi durasinya manual bila tetap mau dihitung.'
                    : 'Jendela absen keluar sesi ini belum diatur di Master Shift, jadi durasi lemburnya harus diisi manual.');
            }

            $rate = AttendanceRuleService::overtimeRate();
            $amount = isset($data['amount']) ? (int) $data['amount'] : OvertimeRequest::amountFor($minutes, $rate);

            OvertimeRequest::updateOrCreate(
                [
                    'user_id' => $attendance->user_id,
                    'date' => $date,
                    'schedule_location_id' => $attendance->schedule_location_id,
                ],
                [
                    'window_end_at' => $window['out'][1] ?? $moment,
                    'check_out_at' => $moment,
                    'minutes' => $minutes,
                    'rate_per_hour' => $rate,
                    'amount' => $amount,
                    'note' => $request->input('note') ?: 'Konversi lupa absen keluar menjadi lembur.',
                    'status' => OvertimeRequest::STATUS_PENDING,
                    // Durasi yang diketik owner tidak boleh dihitung ulang oleh sistem.
                    'source' => $ditentukanOwner ? OvertimeRequest::SOURCE_MANUAL : OvertimeRequest::SOURCE_AUTO,
                    'reviewed_by' => null,
                    'reviewed_at' => null,
                    'payroll_entry_id' => null,
                ]
            );

            $messages[] = 'Lembur ' . intdiv($minutes, 60) . ' jam ' . ($minutes % 60) . ' menit senilai Rp '
                . number_format($amount, 0, ',', '.') . ' diajukan, menunggu acc di Rekap Lembur.';
        }

        return back()->with('success', implode(' ', $messages));
    }

    /**
     * Melengkapi absen masuk yang tidak pernah diabsen. Jamnya diisi owner, jadi
     * statusnya otomatis "terlambat absen masuk" dengan durasi sesuai jendela shift,
     * dan flag lupa absen masuk pada absen keluarnya hilang.
     */
    public function closeCheckIn(Request $request, Attendance $attendance)
    {
        if (!$this->isOwner()) {
            return back()->with('error', 'Anda tidak memiliki akses untuk mengubah data absensi.');
        }

        if ($attendance->type !== 'out') {
            return back()->with('error', 'Absen masuk hanya bisa dilengkapi pada baris absen keluar.');
        }

        $data = $request->validate([
            'check_in_date' => 'required|date',
            'check_in_time' => 'required|date_format:H:i',
            'note' => 'nullable|string|max:500',
        ], [], ['check_in_date' => 'tanggal masuk', 'check_in_time' => 'jam masuk']);

        if (AttendanceRuleService::openingIn($attendance)) {
            return back()->with('error', 'Sesi ini sudah punya absen masuk.');
        }

        $moment = Carbon::parse($data['check_in_date'] . ' ' . $data['check_in_time']);

        if ($moment->greaterThanOrEqualTo($attendance->created_at)) {
            return back()->with('error', 'Jam masuk ' . $moment->format('H:i')
                . ' tidak boleh sama atau lebih akhir dari absen keluarnya ('
                . $attendance->created_at->format('H:i') . ').');
        }

        $date = $attendance->date->toDateString();
        $window = AttendanceRuleService::sessionWindow(
            AttendanceRuleService::assignmentsFor($attendance->user, $attendance->location, $date),
            $date
        );

        $violations = AttendanceRuleService::timeViolations('in', $moment, $window);

        if ($attendance->violations && array_key_exists(AttendanceRuleService::OUT_OF_RADIUS, (array) $attendance->violations)) {
            $violations[AttendanceRuleService::OUT_OF_RADIUS] = $attendance->violations[AttendanceRuleService::OUT_OF_RADIUS];
        }

        $in = new Attendance([
            'user_id' => $attendance->user_id,
            'schedule_location_id' => $attendance->schedule_location_id,
            'schedule_shift_id' => $window['shifts']['in']?->id ?? $attendance->schedule_shift_id,
            'type' => 'in',
            'date' => $date,
            'latitude' => $attendance->latitude,
            'longitude' => $attendance->longitude,
            'distance' => $attendance->distance,
            'status' => $attendance->status,
            'source' => Attendance::SOURCE_MANUAL,
            'corrected_by' => auth()->user()->name,
            'violations' => $violations,
            'notes' => $request->input('note') ?: 'Absen masuk dilengkapi owner (crew lupa absen masuk).',
        ]);
        $in->created_at = $moment;
        $in->save();

        $keluar = (array) ($attendance->violations ?? []);
        unset($keluar[AttendanceRuleService::MISSING_CHECK_IN]);
        $attendance->update(['violations' => $keluar]);

        $terlambat = $violations[AttendanceRuleService::LATE_CHECK_IN] ?? 0;

        return back()->with('success', 'Absen masuk ' . ($attendance->user->name ?? 'crew') . ' pada '
            . $moment->translatedFormat('d M Y') . ' ' . $moment->format('H:i') . ' dilengkapi'
            . ($terlambat ? ', tercatat terlambat ' . $terlambat . ' menit.' : '.'));
    }

    /**
     * Mengisi absensi otomatis dari jadwal untuk periode yang dipilih, misalnya
     * sejak tanggal 1 bulan ini. Baris yang sudah ada tidak pernah ditimpa.
     */
    public function autoFill(Request $request)
    {
        if (!$this->isOwner()) {
            return back()->with('error', 'Anda tidak memiliki akses untuk mengisi absensi otomatis.');
        }

        $data = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'location_id' => 'nullable|exists:schedule_locations,id',
            'user_id' => 'nullable|exists:users,id',
        ]);

        $assignments = ScheduleAssignment::with(['shift.location', 'user'])
            ->whereBetween('date', [$data['start_date'], $data['end_date']])
            ->when($data['location_id'] ?? null, fn ($q) => $q->whereHas('shift', fn ($s) => $s->where('schedule_location_id', $data['location_id'])))
            ->when($data['user_id'] ?? null, fn ($q) => $q->where('user_id', $data['user_id']))
            ->get();

        $created = $skipped = $noWindow = $belumSelesai = $menunggu = $tanpaMasuk = 0;

        foreach ($assignments->groupBy(fn ($a) => $a->user_id . '-' . $a->shift?->schedule_location_id . '-' . $a->date->toDateString()) as $group) {
            $first = $group->first();
            $location = $first->shift?->location;

            if (!$location) {
                continue;
            }

            $date = $first->date->toDateString();

            $masuk = Attendance::where('user_id', $first->user_id)
                ->where('schedule_location_id', $location->id)
                ->where('type', 'in')
                ->whereDate('date', $date)
                ->get();

            $sudahKeluar = Attendance::where('user_id', $first->user_id)
                ->where('schedule_location_id', $location->id)
                ->where('type', 'out')
                ->whereDate('date', $date)
                ->exists();

            // Sesi yang sedang menunggu keputusan owner (lupa absen keluar) tidak
            // boleh dilengkapi diam-diam, nanti penilaian owner hilang.
            if ($masuk->contains(fn (Attendance $row) => array_key_exists(
                AttendanceRuleService::MISSING_CHECK_OUT, (array) ($row->violations ?? [])
            ))) {
                $menunggu++;
                continue;
            }

            // Absen keluar tanpa absen masuk: hanya owner yang tahu crew datang
            // jam berapa, jadi tidak diisi otomatis (pakai aksi Isi Absen Masuk).
            if ($masuk->isEmpty() && $sudahKeluar) {
                $tanpaMasuk++;
                continue;
            }

            if ($masuk->isNotEmpty() && $sudahKeluar) {
                $skipped++;
                continue;
            }

            $window = AttendanceRuleService::sessionWindow($group, $date);

            if (!$window['in'][1] || !$window['out'][1]) {
                $noWindow++;
                continue;
            }

            // Operasionalnya belum selesai, jadi tidak boleh dikarang lebih dulu.
            if ($window['out'][1]->greaterThan(now())) {
                $belumSelesai++;
                continue;
            }

            $shiftFor = fn ($type) => $window['shifts'][$type] ?? $first->shift;

            // Hanya sisi yang belum diabsen yang diisi, supaya absen crew yang asli
            // tidak pernah ditimpa maupun diduplikasi.
            $missing = array_filter(['in' => $masuk->isEmpty(), 'out' => !$sudahKeluar]);

            foreach ($missing as $type => $unused) {
                $moment = $window[$type][1];
                $row = new Attendance([
                    'user_id' => $first->user_id,
                    'schedule_location_id' => $location->id,
                    'schedule_shift_id' => $shiftFor($type)?->id,
                    'type' => $type,
                    'date' => $date,
                    'status' => 'in_radius',
                    'source' => Attendance::SOURCE_AUTO,
                    'corrected_by' => auth()->user()->name,
                    // Diisi dari jendela absen sehingga statusnya tepat waktu.
                    'violations' => [],
                    'notes' => 'Diisi otomatis dari jadwal',
                ]);
                $row->created_at = $moment->copy()->addSeconds($type === 'out' ? 1 : 0);
                $row->save();
            }

            $created++;
        }

        $messages = [$created . ' sesi absensi diisi otomatis'];
        $messages[] = $skipped . ' sesi dilewati karena sudah ada data';
        if ($menunggu > 0) {
            $messages[] = $menunggu . ' sesi dilewati karena masih menunggu keputusan Anda (lupa absen keluar)';
        }
        if ($tanpaMasuk > 0) {
            $messages[] = $tanpaMasuk . ' sesi dilewati karena absen masuknya lupa dicatat (isi lewat aksi Isi Absen Masuk)';
        }
        if ($belumSelesai > 0) {
            $messages[] = $belumSelesai . ' sesi dilewati karena jam operasionalnya belum lewat';
        }
        if ($noWindow > 0) {
            $messages[] = $noWindow . ' sesi dilewati karena jendela absennya belum diatur di Master Shift';
        }

        if ($request->boolean('close_missing')) {
            $messages[] = $this->closeMissingSessions($data) . ' sesi lupa absen keluar ditutup oleh sistem';
        }

        return back()->with('success', implode(', ', $messages) . '.');
    }

    /**
     * Melengkapi absen keluar crew yang terkunci karena lupa absen (termasuk yang
     * baru ingat setelah lewat tengah malam). Jamnya diambil dari batas jendela
     * sehingga tidak ada jam yang dikarang semaunya, dan tetap ditandai lupa
     * absen keluar supaya Anda bisa menilai ulang: lembur atau tidak.
     *
     * @param array{start_date: string, end_date: string, location_id?: ?int, user_id?: ?int} $data
     */
    private function closeMissingSessions(array $data): int
    {
        $from = Carbon::parse($data['start_date'])->startOfDay();
        $to = Carbon::parse($data['end_date'])->endOfDay();
        $closed = 0;

        foreach (AttendanceRuleService::recentSessions(60) as $session) {
            $in = $session['in'];

            if (!$in || $session['out'] || !$session['location'] || !$session['user']
                || $in->date->lessThan($from) || $in->date->greaterThan($to)) {
                continue;
            }

            if (!array_key_exists(AttendanceRuleService::MISSING_CHECK_OUT, (array) ($in->violations ?? []))) {
                continue;
            }

            if (($data['location_id'] ?? null) && $session['location']->id != $data['location_id']) {
                continue;
            }

            if (($data['user_id'] ?? null) && $in->user_id != $data['user_id']) {
                continue;
            }

            $date = $session['date'];
            $window = AttendanceRuleService::sessionWindow(
                AttendanceRuleService::assignmentsFor($session['user'], $session['location'], $date),
                $date
            );

            $moment = $window['out'][1]
                ? $window['out'][1]->copy()->addSecond()
                : $in->created_at->copy()->addHour();

            // Jam keluar tidak boleh lebih awal dari absen masuk yang ada.
            if ($moment->lessThanOrEqualTo($in->created_at)) {
                $moment = $in->created_at->copy()->addMinute();
            }

            $out = new Attendance([
                'user_id' => $in->user_id,
                'schedule_location_id' => $session['location']->id,
                'schedule_shift_id' => $window['shifts']['out']?->id ?? $in->schedule_shift_id,
                'type' => 'out',
                'date' => $date,
                'status' => $in->status,
                'source' => Attendance::SOURCE_AUTO,
                'corrected_by' => auth()->user()->name,
                'violations' => [
                    AttendanceRuleService::MISSING_CHECK_OUT =>
                        $in->violations[AttendanceRuleService::MISSING_CHECK_OUT] ?? 0,
                ],
                'notes' => 'Absen keluar dilengkapi otomatis (lupa absen keluar). Cek lagi: lembur atau tidak.',
            ]);
            $out->created_at = $moment;
            $out->save();

            $closed++;
        }

        return $closed;
    }

    /**
     * Laporan absensi PDF untuk satu hari, satu minggu, atau satu bulan.
     */
    public function report(Request $request)
    {
        AttendanceRuleService::enforceRecentRules();

        [$from, $to, $periodLabel] = AttendanceRuleService::reportPeriod(
            $request->input('period', 'daily'),
            $request->input('date') ?: now()->toDateString()
        );

        $rows = Attendance::with(['user', 'location', 'shift'])
            ->whereBetween('date', [$from->toDateString(), $to->toDateString()])
            ->when(in_array(auth()->user()->role, ['crew', 'kasir']), fn ($q) => $q->where('user_id', auth()->id()))
            ->when($request->filled('location_id'), fn ($q) => $q->where('schedule_location_id', $request->location_id))
            ->orderBy('date')
            ->orderBy('created_at')
            ->get();

        AttendanceRuleService::attachOvertime($rows);

        $summary = $this->buildSessions($rows)
            ->groupBy(fn (array $session) => $session['user']?->name ?? '-')
            ->map(function ($group) {
                return [
                    'name' => $group->first()['user']?->name ?? '-',
                    'role' => $group->first()['user']?->role ?? 'crew',
                    'rows' => $group->sortBy(fn (array $s) => $s['date'])->values()->all(),
                    'hadir' => count($group),
                    'pelanggaran' => $group->sum(fn (array $s) => count($s['violations'])),
                    'lembur_menit' => $group->sum(fn (array $s) => $s['overtime']?->minutes ?? 0),
                    'lembur_acc' => $group->sum(fn (array $s) => $s['overtime'] && $s['overtime']->status === OvertimeRequest::STATUS_APPROVED ? $s['overtime']->amount : 0),
                ];
            })
            ->sortByDesc('hadir')
            ->values()
            ->all();

        $data = [
            'periodLabel' => $periodLabel,
            'period' => $request->input('period', 'daily'),
            'from' => $from,
            'to' => $to,
            'summary' => $summary,
            'locations' => ScheduleLocation::active()->get(),
            'locationId' => $request->location_id,
        ];

        if ($request->boolean('preview')) {
            return view('schedules.attendances.pdf_report', $data);
        }

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('schedules.attendances.pdf_report', $data)
            ->setPaper('a4', 'landscape');

        $filename = 'LAPORAN_ABSENSI_' . strtoupper(str_replace(' ', '_', $periodLabel)) . '_' . now()->format('YmdHis') . '.pdf';

        return $pdf->download($filename);
    }

    /**
     * Gabungkan absen masuk dan keluar menjadi satu baris sesi per crew/lokasi/tanggal.
     *
     * @return array<int, array<string, mixed>>
     */
    private function buildSessions($rows)
    {
        return $rows->groupBy(
            fn (Attendance $row) => $row->user_id . '-' . $row->schedule_location_id . '-' . $row->date->toDateString()
        )->map(function ($group) {
            $in = $group->firstWhere('type', 'in');
            $out = $group->sortByDesc('created_at')->firstWhere('type', 'out');
            $base = $in ?? $out;

            // Satu sesi bisa punya lebih dari satu baris (koreksi, absen ganda),
            // jadi seluruh pelanggaran dan catatnya digabung.
            $violations = [];
            $dinilai = false;

            foreach ($group as $row) {
                $dinilai = $dinilai || !is_null($row->violations);

                foreach ((array) ($row->violations ?? []) as $code => $value) {
                    $violations[$code] = max($value, $violations[$code] ?? 0);
                }
            }

            return [
                'user' => $base?->user,
                'location' => $base?->location,
                'date' => $base?->date?->toDateString() ?? now()->toDateString(),
                'in' => $in,
                'out' => $out,
                'overtime' => $out?->overtimeRequest ?? $in?->overtimeRequest,
                'violations' => $violations,
                'dinilai' => $dinilai,
                'notes' => $group->pluck('notes')->filter()->unique()->implode(' / '),
                'source' => $in?->source ?? $out?->source,
            ];
        })->values();
    }

    private function violationValue(string $code, Carbon $moment, array $window, Attendance $attendance): int
    {
        $existing = (array) ($attendance->violations ?? []);

        return match ($code) {
            AttendanceRuleService::LATE_CHECK_IN => $window['in'][1] ? max(0, (int) $window['in'][1]->diffInMinutes($moment, false)) : 0,
            AttendanceRuleService::EARLY_CHECK_OUT => $window['out'][0] ? max(0, (int) $moment->diffInMinutes($window['out'][0], false)) : 0,
            AttendanceRuleService::MISSING_CHECK_OUT => $window['out'][1] ? max(0, (int) $window['out'][1]->diffInMinutes($moment, false)) : 0,
            AttendanceRuleService::MISSING_CHECK_IN => $window['in'][1] ? max(0, (int) $window['in'][1]->diffInMinutes($moment, false)) : 0,
            AttendanceRuleService::OUT_OF_RADIUS => (int) ($existing[AttendanceRuleService::OUT_OF_RADIUS] ?? $attendance->distance ?? 0),
            default => 0,
        };
    }

    private function isOwner(): bool
    {
        return in_array(auth()->user()->role, ['superadmin', 'owner']);
    }

    /** @return array{0: ?int, 1: string} jarak dalam meter dan status radius */
    private function locate(Request $request, ScheduleLocation $location): array
    {
        if (!$location->latitude || !$location->longitude) {
            // Lokasi tanpa koordinat diterima tanpa penilaian radius.
            return [null, 'in_radius'];
        }

        $earthRadius = 6371000; // meter

        $latFrom = deg2rad($request->latitude);
        $lonFrom = deg2rad($request->longitude);
        $latTo = deg2rad($location->latitude);
        $lonTo = deg2rad($location->longitude);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
            cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));

        $distance = (int) round($angle * $earthRadius);

        return [$distance, $distance <= ($location->radius ?? 100) ? 'in_radius' : 'out_of_radius'];
    }

    private function savePhoto(Request $request, User $user): string
    {
        $imageParts = explode(";base64,", $request->photo);
        $imageType = explode("image/", $imageParts[0])[1] ?? 'webp';
        $fileName = 'attendance/' . $user->id . '/' . Str::uuid() . '.' . $imageType;

        Storage::disk('public')->put($fileName, base64_decode($imageParts[1]));

        return $fileName;
    }

    private function recordOvertime(Request $request, User $user, ScheduleLocation $location, string $date, $now, array $window): ?OvertimeRequest
    {
        $minutes = AttendanceRuleService::overtimeMinutes($now, $window['out'][1]);

        if ($minutes <= 0) {
            return null;
        }

        $rate = AttendanceRuleService::overtimeRate();

        return OvertimeRequest::updateOrCreate(
            [
                'user_id' => $user->id,
                'date' => $date,
                'schedule_location_id' => $location->id,
            ],
            [
                'schedule_assignment_id' => ScheduleAssignment::where('user_id', $user->id)
                    ->where('date', $date)
                    ->whereHas('shift', fn ($q) => $q->where('schedule_location_id', $location->id))
                    ->value('id'),
                'window_end_at' => $window['out'][1],
                'check_out_at' => $now,
                'minutes' => $minutes,
                'rate_per_hour' => $rate,
                'amount' => OvertimeRequest::amountFor($minutes, $rate),
                'note' => $request->input('overtime_note') ?: null,
                'status' => OvertimeRequest::STATUS_PENDING,
                'reviewed_by' => null,
                'reviewed_at' => null,
                'payroll_entry_id' => null,
            ]
        );
    }

    private function violationMessage(string $code, int $value, array $window): string
    {
        return match ($code) {
            AttendanceRuleService::LATE_CHECK_IN => 'Pelanggaran: terlambat ' . $value . ' menit dari jendela absen masuk (' . AttendanceRuleService::windowLabel($window, 'in') . ').',
            AttendanceRuleService::EARLY_CHECK_OUT => 'Pelanggaran: absen keluar ' . $value . ' menit lebih cepat dari jendela absen keluar (' . AttendanceRuleService::windowLabel($window, 'out') . ').',
            AttendanceRuleService::OUT_OF_RADIUS => 'Pelanggaran: absen ' . $value . ' meter di luar radius lokasi.',
            AttendanceRuleService::MISSING_CHECK_IN => 'Absen masuk hari ini tidak tercatat, jadi dilaporkan ke owner agar jam masuknya dilengkapi.',
            default => 'Pelanggaran: ' . (AttendanceRuleService::labels()[$code] ?? $code) . '.',
        };
    }
}
