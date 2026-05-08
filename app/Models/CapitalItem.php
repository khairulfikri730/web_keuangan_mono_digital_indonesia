<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CapitalItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'capital_id',
        'name',
        'type',
        'price',
        'quantity',
        'total_price',
        'product_id'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'total_price' => 'decimal:2'
    ];

    public function capital()
    {
        return $this->belongsTo(Capital::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
