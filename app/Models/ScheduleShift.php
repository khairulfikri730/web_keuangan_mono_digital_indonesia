<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScheduleShift extends Model
{
    use HasFactory;

    protected $fillable = ['schedule_location_id', 'name', 'start_time', 'end_time', 'color', 'max_crew'];

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
}
