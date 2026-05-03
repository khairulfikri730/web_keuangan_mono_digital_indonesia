<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shift extends Model
{
    use HasFactory, \App\Traits\BelongsToWorksheet;

    protected $fillable = [
        'worksheet_id', 'opened_by', 'closed_by', 'opening_cash', 'closing_cash',
        'expected_cash', 'discrepancy', 'cash_sales', 'bank_sales', 'cash_expenses',
        'total_sales', 'total_transactions', 'status', 'notes',
        'opened_at', 'closed_at',
    ];

    protected $casts = [
        'opening_cash' => 'decimal:2',
        'closing_cash' => 'decimal:2',
        'expected_cash' => 'decimal:2',
        'discrepancy' => 'decimal:2',
        'cash_sales' => 'decimal:2',
        'bank_sales' => 'decimal:2',
        'cash_expenses' => 'decimal:2',
        'total_sales' => 'decimal:2',
        'opened_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    public function opener()
    {
        return $this->belongsTo(User::class, 'opened_by');
    }

    public function closer()
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function cashflows()
    {
        return $this->hasMany(Cashflow::class);
    }

    public function isOpen(): bool
    {
        return $this->status === 'open';
    }

    public static function activeShift(): ?Shift
    {
        return self::where('status', 'open')->latest()->first();
    }

    public function worksheet()
    {
        return $this->belongsTo(Worksheet::class);
    }
}
