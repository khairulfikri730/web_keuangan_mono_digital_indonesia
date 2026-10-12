<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    public const SOURCE_DEVICE = 'device';
    public const SOURCE_MANUAL = 'manual';
    public const SOURCE_AUTO = 'auto';

    protected $fillable = [
        'user_id', 'schedule_location_id', 'schedule_shift_id', 'type', 'date', 'photo_path',
        'latitude', 'longitude', 'distance', 'status', 'source', 'corrected_by',
        'violations', 'notes'
    ];

    protected $casts = [
        'date' => 'date',
        'violations' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function location()
    {
        return $this->belongsTo(ScheduleLocation::class, 'schedule_location_id');
    }

    public function shift()
    {
        return $this->belongsTo(ScheduleShift::class, 'schedule_shift_id');
    }

    public function overtimeRequest()
    {
        return $this->hasOne(OvertimeRequest::class, 'user_id')
            ->where('date', $this->date)
            ->where('schedule_location_id', $this->schedule_location_id);
    }

    public static function sourceLabel(?string $source): string
    {
        return [
            self::SOURCE_DEVICE => 'Absen sendiri (selfie)',
            self::SOURCE_MANUAL => 'Diisi owner',
            self::SOURCE_AUTO => 'Diisi otomatis',
        ][$source] ?? ($source ?? '-');
    }
}
