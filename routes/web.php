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
use App\Http\Controllers\WorksheetController;
use App\Http\Controllers\CapitalController;
use App\Http\Controllers\MonthlyExpenseController;
use App\Http\Controllers\ExpenseCategoryController;
use App\Http\Controllers\AccountController;
use Illuminate\Support\Facades\Route;

// Auth routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');

    Route::get('/forgot-password', [AuthController::class, 'showForgot'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'sendOtp'])->name('password.otp.send');
    Route::get('/verify-otp', [AuthController::class, 'showVerifyOtp'])->name('password.otp.verify');
    Route::post('/verify-otp', [AuthController::class, 'verifyOtp'])->name('password.otp.check');
    Route::get('/reset-password', [AuthController::class, 'showReset'])->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

Route::middleware('auth')->prefix('account')->name('account.')->group(function () {
    Route::get('/', [AccountController::class, 'profile'])->name('profile');
    Route::put('/', [AccountController::class, 'updateProfile'])->name('profile.update');
    Route::post('/avatar', [AccountController::class, 'uploadAvatar'])->name('avatar');
    Route::get('/password', [AccountController::class, 'showPassword'])->name('password');
    Route::put('/password', [AccountController::class, 'updatePassword'])->name('password.update');


});

// Redirect root to dashboard
Route::get('/', fn() => redirect()->route('dashboard'));

// Authenticated routes
Route::middleware(['auth'])->group(function () {

    // Dashboard - semua role
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Worksheets
    Route::post('/worksheets/switch', [WorksheetController::class, 'switch'])->name('worksheets.switch');
    Route::post('/worksheets', [WorksheetController::class, 'store'])->name('worksheets.store');
    Route::put('/worksheets/{worksheet}', [WorksheetController::class, 'update'])->name('worksheets.update');
    Route::delete('/worksheets/{worksheet}', [WorksheetController::class, 'destroy'])->name('worksheets.destroy');

    // POS - permission based
    Route::middleware('permission:pos')->prefix('pos')->name('pos.')->group(function () {
        Route::get('/', [PosController::class, 'index'])->name('index');
        Route::get('/products', [PosController::class, 'getProducts'])->name('products');
        Route::post('/checkout', [PosController::class, 'checkout'])->name('checkout');
        Route::post('/print-receipt/{transaction}', [PosController::class, 'printReceipt'])->name('print-receipt');
        Route::get('/receipt/{transaction}', [PosController::class, 'receipt'])->name('receipt');
        Route::get('/receipt-test', [PosController::class, 'testReceipt'])->name('receipt.test');
        Route::post('/expense', [PosController::class, 'storeExpense'])->name('expense');

        // POS Groups
        Route::post('/groups/sync-all', [PosController::class, 'syncAllGroups'])->name('groups.syncAll');
        Route::post('/groups', [PosController::class, 'storeGroup'])->name('groups.store');
        Route::put('/groups/{group}', [PosController::class, 'updateGroup'])->name('groups.update');
        Route::delete('/groups/{group}', [PosController::class, 'destroyGroup'])->name('groups.destroy');
        Route::post('/groups/{group}/sync', [PosController::class, 'syncGroupProducts'])->name('groups.sync');
    });

    // Transaksi - permission based
    Route::middleware('permission:transactions.view')->group(function () {
        Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions.index');
        Route::get('/transactions/{transaction}', [TransactionController::class, 'show'])->name('transactions.show');
    });

    // Shift Management - permission based
    Route::prefix('shifts')->name('shifts.')->group(function () {
        // Access to index and summary allowed if can view OR manage
        Route::get('/', [ShiftController::class, 'index'])->middleware('permission:shifts.view,shifts.manage')->name('index');
        Route::get('/{shift}/summary', [ShiftController::class, 'getSummary'])->middleware('permission:shifts.view,shifts.manage')->name('summary');
        
        // Specific route for opening shift (GET) to prevent collision with {shift}
        Route::get('/open', function() {
            return redirect()->route('shifts.index', ['open' => 1]);
        })->middleware('permission:shifts.manage')->name('open-form');

        // Viewing specific shift detail only if has view permission
        Route::get('/{shift}', [ShiftController::class, 'show'])->middleware('permission:shifts.view')->name('show');
        
        // Managing actions require manage permission
        Route::middleware('permission:shifts.manage')->group(function() {
            Route::post('/open', [ShiftController::class, 'open'])->name('open');
            Route::post('/{shift}/close', [ShiftController::class, 'close'])->name('close');
            Route::post('/{shift}/approve', [ShiftController::class, 'approve'])->name('approve');
            Route::post('/{shift}/cashout', [ShiftController::class, 'cashOut'])->name('cashout');
            Route::put('/{shift}', [ShiftController::class, 'update'])->name('update');
            Route::delete('/{shift}', [ShiftController::class, 'destroy'])->name('destroy');
        });
    });

    // Produk - permission based
    Route::group([], function () {
        Route::get('/products', [ProductController::class, 'index'])->middleware('permission:products.view,products.create,products.edit,products.delete')->name('products.index');
        Route::get('/products/export/{format}', [ProductController::class, 'export'])->middleware('permission:products.view')->name('products.export');
        
        Route::middleware('permission:products.create')->group(function() {
            Route::get('/products/create', [ProductController::class, 'create'])->name('products.create');
            Route::post('/products', [ProductController::class, 'store'])->name('products.store');
            Route::post('/products/import', [ProductController::class, 'import'])->name('products.import');
            Route::get('/products/import/template', [ProductController::class, 'downloadTemplate'])->name('products.import.template');
        });
        
        Route::middleware('permission:products.edit')->group(function() {
            Route::get('/products/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');
            Route::put('/products/{product}', [ProductController::class, 'update'])->name('products.update');
        });
        
        Route::middleware('permission:products.delete')->group(function() {
            Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');
        });
    });


    // Kategori - permission based
    Route::prefix('categories')->name('categories.')->group(function () {
        Route::get('/', [CategoryController::class, 'index'])->middleware('permission:categories.view,categories.create,categories.edit,categories.delete')->name('index');
        Route::post('/', [CategoryController::class, 'store'])->middleware('permission:categories.create')->name('store');
        Route::put('/{category}', [CategoryController::class, 'update'])->middleware('permission:categories.edit')->name('update');
        Route::delete('/{category}', [CategoryController::class, 'destroy'])->middleware('permission:categories.delete')->name('destroy');
    });

    // Stok - permission based
    Route::prefix('stock')->name('stock.')->group(function () {
        Route::get('/', [StockController::class, 'index'])->middleware('permission:stock.view,stock.edit')->name('index');
        Route::get('/history', [StockController::class, 'history'])->middleware('permission:stock.view,stock.edit')->name('history');
        Route::post('/adjust', [StockController::class, 'adjust'])->middleware('permission:stock.edit')->name('adjust');
        Route::delete('/mutation/{mutation}', [StockController::class, 'destroy'])->middleware('permission:stock.edit')->name('destroy');
    });

    // Cashflow - permission based
    Route::middleware('permission:cashflow.view')->prefix('cashflow')->name('cashflow.')->group(function () {
        Route::get('/', [CashflowController::class, 'index'])->name('index');
        Route::get('/data', [CashflowController::class, 'getData'])->name('data');
        Route::get('/export', [CashflowController::class, 'export'])->name('export');
        
        Route::middleware('permission:cashflow.create')->group(function() {
            Route::post('/', [CashflowController::class, 'store'])->name('store');
            Route::post('/transfer', [CashflowController::class, 'transfer'])->name('transfer');
            Route::post('/quick-store', [CashflowController::class, 'storeQuick'])->name('quick-store');
        });
        
        Route::middleware('permission:settings')->group(function() {
            Route::post('/update-target', [CashflowController::class, 'updateTarget'])->name('update-target');
            Route::post('/update-capital', [CashflowController::class, 'updateCapital'])->name('update-capital');
            Route::post('/sync-bank', [CashflowController::class, 'syncBank'])->name('sync-bank');
            Route::post('/sync-laci', [CashflowController::class, 'syncLaci'])->name('sync-laci');
        });
        
        Route::put('/{cashflow}', [CashflowController::class, 'update'])->middleware('permission:cashflow.create')->name('update');
        Route::delete('/{cashflow}', [CashflowController::class, 'destroy'])->middleware('permission:cashflow.delete')->name('destroy');
    });

    // Modal Usaha & Pemakaian Bulanan (Owner / Financial access)
    Route::middleware('permission:reports_financial,capitals.view,monthly_expenses.view,expense_categories.view')->group(function () {
        
        Route::middleware('permission:capitals.view')->group(function() {
            Route::get('capitals', [CapitalController::class, 'index'])->name('capitals.index');
            Route::get('capitals/{capital}', [CapitalController::class, 'show'])->name('capitals.show');
            Route::middleware('permission:capitals.manage')->group(function() {
                Route::get('capitals/create', [CapitalController::class, 'create'])->name('capitals.create');
                Route::post('capitals', [CapitalController::class, 'store'])->name('capitals.store');
                Route::get('capitals/{capital}/edit', [CapitalController::class, 'edit'])->name('capitals.edit');
                Route::put('capitals/{capital}', [CapitalController::class, 'update'])->name('capitals.update');
                Route::delete('capitals/{capital}', [CapitalController::class, 'destroy'])->name('capitals.destroy');
                Route::post('capitals/import', [CapitalController::class, 'import'])->name('capitals.import');
                Route::get('capitals/template', [CapitalController::class, 'template'])->name('capitals.template');
            });
        });

        Route::middleware('permission:monthly_expenses.view')->group(function() {
            Route::get('monthly_expenses', [MonthlyExpenseController::class, 'index'])->name('monthly_expenses.index');
            Route::middleware('permission:monthly_expenses.manage')->group(function() {
                Route::get('monthly_expenses/create', [MonthlyExpenseController::class, 'create'])->name('monthly_expenses.create');
                Route::post('monthly_expenses', [MonthlyExpenseController::class, 'store'])->name('monthly_expenses.store');
                Route::get('monthly_expenses/{monthly_expense}/edit', [MonthlyExpenseController::class, 'edit'])->name('monthly_expenses.edit');
                Route::put('monthly_expenses/{monthly_expense}', [MonthlyExpenseController::class, 'update'])->name('monthly_expenses.update');
                Route::delete('monthly_expenses/{monthly_expense}', [MonthlyExpenseController::class, 'destroy'])->name('monthly_expenses.destroy');
            });
        });

        Route::middleware('permission:expense_categories.view')->group(function() {
            Route::get('expense_categories', [ExpenseCategoryController::class, 'index'])->name('expense_categories.index');
            Route::middleware('permission:expense_categories.manage')->group(function() {
                Route::post('expense_categories', [ExpenseCategoryController::class, 'store'])->name('expense_categories.store');
                Route::put('expense_categories/{expense_category}', [ExpenseCategoryController::class, 'update'])->name('expense_categories.update');
                Route::delete('expense_categories/{expense_category}', [ExpenseCategoryController::class, 'destroy'])->name('expense_categories.destroy');
            });
        });
    });

    // Invoice Generator Routes
    Route::middleware('permission:invoices.view')->group(function() {
        Route::get('invoices', [\App\Http\Controllers\InvoiceController::class, 'index'])->name('invoices.index');
        
        Route::middleware('permission:invoices.create')->group(function() {
            Route::get('invoices/create', [\App\Http\Controllers\InvoiceController::class, 'create'])->name('invoices.create');
            Route::post('invoices', [\App\Http\Controllers\InvoiceController::class, 'store'])->name('invoices.store');
            Route::get('invoices/{invoice}/edit', [\App\Http\Controllers\InvoiceController::class, 'edit'])->name('invoices.edit');
            Route::put('invoices/{invoice}', [\App\Http\Controllers\InvoiceController::class, 'update'])->name('invoices.update');
            Route::post('invoices/{invoice}/payments', [\App\Http\Controllers\InvoiceController::class, 'addPayment'])->name('invoices.payments.store');
        });

        Route::get('invoices/{invoice}', [\App\Http\Controllers\InvoiceController::class, 'show'])->name('invoices.show');
        Route::get('invoices/{invoice}/pdf', [\App\Http\Controllers\InvoiceController::class, 'downloadPdf'])->name('invoices.pdf');
        Route::delete('invoices/{invoice}', [\App\Http\Controllers\InvoiceController::class, 'destroy'])->middleware('permission:invoices.delete')->name('invoices.destroy');
    });

    // Laporan - permission based
    Route::middleware('permission:sales.view')->get('/reports/sales', [ReportController::class, 'sales'])->name('sales.index');
    Route::middleware('permission:reports_financial')->get('/reports/financial', [ReportController::class, 'financial'])->name('reports.financial');
    Route::middleware('permission:reports_shifts')->get('/reports/shifts', [ReportController::class, 'shifts'])->name('reports.shifts');
    Route::post('/reports/export-pdf', [ReportController::class, 'exportPdf'])->name('reports.export-pdf');
    Route::post('/reports/export-excel', [ReportController::class, 'exportExcel'])->name('reports.export-excel');
    Route::post('/reports/export-csv', [ReportController::class, 'exportCsv'])->name('reports.export-csv');

    // Transaksi Actions (Protected by granular permissions)
    Route::post('/transactions/{transaction}/cancel', [TransactionController::class, 'cancel'])->middleware('permission:transactions.edit')->name('transactions.cancel');
    Route::delete('/transactions/{transaction}', [TransactionController::class, 'destroy'])->middleware('permission:transactions.delete')->name('transactions.destroy');

    // Bayar piutang (Protected by granular permission)
    Route::post('/transactions/{transaction}/pay', [TransactionController::class, 'payPiutang'])->middleware('permission:transactions.edit')->name('transactions.pay');

    // Manajemen Tim
    Route::prefix('team')->name('team.')->group(function () {
        Route::get('/', [TeamController::class, 'index'])->middleware('permission:team.view,team.manage')->name('index');
        Route::post('/', [TeamController::class, 'store'])->middleware('permission:team.manage')->name('store');
        Route::put('/{user}', [TeamController::class, 'update'])->middleware('permission:team.manage')->name('update');
        Route::post('/{user}/toggle-active', [TeamController::class, 'toggleActive'])->middleware('permission:team.manage')->name('toggle-active');
        Route::delete('/{user}', [TeamController::class, 'destroy'])->middleware('permission:team.manage')->name('destroy');
    });

    // Pengaturan
    Route::middleware('permission:settings')->group(function () {
        Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
        Route::put('/settings', [SettingController::class, 'update'])->name('settings.update');
        Route::put('/settings/targets', [SettingController::class, 'updateTargets'])->name('settings.targets');
        Route::post('/settings/test-drawer', [SettingController::class, 'testDrawer'])->name('settings.test-drawer');
        Route::post('/settings/reset', [SettingController::class, 'resetData'])->name('settings.reset');
    });

    // Jadwal Kerja (Independent Schedule System)
    Route::prefix('schedules')->name('schedules.')->group(function () {
        Route::get('/', [\App\Http\Controllers\ScheduleController::class, 'index'])->middleware('permission:schedules.view,schedules.manage')->name('index');
        Route::get('/poster', [\App\Http\Controllers\ScheduleController::class, 'poster'])->middleware('permission:schedules.view,schedules.manage')->name('poster');
        
        Route::middleware('permission:schedules.manage')->group(function () {
            // Locations
            Route::post('/locations', [\App\Http\Controllers\ScheduleController::class, 'storeLocation'])->name('locations.store');
            Route::put('/locations/{location}', [\App\Http\Controllers\ScheduleController::class, 'updateLocation'])->name('locations.update');
            Route::delete('/locations/{location}', [\App\Http\Controllers\ScheduleController::class, 'destroyLocation'])->name('locations.destroy');
            
            // Crews
            Route::post('/crews', [\App\Http\Controllers\ScheduleController::class, 'storeCrew'])->name('crews.store');
            Route::put('/crews/{crew}', [\App\Http\Controllers\ScheduleController::class, 'updateCrew'])->name('crews.update');
            Route::post('/crews/{crew}/toggle', [\App\Http\Controllers\ScheduleController::class, 'toggleCrew'])->name('crews.toggle');
            Route::delete('/crews/{crew}', [\App\Http\Controllers\ScheduleController::class, 'destroyCrew'])->name('crews.destroy');
            
            // Shifts
            Route::post('/shifts', [\App\Http\Controllers\ScheduleController::class, 'storeShift'])->name('shifts.store');
            Route::put('/shifts/{shift}', [\App\Http\Controllers\ScheduleController::class, 'updateShift'])->name('shifts.update');
            Route::delete('/shifts/{shift}', [\App\Http\Controllers\ScheduleController::class, 'destroyShift'])->name('shifts.destroy');
            
            // Assignments
            Route::post('/assignments', [\App\Http\Controllers\ScheduleController::class, 'storeAssignment'])->name('assignments.store');
            Route::delete('/assignments/{assignment}', [\App\Http\Controllers\ScheduleController::class, 'destroyAssignment'])->name('assignments.destroy');
            Route::post('/assignments/bulk', [\App\Http\Controllers\ScheduleController::class, 'bulkAssign'])->name('assignments.bulk');
            Route::post('/assignments/weekly-bulk', [\App\Http\Controllers\ScheduleController::class, 'weeklyBulkAssign'])->name('assignments.weekly-bulk');
            
            // Close / Reopen / Change
            Route::post('/assignments/{assignment}/close', [\App\Http\Controllers\ScheduleController::class, 'closeAssignment'])->name('assignments.close');
            Route::post('/assignments/{assignment}/reopen', [\App\Http\Controllers\ScheduleController::class, 'reopenAssignment'])->name('assignments.reopen');
            Route::post('/assignments/{assignment}/change', [\App\Http\Controllers\ScheduleController::class, 'changeAssignment'])->name('assignments.change');
        });
    });
});




