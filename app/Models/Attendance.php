<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    protected $fillable = [
        'user_id', 'schedule_location_id', 'type', 'photo_path', 
        'latitude', 'longitude', 'distance', 'status', 'notes'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function location()
    {
        return $this->belongsTo(ScheduleLocation::class, 'schedule_location_id');
    }
}
