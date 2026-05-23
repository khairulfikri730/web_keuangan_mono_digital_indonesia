<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'work_shift_id', 'user_id', 'date', 'status'
    ];

    public function workShift()
    {
        return $this->belongsTo(WorkShift::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
