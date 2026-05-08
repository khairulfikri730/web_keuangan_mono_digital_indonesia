<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MonthlyUsage extends Model
{
    use HasFactory, \App\Traits\BelongsToWorksheet;

    protected $fillable = [
        'worksheet_id',
        'expense_name',
        'expense_type',
        'supplier',
        'quantity',
        'unit',
        'unit_price',
        'due_date',
        'project_name',
        'product_id',
        'category',
        'sub_category',
        'frequency',
        'month',
        'year',
        'expense_date',
        'usage_amount',
        'payment_method',
        'status',
        'payment_status',
        'paid_at',
        'description',
        'expense_category_id',
        'sync_status'
    ];

    protected $casts = [
        'usage_amount' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'quantity' => 'integer',
        'expense_date' => 'date',
        'due_date' => 'date',
        'paid_at' => 'datetime',
        'month' => 'integer',
        'year' => 'integer'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function category()
    {
        return $this->belongsTo(ExpenseCategory::class, 'expense_category_id');
    }

    public function items()
    {
        return $this->hasMany(MonthlyUsageItem::class);
    }
}
