<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ShiftController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\CashflowController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SalesController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\SettingController;
use Illuminate\Support\Facades\Route;

// Auth routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Redirect root to dashboard
Route::get('/', fn() => redirect()->route('dashboard'));

// Authenticated routes
Route::middleware(['auth'])->group(function () {

    // Dashboard - semua role
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // POS - semua role (operator & owner), tapi harus ada shift aktif
    Route::prefix('pos')->name('pos.')->group(function () {
        Route::get('/', [PosController::class, 'index'])->name('index');
        Route::get('/products', [PosController::class, 'getProducts'])->name('products');
        Route::post('/checkout', [PosController::class, 'checkout'])->name('checkout');
        Route::get('/receipt/{transaction}', [PosController::class, 'receipt'])->name('receipt');
        
        // POS Groups
        Route::post('/groups/sync-all', [PosController::class, 'syncAllGroups'])->name('groups.syncAll');
        Route::post('/groups', [PosController::class, 'storeGroup'])->name('groups.store');
        Route::put('/groups/{group}', [PosController::class, 'updateGroup'])->name('groups.update');
        Route::delete('/groups/{group}', [PosController::class, 'destroyGroup'])->name('groups.destroy');
        Route::post('/groups/{group}/sync', [PosController::class, 'syncGroupProducts'])->name('groups.sync');
    });

    // Transaksi - semua role bisa lihat (operator lihat miliknya via shift)
    Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions.index');
    Route::get('/transactions/{transaction}', [TransactionController::class, 'show'])->name('transactions.show');

    // Owner-only routes
    Route::middleware(['role:owner'])->group(function () {

        // Manajemen Shift
        Route::prefix('shifts')->name('shifts.')->group(function () {
            Route::get('/', [ShiftController::class, 'index'])->name('index');
            Route::post('/open', [ShiftController::class, 'open'])->name('open');
            Route::post('/{shift}/close', [ShiftController::class, 'close'])->name('close');
            Route::get('/{shift}', [ShiftController::class, 'show'])->name('show');
        });

        // Produk
        Route::resource('products', ProductController::class)->except(['show']);
        Route::get('/products/export/{format}', [ProductController::class, 'export'])->name('products.export');

        // Kategori
        Route::prefix('categories')->name('categories.')->group(function () {
            Route::get('/', [CategoryController::class, 'index'])->name('index');
            Route::post('/', [CategoryController::class, 'store'])->name('store');
            Route::put('/{category}', [CategoryController::class, 'update'])->name('update');
            Route::delete('/{category}', [CategoryController::class, 'destroy'])->name('destroy');
        });

        // Manajemen Stok
        Route::prefix('stock')->name('stock.')->group(function () {
            Route::get('/', [StockController::class, 'index'])->name('index');
            Route::post('/adjust', [StockController::class, 'adjust'])->name('adjust');
        });

        // Cashflow
        Route::prefix('cashflow')->name('cashflow.')->group(function () {
            Route::get('/', [CashflowController::class, 'index'])->name('index');
            Route::get('/data', [CashflowController::class, 'getData'])->name('data');
            Route::get('/export', [CashflowController::class, 'export'])->name('export');
            Route::post('/', [CashflowController::class, 'store'])->name('store');
            Route::delete('/{cashflow}', [CashflowController::class, 'destroy'])->name('destroy');
        });

        // Laporan
        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('/sales', [ReportController::class, 'sales'])->name('sales');
            Route::get('/financial', [ReportController::class, 'financial'])->name('financial');
            Route::get('/shifts', [ReportController::class, 'shifts'])->name('shifts');
        });

        // Rekap Penjualan & Margin
        Route::get('/sales-report', [SalesController::class, 'index'])->name('sales.index');

        // Batalkan transaksi
        Route::post('/transactions/{transaction}/cancel', [TransactionController::class, 'cancel'])->name('transactions.cancel');
        Route::delete('/transactions/{transaction}', [TransactionController::class, 'destroy'])->name('transactions.destroy');

        // Manajemen Tim
        Route::prefix('team')->name('team.')->group(function () {
            Route::get('/', [TeamController::class, 'index'])->name('index');
            Route::post('/', [TeamController::class, 'store'])->name('store');
            Route::put('/{user}', [TeamController::class, 'update'])->name('update');
            Route::post('/{user}/toggle-active', [TeamController::class, 'toggleActive'])->name('toggle-active');
            Route::delete('/{user}', [TeamController::class, 'destroy'])->name('destroy');
        });

        // Pengaturan
        Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
        Route::put('/settings', [SettingController::class, 'update'])->name('settings.update');
    });
});
