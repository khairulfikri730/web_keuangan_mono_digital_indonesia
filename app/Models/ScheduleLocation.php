<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScheduleLocation extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function shifts()
    {
        return $this->hasMany(ScheduleShift::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
