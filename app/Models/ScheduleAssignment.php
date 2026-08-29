<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScheduleAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'schedule_shift_id', 'user_id', 'date', 'notes',
        'status', 'closed_by', 'closed_reason', 'closed_at_time',
        'original_user_id', 'changed_by',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function shift()
    {
        return $this->belongsTo(ScheduleShift::class, 'schedule_shift_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function originalUser()
    {
        return $this->belongsTo(User::class, 'original_user_id');
    }

    public function swapTargetUser()
    {
        return $this->belongsTo(User::class, 'swap_requested_to');
    }

    public function isOpen()
    {
        return $this->status === 'open';
    }

    public function isClosed()
    {
        return $this->status === 'close';
    }

    public function wasReplaced()
    {
        return !is_null($this->original_crew_id);
    }

    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }

    public function scopeClosed($query)
    {
        return $query->where('status', 'close');
    }
}
