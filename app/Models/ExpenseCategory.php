<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExpenseCategory extends Model
{
    protected $fillable = [
        'worksheet_id',
        'parent_category',
        'name',
        'is_active'
    ];

    public function worksheet()
    {
        return $this->belongsTo(Worksheet::class);
    }

    public function monthlyUsages()
    {
        return $this->hasMany(MonthlyUsage::class);
    }
}
