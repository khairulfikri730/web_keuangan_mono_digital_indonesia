<?php

namespace App\Services;

use App\Models\Attendance;
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
            $closedOut = Attendance::where('user_id', $user->id)
                ->where('schedule_location_id', $location->id)
                ->where('type', 'out')
                ->whereDate('date', $in->date->format('Y-m-d'))
                ->where('created_at', '>', $in->created_at)
                ->exists();

            if (!$closedOut) {
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
     * Sesi yang absen masuknya tidak pernah ditutup hingga melewati batas toleransi
     * dinyatakan lupa absen keluar. Dipanggil setiap kali data absensi dibaca sehingga
     * tidak memerlukan cron pada server.
     *
     * Hanya baris yang sudah dinilai (violations terisi) dan masih baru yang diperiksa,
     * supaya data lama sebelum jendela absen ada tidak ikut ditandai.
     *
     * @return int jumlah sesi yang baru ditandai
     */
    public static function markMissingCheckOut(?Carbon $now = null): int
    {
        if (!self::enforcementEnabled()) {
            return 0;
        }

        $now = $now ?? Carbon::now();
        $since = $now->copy()->subDays(30)->toDateString();
        $until = $now->toDateString();

        $lastOut = Attendance::where('type', 'out')
            ->whereBetween('date', [$since, $until])
            ->get(['user_id', 'schedule_location_id', 'date', 'created_at'])
            ->reduce(function (array $carry, Attendance $out) {
                $key = $out->user_id . '-' . $out->schedule_location_id . '-' . $out->date->toDateString();
                $previous = $carry[$key] ?? null;
                $carry[$key] = $previous && $previous->greaterThan($out->created_at) ? $previous : $out->created_at;

                return $carry;
            }, []);

        $marked = 0;

        $ins = Attendance::with(['user', 'location'])
            ->where('type', 'in')
            ->whereNotNull('violations')
            ->whereBetween('date', [$since, $until])
            ->get();

        foreach ($ins as $in) {
            $key = $in->user_id . '-' . $in->schedule_location_id . '-' . $in->date->toDateString();
            $closedAt = $lastOut[$key] ?? null;

            if ($closedAt && $closedAt->greaterThan($in->created_at)) {
                continue;
            }

            $date = $in->date->toDateString();
            $window = self::sessionWindow(self::assignmentsFor($in->user, $in->location, $date), $date);
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
