<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Capital extends Model
{
    use HasFactory, \App\Traits\BelongsToWorksheet;

    protected $fillable = [
        'worksheet_id',
        'total_amount',
        'is_detailed',
        'date'
    ];

    protected $casts = [
        'is_detailed' => 'boolean',
        'date' => 'date',
        'total_amount' => 'decimal:2'
    ];

    public function items()
    {
        return $this->hasMany(CapitalItem::class);
    }
}
