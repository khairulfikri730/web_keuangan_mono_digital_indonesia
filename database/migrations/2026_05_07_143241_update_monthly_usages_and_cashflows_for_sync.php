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
            if (!Schema::hasColumn('monthly_usages', 'expense_category_id')) {
                $table->foreignId('expense_category_id')->nullable()->after('sub_category')->constrained('expense_categories')->onDelete('set null');
            }
            if (!Schema::hasColumn('monthly_usages', 'sync_status')) {
                $table->enum('sync_status', ['synced', 'pending', 'failed'])->default('synced')->after('status');
            }
        });

        Schema::table('cashflows', function (Blueprint $table) {
            if (!Schema::hasColumn('cashflows', 'reference_id')) {
                $table->unsignedBigInteger('reference_id')->nullable()->after('description');
            }
            if (!Schema::hasColumn('cashflows', 'reference_type')) {
                $table->string('reference_type')->nullable()->after('reference_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('monthly_usages', function (Blueprint $table) {
            if (Schema::hasColumn('monthly_usages', 'expense_category_id')) {
                $table->dropForeign(['expense_category_id']);
                $table->dropColumn(['expense_category_id']);
            }
            if (Schema::hasColumn('monthly_usages', 'sync_status')) {
                $table->dropColumn(['sync_status']);
            }
        });

        Schema::table('cashflows', function (Blueprint $table) {
            if (Schema::hasColumn('cashflows', 'reference_id')) {
                $table->dropColumn('reference_id');
            }
            if (Schema::hasColumn('cashflows', 'reference_type')) {
                $table->dropColumn('reference_type');
            }
        });
    }
};
