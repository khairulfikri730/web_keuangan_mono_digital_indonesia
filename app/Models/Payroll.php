<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payroll extends Model
{
    protected $fillable = [
        'user_id', 'period', 'photographer_fee', 'overtime_fee', 'bonus', 'deduction', 'notes',
        'photographer_fee_note', 'overtime_fee_note', 'bonus_note', 'deduction_note', 'is_finalized'
    ];

    protected $casts = [
        'is_finalized' => 'boolean'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
