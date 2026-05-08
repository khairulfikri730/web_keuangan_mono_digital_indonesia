<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MonthlyUsageItem extends Model
{
    protected $fillable = [
        'monthly_usage_id',
        'product_id',
        'quantity',
        'cost_price',
        'subtotal',
    ];

    public function monthlyUsage()
    {
        return $this->belongsTo(MonthlyUsage::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
