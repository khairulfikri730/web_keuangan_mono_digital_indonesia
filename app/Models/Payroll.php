<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payroll extends Model
{
    protected $fillable = [
        'user_id', 'period', 'photographer_fee', 'overtime_fee', 'bonus', 'deduction', 'notes'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
