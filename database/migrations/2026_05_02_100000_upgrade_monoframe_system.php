<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Shifts: expected cash & discrepancy
        Schema::table('shifts', function (Blueprint $table) {
            $table->decimal('expected_cash', 15, 2)->nullable()->after('closing_cash');
            $table->decimal('discrepancy', 15, 2)->nullable()->after('expected_cash');
            $table->decimal('cash_sales', 15, 2)->default(0)->after('discrepancy');
            $table->decimal('bank_sales', 15, 2)->default(0)->after('cash_sales');
            $table->decimal('cash_expenses', 15, 2)->default(0)->after('bank_sales');
        });

        // 2. Transactions: piutang support
        Schema::table('transactions', function (Blueprint $table) {
            $table->decimal('paid_so_far', 15, 2)->default(0)->after('change_amount');
        });

        // 3. Payments table for partial piutang
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaction_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('restrict');
            $table->decimal('amount', 15, 2);
            $table->string('payment_method'); // cash, transfer, qris, debit
            $table->string('notes')->nullable();
            $table->timestamps();
        });

        // 4. Users: expand role enum, add permissions
        \DB::statement("ALTER TABLE `users` MODIFY COLUMN `role` VARCHAR(20) NOT NULL DEFAULT 'kasir'");
        Schema::table('users', function (Blueprint $table) {
            $table->json('permissions')->nullable()->after('role');
        });

        // Update existing operator roles to kasir
        \DB::table('users')->where('role', 'operator')->update(['role' => 'kasir']);

        // 5. Transactions: expand payment_method to support piutang
        \DB::statement("ALTER TABLE `transactions` MODIFY COLUMN `payment_method` VARCHAR(20) NOT NULL DEFAULT 'cash'");
    }

    public function down(): void
    {
        Schema::table('shifts', function (Blueprint $table) {
            $table->dropColumn(['expected_cash', 'discrepancy', 'cash_sales', 'bank_sales', 'cash_expenses']);
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn('paid_so_far');
        });

        Schema::dropIfExists('payments');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('permissions');
        });

        \DB::table('users')->where('role', 'kasir')->update(['role' => 'operator']);
    }
};
