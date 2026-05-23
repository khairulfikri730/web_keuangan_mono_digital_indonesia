<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScheduleCrew extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'phone', 'position', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function assignments()
    {
        return $this->hasMany(ScheduleAssignment::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
