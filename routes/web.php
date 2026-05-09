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
    Route::middleware('permission:transactions')->group(function () {
        Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions.index');
        Route::get('/transactions/{transaction}', [TransactionController::class, 'show'])->name('transactions.show');
    });

    // Shift Management - permission based
    Route::middleware('permission:shifts')->prefix('shifts')->name('shifts.')->group(function () {
        Route::get('/', [ShiftController::class, 'index'])->name('index');
        Route::post('/open', [ShiftController::class, 'open'])->name('open');
        Route::post('/{shift}/close', [ShiftController::class, 'close'])->name('close');
        Route::post('/{shift}/cashout', [ShiftController::class, 'cashOut'])->name('cashout');
        Route::put('/{shift}', [ShiftController::class, 'update'])->name('update');
        Route::delete('/{shift}', [ShiftController::class, 'destroy'])->name('destroy');
        Route::get('/{shift}', [ShiftController::class, 'show'])->name('show');
        Route::get('/{shift}/summary', [ShiftController::class, 'getSummary'])->name('summary');
    });

    // Produk - permission based
    Route::middleware('permission:products')->group(function () {
        Route::resource('products', ProductController::class)->except(['show']);
        Route::get('/products/export/{format}', [ProductController::class, 'export'])->name('products.export');
    });

    // Kategori - permission based
    Route::middleware('permission:categories')->prefix('categories')->name('categories.')->group(function () {
        Route::get('/', [CategoryController::class, 'index'])->name('index');
        Route::post('/', [CategoryController::class, 'store'])->name('store');
        Route::put('/{category}', [CategoryController::class, 'update'])->name('update');
        Route::delete('/{category}', [CategoryController::class, 'destroy'])->name('destroy');
    });

    // Stok - permission based
    Route::middleware('permission:stock')->prefix('stock')->name('stock.')->group(function () {
        Route::get('/', [StockController::class, 'index'])->name('index');
        Route::post('/adjust', [StockController::class, 'adjust'])->name('adjust');
        Route::delete('/mutation/{mutation}', [StockController::class, 'destroy'])->name('destroy');
    });

    // Cashflow - permission based
    Route::middleware('permission:cashflow')->prefix('cashflow')->name('cashflow.')->group(function () {
        Route::get('/', [CashflowController::class, 'index'])->name('index');
        Route::get('/data', [CashflowController::class, 'getData'])->name('data');
        Route::get('/export', [CashflowController::class, 'export'])->name('export');
        Route::post('/', [CashflowController::class, 'store'])->name('store');
        Route::post('/transfer', [CashflowController::class, 'transfer'])->name('transfer');
        Route::post('/update-target', [CashflowController::class, 'updateTarget'])->name('update-target');
        Route::post('/sync-bank', [CashflowController::class, 'syncBank'])->name('sync-bank');
        Route::post('/sync-laci', [CashflowController::class, 'syncLaci'])->name('sync-laci');
        Route::put('/{cashflow}', [CashflowController::class, 'update'])->name('update');
        Route::post('/quick-store', [CashflowController::class, 'storeQuick'])->name('quick-store');
        Route::delete('/{cashflow}', [CashflowController::class, 'destroy'])->name('destroy');
    });

    // Modal Usaha & Pemakaian Bulanan (Owner / Financial access)
    Route::middleware('permission:reports_financial')->group(function () {
        Route::post('capitals/import', [CapitalController::class, 'import'])->name('capitals.import');
        Route::get('capitals/template', function () {
            $path = public_path('templates/Template_Import_Modal.xlsx');
            if (!file_exists($path)) {
                // Return dummy response since creating real excel here is complex, we just create an empty file if needed or user creates it.
                // Or better, generate dynamically.
                $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
                $sheet = $spreadsheet->getActiveSheet();
                $sheet->setCellValue('A1', 'Nama Item');
                $sheet->setCellValue('B1', 'Tipe');
                $sheet->setCellValue('C1', 'Harga Satuan');
                $sheet->setCellValue('D1', 'Jumlah');
                $sheet->setCellValue('E1', 'Satuan');
                
                $sheet->setCellValue('A2', 'Kamera Canon 5D');
                $sheet->setCellValue('B2', 'Aset');
                $sheet->setCellValue('C2', '15000000');
                $sheet->setCellValue('D2', '1');
                $sheet->setCellValue('E2', 'Pcs');
                
                $sheet->setCellValue('A3', 'Kertas HVS A4');
                $sheet->setCellValue('B3', 'Bahan Baku');
                $sheet->setCellValue('C3', '55000');
                $sheet->setCellValue('D3', '10');
                $sheet->setCellValue('E3', 'Rim');

                $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
                header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                header('Content-Disposition: attachment; filename="Template_Import_Modal.xlsx"');
                $writer->save('php://output');
                exit;
            }
            return response()->download($path);
        })->name('capitals.template');
        Route::resource('capitals', CapitalController::class);
        Route::resource('monthly_expenses', MonthlyExpenseController::class);
        Route::resource('expense_categories', ExpenseCategoryController::class);
        
        // Invoice Generator Routes
        Route::resource('invoices', \App\Http\Controllers\InvoiceController::class);
        Route::get('invoices/{invoice}/pdf', [\App\Http\Controllers\InvoiceController::class, 'downloadPdf'])->name('invoices.pdf');
        Route::post('invoices/{invoice}/payments', [\App\Http\Controllers\InvoiceController::class, 'addPayment'])->name('invoices.payments.store');

        Route::resource('expense_categories', ExpenseCategoryController::class);
    });

    // Laporan - permission based
    Route::middleware('permission:sales')->get('/reports/sales', [ReportController::class, 'sales'])->name('sales.index');
    Route::middleware('permission:reports_financial')->get('/reports/financial', [ReportController::class, 'financial'])->name('reports.financial');
    Route::middleware('permission:reports_shifts')->get('/reports/shifts', [ReportController::class, 'shifts'])->name('reports.shifts');
    Route::post('/reports/export-pdf', [ReportController::class, 'exportPdf'])->name('reports.export-pdf');
    Route::post('/reports/export-excel', [ReportController::class, 'exportExcel'])->name('reports.export-excel');
    Route::post('/reports/export-csv', [ReportController::class, 'exportCsv'])->name('reports.export-csv');

    // Owner-only routes
    Route::middleware(['role:owner'])->group(function () {
        // Batalkan / hapus transaksi
        Route::post('/transactions/{transaction}/cancel', [TransactionController::class, 'cancel'])->name('transactions.cancel');
        Route::delete('/transactions/{transaction}', [TransactionController::class, 'destroy'])->name('transactions.destroy');

        // Bayar piutang
        Route::post('/transactions/{transaction}/pay', [TransactionController::class, 'payPiutang'])->name('transactions.pay');

        // Manajemen Tim
        Route::middleware('permission:team')->prefix('team')->name('team.')->group(function () {
            Route::get('/', [TeamController::class, 'index'])->name('index');
            Route::post('/', [TeamController::class, 'store'])->name('store');
            Route::put('/{user}', [TeamController::class, 'update'])->name('update');
            Route::post('/{user}/toggle-active', [TeamController::class, 'toggleActive'])->name('toggle-active');
            Route::delete('/{user}', [TeamController::class, 'destroy'])->name('destroy');
        });

        // Pengaturan
        Route::middleware('permission:settings')->group(function () {
            Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
            Route::put('/settings', [SettingController::class, 'update'])->name('settings.update');
            Route::post('/settings/test-drawer', [SettingController::class, 'testDrawer'])->name('settings.test-drawer');
        });
    });
});
