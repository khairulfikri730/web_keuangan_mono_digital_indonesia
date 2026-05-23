<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Cashflow;
use Illuminate\Support\Facades\DB;

echo "MEMBERSIHKAN & MENYELARASKAN DATA CASHFLOW...\n";
echo str_repeat("-", 80) . "\n";

DB::beginTransaction();
try {
    // 1. Penjualan -> Harus income
    $penjualan = Cashflow::where('category', 'Penjualan')->where('type', 'income')->update(['transaction_category' => 'income']);
    echo "Fixed Penjualan: $penjualan rows updated to 'income'\n";

    // 2. Modal Awal & Input Saldo & Transfer -> Harus adjustment
    $adjCategories = ['Modal Awal Kasir', 'Input Saldo Manual', 'Transfer Internal', 'Penyesuaian Saldo', 'Audit Kas', 'Koreksi Saldo'];
    $adjustments = Cashflow::whereIn('category', $adjCategories)->update(['transaction_category' => 'adjustment']);
    echo "Fixed Adjustments: $adjustments rows updated to 'adjustment'\n";

    // 3. Lain-lain yang type=expense tapi belum punya transaction_category yang benar
    // Jika tidak termasuk kategori di atas dan type=expense, maka itu business expense
    $expenses = Cashflow::where('type', 'expense')
        ->whereNotIn('category', $adjCategories)
        ->update(['transaction_category' => 'expense']);
    echo "Fixed Business Expenses: $expenses rows updated to 'expense'\n";
    
    // 4. Lain-lain yang type=income tapi belum punya transaction_category yang benar
    // (Manual income yang bukan Penjualan)
    $incomes = Cashflow::where('type', 'income')
        ->whereNotIn('category', array_merge(['Penjualan'], $adjCategories))
        ->update(['transaction_category' => 'income']);
    echo "Fixed Other Incomes: $incomes rows updated to 'income'\n";

    DB::commit();
    echo str_repeat("-", 80) . "\n";
    echo "DATA BERHASIL DISELARASKAN!\n";
} catch (\Exception $e) {
    DB::rollBack();
    echo "ERROR: " . $e->getMessage() . "\n";
}
