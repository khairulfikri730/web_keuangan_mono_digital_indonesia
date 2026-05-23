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
        'pos'               => 'Akses POS Kasir',
        'transactions.view' => 'Lihat Daftar Transaksi',
        'transactions.edit' => 'Edit Transaksi',
        'transactions.delete'=> 'Hapus Transaksi',
        'invoices.view'     => 'Lihat Invoice',
        'invoices.create'   => 'Buat Invoice Baru',
        'invoices.delete'   => 'Hapus Invoice',
        'products.view'     => 'Lihat Katalog Produk',
        'products.create'   => 'Tambah Produk',
        'products.edit'     => 'Edit Produk',
        'products.delete'   => 'Hapus Produk',
        'categories.view'   => 'Lihat Kategori',
        'categories.create' => 'Tambah Kategori',
        'categories.edit'   => 'Edit Kategori',
        'categories.delete' => 'Hapus Kategori',
        'stock.view'        => 'Lihat Gudang Stok',
        'stock.edit'        => 'Update Stok',
        'shifts.view'       => 'Lihat Sesi Shift',
        'shifts.manage'     => 'Buka/Tutup Shift',
        'cashflow.view'     => 'Lihat Arus Kas',
        'cashflow.create'   => 'Input Kas Keluar/Masuk',
        'cashflow.delete'   => 'Hapus Record Kas',
        'sales.view'        => 'Analisa Penjualan',
        'capitals.view'     => 'Lihat Modal Usaha',
        'capitals.manage'   => 'Kelola Modal Usaha',
        'monthly_expenses.view' => 'Lihat Biaya Bulanan',
        'monthly_expenses.manage' => 'Kelola Biaya Bulanan',
        'expense_categories.view' => 'Lihat Jenis Biaya',
        'expense_categories.manage' => 'Kelola Jenis Biaya',
        'reports_financial' => 'Laporan Laba Rugi',
        'reports_shifts'    => 'Laporan Shift',
        'team.view'         => 'Lihat Manajemen Tim',
        'team.manage'       => 'Kelola Anggota Tim',
        'schedules.view'    => 'Lihat Jadwal Kerja',
        'schedules.manage'  => 'Kelola Jadwal Kerja',
        'settings'          => 'Pengaturan Toko',
    ];

    const PERMISSION_GROUPS = [
        'Transaksi & Kasir' => [
            'pos'               => 'POS Kasir',
            'transactions.view' => 'Lihat Transaksi',
            'transactions.edit' => 'Edit Transaksi',
            'transactions.delete'=> 'Hapus Transaksi',
            'invoices.view'     => 'Lihat Invoice',
            'invoices.create'   => 'Buat Invoice',
            'invoices.delete'   => 'Hapus Invoice',
        ],
        'Produk & Stok' => [
            'products.view'     => 'Lihat Produk',
            'products.create'   => 'Tambah Produk',
            'products.edit'     => 'Edit Produk',
            'products.delete'   => 'Hapus Produk',
            'categories.view'   => 'Lihat Kategori',
            'categories.create' => 'Tambah Kategori',
            'categories.edit'   => 'Edit Kategori',
            'categories.delete' => 'Hapus Kategori',
            'stock.view'        => 'Lihat Stok',
            'stock.edit'        => 'Update Stok',
        ],
        'Keuangan & Laporan' => [
            'cashflow.view'     => 'Lihat Arus Kas',
            'cashflow.create'   => 'Input Kas (In/Out)',
            'cashflow.delete'   => 'Hapus Record Kas',
            'sales.view'        => 'Analisa Penjualan',
            'capitals.view'     => 'Lihat Modal Usaha',
            'capitals.manage'   => 'Kelola Modal Usaha',
            'monthly_expenses.view' => 'Lihat Biaya Bulanan',
            'monthly_expenses.manage' => 'Kelola Biaya Bulanan',
            'expense_categories.view' => 'Lihat Jenis Biaya',
            'expense_categories.manage' => 'Kelola Jenis Biaya',
            'reports_financial' => 'Laporan Laba Rugi',
            'reports_shifts'    => 'Laporan Shift',
        ],
        'Sistem & Operasional' => [
            'shifts.view'       => 'Lihat Sesi Shift',
            'shifts.manage'     => 'Buka/Tutup Shift',
            'team.view'         => 'Lihat Manajemen Tim',
            'team.manage'       => 'Kelola Anggota Tim',
            'schedules.view'    => 'Lihat Jadwal Kerja',
            'schedules.manage'  => 'Kelola Jadwal Kerja',
            'settings'          => 'Pengaturan Toko',
        ],
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
