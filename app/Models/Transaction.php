<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory, \App\Traits\BelongsToWorksheet;

    protected $fillable = [
        'worksheet_id', 'invoice_number', 'shift_id', 'user_id', 'subtotal', 'discount',
        'tax', 'delivery_fee', 'delivery_destination', 'total', 'paid_amount', 'change_amount', 'paid_so_far', 'payment_method', 'dp_payment_method',
        'status', 'customer_name', 'customer_phone', 'discount_type', 'notes',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'discount' => 'decimal:2',
        'tax' => 'decimal:2',
        'delivery_fee' => 'decimal:2',
        'total' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'change_amount' => 'decimal:2',
        'paid_so_far' => 'decimal:2',
    ];

    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(TransactionItem::class);
    }

    public static function generateInvoiceNumber(bool $shouldLock = false): string
    {
        $prefix = 'INV-' . date('Ymd') . '-';
        
        $query = self::withoutGlobalScope('worksheet')
            ->where('invoice_number', 'like', $prefix . '%')
            ->orderBy('invoice_number', 'desc');
            
        if ($shouldLock) {
            $query->lockForUpdate();
        }
        
        $last = $query->first();

        $number = 1;
        if ($last) {
            $lastNumber = (int) substr($last->invoice_number, -4);
            $number = $lastNumber + 1;
        }

        return $prefix . str_pad($number, 4, '0', STR_PAD_LEFT);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopePiutang($query)
    {
        return $query->where('status', 'pending');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function getRemainingAttribute(): float
    {
        return max(0, $this->total - $this->paid_so_far);
    }

    public function isPiutang(): bool
    {
        return $this->status === 'pending';
    }

    public function isFullyPaid(): bool
    {
        return $this->paid_so_far >= $this->total;
    }

    public function getTotalCostAttribute(): float
    {
        return (float) $this->items->sum(fn($i) => $i->cost_price * $i->quantity);
    }

    public function getGrossProfitAttribute(): float
    {
        return (float) $this->total - $this->total_cost;
    }

    public function worksheet()
    {
        return $this->belongsTo(Worksheet::class);
    }
}
