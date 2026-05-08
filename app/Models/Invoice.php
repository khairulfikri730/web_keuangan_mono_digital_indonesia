<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $fillable = [
        'invoice_number', 'date', 'due_date',
        'business_name', 'business_email', 'business_phone', 'business_address', 'business_logo',
        'client_name', 'client_company', 'client_phone', 'client_email', 'client_address',
        'subtotal', 'discount_type', 'discount_value', 'discount', 'total_amount', 'paid_amount',
        'status', 'notes', 'worksheet_id', 'created_by'
    ];

    protected $casts = [
        'date' => 'date',
        'due_date' => 'date',
        'subtotal' => 'decimal:2',
        'discount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
    ];

    public function items()
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function payments()
    {
        return $this->hasMany(InvoicePayment::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function worksheet()
    {
        return $this->belongsTo(Worksheet::class);
    }

    public function getBalanceRemainingAttribute()
    {
        return (float) ($this->total_amount - $this->paid_amount);
    }

    public function getPaymentPercentageAttribute()
    {
        if ($this->total_amount <= 0) return 0;
        return round(($this->paid_amount / $this->total_amount) * 100);
    }
}
