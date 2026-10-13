<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\OvertimeRequest;
use App\Models\ScheduleAssignment;
use App\Models\ScheduleLocation;
use App\Models\ScheduleShift;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;

class AttendanceRuleService
{
    public const LATE_CHECK_IN = 'late_check_in';
    public const EARLY_CHECK_OUT = 'early_check_out';
    public const OUT_OF_RADIUS = 'out_of_radius';
    public const MISSING_CHECK_OUT = 'missing_check_out';

    public static function labels(): array
    {
        return [
            self::LATE_CHECK_IN => 'Terlambat absen masuk',
            self::EARLY_CHECK_OUT => 'Absen keluar terlalu cepat',
            self::OUT_OF_RADIUS => 'Absen di luar radius lokasi',
            self::MISSING_CHECK_OUT => 'Lupa absen keluar',
        ];
    }

    public static function enforcementEnabled(): bool
    {
        return Setting::get('attendance_window_enabled', '1') === '1';
    }

    /**
     * Absen keluar yang menutup sebuah absen masuk. Dipilih berdasarkan urutan waktu
     * pencatatan, bukan kesamaan tanggal, karena absen keluar setelah tengah malam
     * tercatat pada tanggal hari berikutnya.
     */
    public static function closingOut(Attendance $in): ?Attendance
    {
        $next = Attendance::where('user_id', $in->user_id)
            ->where('schedule_location_id', $in->schedule_location_id)
            ->where('created_at', '>', $in->created_at)
            ->orderBy('created_at')
            ->first();

        return $next && $next->type === 'out' ? $next : null;
    }

    /**
     * Satu sesi absen = satu crew di satu lokasi pada satu tanggal operasional,
     * sehingga lokasi dengan 2 shift tetap cukup absen masuk sekali dan keluar sekali.
     */
    public static function openSession(User $user, ScheduleLocation $location): ?Attendance
    {
        $since = Carbon::today()->subDay()->format('Y-m-d');

        $ins = Attendance::where('user_id', $user->id)
            ->where('schedule_location_id', $location->id)
            ->where('type', 'in')
            ->whereDate('date', '>=', $since)
            ->orderByDesc('created_at')
            ->get();

        foreach ($ins as $in) {
            if (!self::closingOut($in)) {
                return $in;
            }
        }

        return null;
    }

    public static function assignmentsFor(User $user, ScheduleLocation $location, string $date): Collection
    {
        return ScheduleAssignment::with('shift')
            ->where('user_id', $user->id)
            ->where('date', $date)
            ->whereHas('shift', fn ($q) => $q->where('schedule_location_id', $location->id))
            ->get();
    }

    /**
     * Jendela absen sebuah sesi: masuk diambil dari shift paling awal,
     * keluar dari shift yang jendela akhirnya paling akhir.
     *
     * @return array{in: array{0: ?Carbon, 1: ?Carbon}, out: array{0: ?Carbon, 1: ?Carbon}, shifts: array{in: ?ScheduleShift, out: ?ScheduleShift}}
     */
    public static function sessionWindow(Collection $assignments, string $date): array
    {
        $window = [
            'in' => [null, null],
            'out' => [null, null],
            'shifts' => ['in' => null, 'out' => null],
        ];

        foreach ($assignments as $assignment) {
            $shift = $assignment->shift;
            if (!$shift) {
                continue;
            }

            $candidate = $shift->windowDateTimes($date);

            if ($candidate['in'][1] && (!$window['in'][1] || $candidate['in'][0] < $window['in'][0])) {
                $window['in'] = $candidate['in'];
                $window['shifts']['in'] = $shift;
            }

            if ($candidate['out'][1] && (!$window['out'][1] || $candidate['out'][1] > $window['out'][1])) {
                $window['out'] = $candidate['out'];
                $window['shifts']['out'] = $shift;
            }
        }

        return $window;
    }

    /**
     * Pelanggaran waktu berupa code => selisih menit. Kosong bila penegakan jendela
     * dimatikan atau jendela belum diatur untuk sesi ini.
     *
     * @return array<string, int>
     */
    public static function timeViolations(string $type, Carbon $moment, array $window): array
    {
        if (!self::enforcementEnabled()) {
            return [];
        }

        [$from, $to] = $window[$type];

        if (!$from || !$to) {
            return [];
        }

        if ($type === 'in' && $moment->greaterThan($to)) {
            return [self::LATE_CHECK_IN => (int) $to->diffInMinutes($moment, false)];
        }

        if ($type === 'out' && $moment->lessThan($from)) {
            return [self::EARLY_CHECK_OUT => (int) $moment->diffInMinutes($from, false)];
        }

        return [];
    }

    /**
     * Durasi lembur dalam menit: selisih absen keluar dengan batas akhir jendela keluar,
     * di luar nilai toleransi.
     */
    public static function overtimeMinutes(Carbon $checkOut, ?Carbon $windowEnd): int
    {
        if (!$windowEnd || !self::enforcementEnabled()) {
            return 0;
        }

        $minutes = (int) $windowEnd->diffInMinutes($checkOut, false);
        $grace = (int) Setting::get('overtime_grace_minutes', 0);

        return $minutes > $grace ? $minutes : 0;
    }

    public static function overtimeRate(): int
    {
        return (int) Setting::get('overtime_rate_per_hour', 15000);
    }

    public static function missingGraceHours(): int
    {
        return (int) Setting::get('missing_check_out_grace_hours', 3);
    }

    /**
     * Tandai sesi yang absen masuknya tidak pernah ditutup sampai melewati batas
     * toleransi. Dipanggil setiap kali data absensi dibaca sehingga server tidak
     * memerlukan cron.
     *
     * @return int jumlah sesi yang baru ditandai
     */
    public static function markMissingCheckOut(?Carbon $now = null): int
    {
        if (!self::enforcementEnabled()) {
            return 0;
        }

        $now = $now ?? Carbon::now();
        $marked = 0;

        foreach (self::recentSessions(30, $now) as $session) {
            $in = $session['in'];

            if (!$in || $session['out'] || !$in->user || !$in->location) {
                continue;
            }

            $window = self::sessionWindow(
                self::assignmentsFor($in->user, $in->location, $session['date']),
                $session['date']
            );
            $deadline = self::missingDeadline($window);

            if (!$deadline || !$deadline->lessThan($now)) {
                continue;
            }

            $violations = (array) ($in->violations ?? []);

            if (array_key_exists(self::MISSING_CHECK_OUT, $violations)) {
                continue;
            }

            $violations[self::MISSING_CHECK_OUT] = (int) $window['out'][1]->diffInMinutes($now, false);
            $in->update(['violations' => $violations]);
            $marked++;
        }

        return $marked;
    }

    /**
     * Semua aturan absensi yang berjalan saat halaman dibaca: nilai ulang status,
     * buatkan pengajuan lembur, dan tandai sesi yang lupa ditutup.
     */
    public static function enforceRecentRules(?Carbon $now = null): void
    {
        self::reassess($now);
        self::markMissingCheckOut($now);
    }

    /**
     * Batas akhir sesi masih wajar belum absen keluar.
     *
     * @param array{in: array{0: ?Carbon, 1: ?Carbon}, out: array{0: ?Carbon, 1: ?Carbon}} $window
     */
    public static function missingDeadline(array $window): ?Carbon
    {
        return $window['out'][1]?->copy()->addHours(self::missingGraceHours());
    }

    /**
     * Absen masuk yang belum ditutup dan sudah melewati batas toleransi lupa absen
     * keluar. Sesi seperti ini ditutup untuk crew: hanya owner yang boleh melanjutkannya,
     * supaya absen hari berikutnya tidak menimpa sesi yang belum jelas akhirnya.
     */
    public static function staleOpenSession(User $user, ScheduleLocation $location, ?Carbon $now = null): ?Attendance
    {
        $now = $now ?? Carbon::now();
        $open = self::openSession($user, $location);

        if (!$open) {
            return null;
        }

        $date = $open->date->toDateString();
        $deadline = self::missingDeadline(
            self::sessionWindow(self::assignmentsFor($user, $location, $date), $date)
        );

        return $deadline && $deadline->lessThan($now) ? $open : null;
    }

    /**
     * Susun sesi absen dari urutan pencatatan per crew dan lokasi. Absen keluar yang
     * tercatat setelah tengah malam tetap menjadi penutup sesi hari sebelumnya.
     *
     * @return array<int, array{user: User, location: ScheduleLocation, date: string, in: ?Attendance, out: ?Attendance}>
     */
    public static function recentSessions(int $days = 30, ?Carbon $until = null): array
    {
        $from = Carbon::now()->subDays($days)->startOfDay();
        $until = $until ?? Carbon::now();

        $rows = Attendance::with(['user', 'location'])
            ->whereBetween('created_at', [$from, $until])
            ->orderBy('created_at')
            ->get();

        $sessions = [];

        foreach ($rows->groupBy(fn (Attendance $row) => $row->user_id . '-' . $row->schedule_location_id) as $group) {
            $pending = null;

            foreach ($group as $row) {
                if ($row->type === 'in') {
                    if ($pending) {
                        $sessions[] = self::sessionOf($pending, null);
                    }
                    $pending = $row;
                    continue;
                }

                if ($pending) {
                    $sessions[] = self::sessionOf($pending, $row);
                    $pending = null;
                    continue;
                }

                $sessions[] = self::sessionOf(null, $row);
            }

            if ($pending) {
                $sessions[] = self::sessionOf($pending, null);
            }
        }

        return $sessions;
    }

    private static function sessionOf(?Attendance $in, ?Attendance $out): array
    {
        $base = $in ?? $out;

        return [
            'user' => $base->user,
            'location' => $base->location,
            'date' => ($in ?? $out)->date->toDateString(),
            'in' => $in,
            'out' => $out,
        ];
    }

    /**
     * Nilai ulang absensi yang tercatat sebelum jendela waktu diatur, dan buatkan
     * pengajuan lembur otomatis untuk absen keluar yang lewat batas. Nama crew dan
     * jamnya tidak diubah, hanya statusnya yang mengikuti jendela saat ini.
     *
     * @return array{dinilai: int, lembur: int}
     */
    public static function reassess(?Carbon $now = null): array
    {
        if (!self::enforcementEnabled()) {
            return ['dinilai' => 0, 'lembur' => 0];
        }

        $updated = $overtimes = 0;

        foreach (self::recentSessions(30, $now) as $session) {
            if (!$session['in'] && !$session['out']) {
                continue;
            }

            if (!$session['user'] || !$session['location']) {
                continue;
            }

            $window = self::sessionWindow(
                self::assignmentsFor($session['user'], $session['location'], $session['date']),
                $session['date']
            );

            if (!$window['in'][1] && !$window['out'][1]) {
                continue;
            }

            foreach (['in' => $session['in'], 'out' => $session['out']] as $type => $row) {
                if (!$row) {
                    continue;
                }

                $violations = self::timeViolations($type, $row->created_at, $window);

                $existing = (array) ($row->violations ?? []);

                // Jarak radius dan keputusan lupa absen keluar adalah penilaian yang
                // sudah dibuat, jadi tidak dihitung ulang.
                foreach ([self::OUT_OF_RADIUS, self::MISSING_CHECK_OUT] as $keep) {
                    if (isset($existing[$keep])) {
                        $violations[$keep] = $existing[$keep];
                    }
                }

                // Absen keluar setelah tengah malam tercatat pada tanggal shift asalnya.
                $date = $type === 'out' ? $session['date'] : $row->date->toDateString();

                if ($violations !== (array) ($row->violations ?? []) || $date !== $row->date->toDateString()) {
                    $row->update(['violations' => $violations, 'date' => $date]);
                    $updated++;
                }
            }

            if ($session['out'] && !$session['in']) {
                continue;
            }

            $minutes = self::overtimeMinutes(
                $session['out']?->created_at ?? $now ?? Carbon::now(),
                $window['out'][1]
            );

            $existing = OvertimeRequest::where('user_id', $session['user']->id)
                ->where('schedule_location_id', $session['location']->id)
                ->whereDate('date', $session['date'])
                ->orderBy('id')
                ->first();

            if (!$existing) {
                if (!$session['out'] || $minutes <= 0) {
                    continue;
                }

                $rate = self::overtimeRate();

                OvertimeRequest::create([
                    'user_id' => $session['user']->id,
                    'schedule_location_id' => $session['location']->id,
                    'date' => $session['date'],
                    'window_end_at' => $window['out'][1],
                    'check_out_at' => $session['out']->created_at,
                    'minutes' => $minutes,
                    'rate_per_hour' => $rate,
                    'amount' => OvertimeRequest::amountFor($minutes, $rate),
                    'note' => 'Lembur terdeteksi otomatis dari absen keluar',
                    'status' => OvertimeRequest::STATUS_PENDING,
                    'source' => OvertimeRequest::SOURCE_AUTO,
                ]);

                $overtimes++;
                continue;
            }

            // Lembur turunan sistem yang belum diproses ikut jam absen keluar dan
            // jendela shift terbaru. Entri manual dan yang sudah di-acc/ditolak
            // dikunci: itu keputusan owner, dan masih bisa lewat tombol Koreksi.
            if ($session['out']
                && $existing->source === OvertimeRequest::SOURCE_AUTO
                && $existing->status === OvertimeRequest::STATUS_PENDING) {
                $rate = self::overtimeRate();

                if ($minutes <= 0) {
                    $existing->delete();
                    continue;
                }

                $nominal = OvertimeRequest::amountFor($minutes, $rate);

                $berubah = $existing->minutes !== $minutes
                    || $existing->amount !== $nominal
                    || $existing->rate_per_hour !== $rate
                    || !Carbon::parse($existing->check_out_at)->equalTo($session['out']->created_at)
                    || !Carbon::parse($existing->window_end_at)->equalTo($window['out'][1]);

                if ($berubah) {
                    $existing->update([
                        'window_end_at' => $window['out'][1],
                        'check_out_at' => $session['out']->created_at,
                        'minutes' => $minutes,
                        'rate_per_hour' => $rate,
                        'amount' => $nominal,
                    ]);

                    $overtimes++;
                }
            }
        }

        return ['dinilai' => $updated, 'lembur' => $overtimes];
    }

    /**
     * Pasangkan pengajuan lembur ke sekumpulan baris absensi memakai satu query.
     * Relasi overtimeRequest tidak dapat di-eager load karena konstraint tanggalnya
     * dibuat dari model hampa, sehingga hasilnya selalu kosong.
     */
    public static function attachOvertime(Collection $rows): void
    {
        if ($rows->isEmpty()) {
            return;
        }

        $dates = $rows->map(fn (Attendance $row) => $row->date?->toDateString())->filter();

        $requests = OvertimeRequest::whereIn('user_id', $rows->pluck('user_id')->unique())
            ->whereBetween('date', [$dates->min(), $dates->max()])
            ->get()
            ->keyBy(fn (OvertimeRequest $o) => $o->user_id . '-' . $o->schedule_location_id . '-' . $o->date->toDateString());

        foreach ($rows as $row) {
            $row->setRelation(
                'overtimeRequest',
                $requests->get($row->user_id . '-' . $row->schedule_location_id . '-' . $row->date?->toDateString())
            );
        }
    }

    /**
     * Periode laporan: harian, mingguan, atau bulanan.
     *
     * @return array{0: Carbon, 1: Carbon, 2: string}
     */
    public static function reportPeriod(?string $period, ?string $date): array
    {
        $period = in_array($period, ['daily', 'weekly', 'monthly']) ? $period : 'daily';
        $moment = $date ? Carbon::parse($date) : Carbon::today();

        $range = match ($period) {
            'weekly' => [$moment->copy()->startOfWeek(Carbon::MONDAY), $moment->copy()->endOfWeek(Carbon::SUNDAY)],
            'monthly' => [$moment->copy()->startOfMonth(), $moment->copy()->endOfMonth()],
            default => [$moment->copy()->startOfDay(), $moment->copy()->endOfDay()],
        };

        $label = match ($period) {
            'weekly' => 'Minggu ' . $range[0]->translatedFormat('d M') . ' - ' . $range[1]->translatedFormat('d M Y'),
            'monthly' => $range[0]->translatedFormat('F Y'),
            default => $range[0]->translatedFormat('l, d F Y'),
        };

        return [$range[0], $range[1], $label];
    }

    public static function windowLabel(array $window, string $type, ?string $operationalDate = null): ?string
    {
        [$from, $to] = $window[$type];

        if (!$from || !$to) {
            return null;
        }

        $label = $from->format('H:i') . ' - ' . $to->format('H:i');

        if ($operationalDate && $from->toDateString() !== Carbon::parse($operationalDate)->toDateString()) {
            $label = 'besok, ' . $label;
        }

        return $label;
    }
}
