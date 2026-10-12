<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OvertimeRequest extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'user_id', 'schedule_location_id', 'schedule_assignment_id', 'date',
        'window_end_at', 'check_out_at', 'minutes', 'rate_per_hour', 'amount',
        'note', 'status', 'reviewed_by', 'reviewed_at', 'payroll_entry_id'
    ];

    protected $casts = [
        'date' => 'date',
        'window_end_at' => 'datetime',
        'check_out_at' => 'datetime',
        'reviewed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function location()
    {
        return $this->belongsTo(ScheduleLocation::class, 'schedule_location_id');
    }

    public function payrollEntry()
    {
        return $this->belongsTo(PayrollEntry::class, 'payroll_entry_id');
    }

    /**
     * Nominal lembur: tarif per jam, dihitung proporsional per menit.
     */
    public static function amountFor(int $minutes, int $ratePerHour): int
    {
        return (int) round($minutes * $ratePerHour / 60);
    }

    public static function statusLabel(string $status): string
    {
        return [
            self::STATUS_PENDING => 'Menunggu Acc',
            self::STATUS_APPROVED => 'Di-acc',
            self::STATUS_REJECTED => 'Ditolak',
        ][$status] ?? $status;
    }

    public function durationLabel(): string
    {
        return intdiv($this->minutes, 60) . ' jam ' . ($this->minutes % 60) . ' menit';
    }
}
