<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Worksheet extends Model
{
    protected $fillable = [
        'name',
        'initial_balance',
        'target_payback_months',
        'description',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class, 'worksheet_user');
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function categories()
    {
        return $this->hasMany(Category::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function cashflows()
    {
        return $this->hasMany(Cashflow::class);
    }

    public function shifts()
    {
        return $this->hasMany(Shift::class);
    }
}
