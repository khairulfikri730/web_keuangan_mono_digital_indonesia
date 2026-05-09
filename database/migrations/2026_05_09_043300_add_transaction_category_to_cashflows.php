<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cashflows', function (Blueprint $table) {
            $table->enum('transaction_category', ['income', 'expense', 'adjustment'])->default('expense')->after('type');
        });

        // Backfill data
        // 1. Sales/Penjualan -> income
        DB::table('cashflows')->where('category', 'Penjualan')->update(['transaction_category' => 'income']);
        
        // 2. Input Saldo Manual -> adjustment
        DB::table('cashflows')->where('category', 'Input Saldo Manual')->update(['transaction_category' => 'adjustment']);
        
        // 3. Modal Awal Kasir -> adjustment
        DB::table('cashflows')->where('category', 'Modal Awal Kasir')->update(['transaction_category' => 'adjustment']);
        
        // 4. Transfer Internal -> adjustment (because it's just moving money)
        DB::table('cashflows')->where('category', 'Transfer Internal')->update(['transaction_category' => 'adjustment']);
        
        // 5. Default income others -> income
        DB::table('cashflows')->where('type', 'income')
            ->whereNotIn('category', ['Penjualan', 'Input Saldo Manual', 'Modal Awal Kasir', 'Transfer Internal'])
            ->update(['transaction_category' => 'income']);
            
        // 6. Default expense others -> expense
        DB::table('cashflows')->where('type', 'expense')
            ->whereNotIn('category', ['Transfer Internal', 'Input Saldo Manual'])
            ->update(['transaction_category' => 'expense']);
    }

    public function down(): void
    {
        Schema::table('cashflows', function (Blueprint $table) {
            $table->dropColumn('transaction_category');
        });
    }
};
