<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class ScheduleShift extends Model
{
    use HasFactory;

    protected $fillable = [
        'schedule_location_id', 'name', 'start_time', 'end_time', 'color', 'max_crew',
        'check_in_start', 'check_in_end', 'check_out_start', 'check_out_end'
    ];

    public function location()
    {
        return $this->belongsTo(ScheduleLocation::class, 'schedule_location_id');
    }

    public function assignments()
    {
        return $this->hasMany(ScheduleAssignment::class);
    }

    public function getTimeRangeAttribute()
    {
        return substr($this->start_time, 0, 5) . ' - ' . substr($this->end_time, 0, 5);
    }

    /** Shift yang beroperasi melewati tengah malam, mis. 18:00 - 11:15. */
    public function isOvernight(): bool
    {
        return substr($this->end_time, 0, 5) <= substr($this->start_time, 0, 5);
    }

    /** @return array{0: ?string, 1: ?string} jam awal dan jam akhir sebuah jendela absen */
    public function windowFor(string $type): array
    {
        return $type === 'in'
            ? [$this->check_in_start, $this->check_in_end]
            : [$this->check_out_start, $this->check_out_end];
    }

    public function hasWindow(string $type): bool
    {
        [$from, $to] = $this->windowFor($type);

        return filled($from) && filled($to);
    }

    /**
     * Jendela absen sebagai titik waktu, diikat ke tanggal operasional shift.
     * Jendela keluar untuk shift yang melewati tengah malam jatuh di hari berikutnya.
     *
     * @return array{in: array{0: ?Carbon, 1: ?Carbon}, out: array{0: ?Carbon, 1: ?Carbon}}
     */
    public function windowDateTimes(string $operationalDate): array
    {
        $result = ['in' => [null, null], 'out' => [null, null]];

        foreach (['in', 'out'] as $type) {
            if (!$this->hasWindow($type)) {
                continue;
            }

            $day = Carbon::parse($operationalDate)->startOfDay();
            if ($type === 'out' && $this->isOvernight()) {
                $day->addDay();
            }

            [$from, $to] = $this->windowFor($type);
            $result[$type] = [
                Carbon::parse($day->format('Y-m-d') . ' ' . substr($from, 0, 5)),
                Carbon::parse($day->format('Y-m-d') . ' ' . substr($to, 0, 5)),
            ];
        }

        return $result;
    }
}
