<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name', 'email', 'phone', 'role', 'is_active', 'password',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
    ];

    public function isOwner(): bool
    {
        return $this->role === 'owner';
    }

    public function isOperator(): bool
    {
        return $this->role === 'operator';
    }

    public function shifts()
    {
        return $this->hasMany(Shift::class, 'opened_by');
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function cashflows()
    {
        return $this->hasMany(Cashflow::class);
    }

    public function stockMutations()
    {
        return $this->hasMany(StockMutation::class);
    }
}
