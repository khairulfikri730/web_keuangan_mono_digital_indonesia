<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    // Available permissions that super admin can assign to kasir
    const AVAILABLE_PERMISSIONS = [
        'pos'              => 'POS Kasir',
        'transactions'     => 'Daftar Transaksi',
        'shifts'           => 'Sesi Shift',
        'products'         => 'Katalog Produk',
        'categories'       => 'Kategori Produk',
        'stock'            => 'Gudang Stok',
        'cashflow'         => 'Arus Kas',
        'sales'            => 'Analisa Penjualan',
        'reports_financial'=> 'Laporan Laba Rugi',
        'reports_shifts'   => 'Laporan Shift',
        'team'             => 'Manajemen Tim',
        'settings'         => 'Pengaturan Toko',
    ];

    protected $fillable = [
        'name', 'email', 'phone', 'role', 'permissions', 'is_active', 'password',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
        'permissions' => 'array',
    ];

    public function isOwner(): bool
    {
        return $this->role === 'owner';
    }

    public function isKasir(): bool
    {
        return $this->role === 'kasir';
    }

    // Keep backward compat
    public function isOperator(): bool
    {
        return $this->isKasir();
    }

    /**
     * Check if user has a specific permission.
     * Owners always have all permissions.
     */
    public function hasPermission(string $permission): bool
    {
        if ($this->isOwner()) {
            return true;
        }

        $perms = $this->permissions ?? [];
        return in_array($permission, $perms);
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

    public function worksheets()
    {
        return $this->belongsToMany(Worksheet::class, 'worksheet_user');
    }
}
