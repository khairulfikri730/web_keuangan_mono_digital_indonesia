<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('monthly_usages', function (Blueprint $table) {
            $table->string('expense_name')->nullable()->after('worksheet_id');
            $table->string('category')->default('operasional')->after('expense_name'); // operasional, variabel, lainnya
            $table->string('frequency')->default('bulanan')->after('category'); // bulanan, 2 harian, sekali beli
            $table->string('payment_method')->default('tunai')->after('usage_amount'); // tunai, qris, bank
            $table->string('status')->default('dibayar')->after('payment_method'); // dibayar, pending
            $table->date('expense_date')->nullable()->after('year');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('monthly_usages', function (Blueprint $table) {
            $table->dropColumn(['expense_name', 'category', 'frequency', 'payment_method', 'status', 'expense_date']);
        });
    }
};
