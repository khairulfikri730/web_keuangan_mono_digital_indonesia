<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cashflow extends Model
{
    use HasFactory, \App\Traits\BelongsToWorksheet;

    protected $fillable = [
        'worksheet_id', 'user_id', 'shift_id', 'type', 'category', 'description',
        'amount', 'reference', 'reference_id', 'source', 'transaction_date', 'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'transaction_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }

    public function scopeIncome($query)
    {
        return $query->where('type', 'income');
    }

    public function scopeExpense($query)
    {
        return $query->where('type', 'expense');
    }

    public static function sourceLabels(): array
    {
        return [
            'pos_cash' => 'Tunai',
            'pos_bank' => 'Bank/QRIS',
            'transfer' => 'Transfer Kasir ke Bank',
            'pos' => 'POS',
            'manual' => 'Manual',
        ];
    }

    public function getSourceLabelAttribute(): string
    {
        return self::sourceLabels()[$this->source] ?? ucfirst($this->source);
    }
    public function scopeByPeriod($query, $period)
    {
        $now = \Carbon\Carbon::now();

        return match ($period) {
            'hari_ini' => $query->whereDate('transaction_date', \Carbon\Carbon::today()),
            'kemarin' => $query->whereDate('transaction_date', \Carbon\Carbon::yesterday()),
            'minggu_ini' => $query->whereBetween('transaction_date', [
                $now->startOfWeek(), $now->endOfWeek()
            ]),
            'bulan_ini' => $query->whereMonth('transaction_date', $now->month)
                ->whereYear('transaction_date', $now->year),
            'tahun_ini' => $query->whereYear('transaction_date', $now->year),
            default => $query,
        };
    }

    public function worksheet()
    {
        return $this->belongsTo(Worksheet::class);
    }
}
