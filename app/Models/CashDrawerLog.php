<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CashDrawerLog extends Model
{
    protected $fillable = [
        'transaction_id',
        'status',
        'message'
    ];

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }
}
