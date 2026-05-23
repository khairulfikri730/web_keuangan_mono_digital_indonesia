<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkShift extends Model
{
    use HasFactory;

    protected $fillable = [
        'worksheet_id', 'name', 'start_time', 'end_time', 'color', 'multiplier', 'required_personnel'
    ];

    public function worksheet()
    {
        return $this->belongsTo(Worksheet::class);
    }

    public function schedules()
    {
        return $this->hasMany(WorkSchedule::class);
    }
}
