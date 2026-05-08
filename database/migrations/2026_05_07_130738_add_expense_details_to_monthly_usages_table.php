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
            $table->string('expense_type')->default('operasional')->after('expense_name');
            $table->string('supplier')->nullable()->after('expense_type');
            $table->integer('quantity')->default(1)->after('supplier');
            $table->string('unit')->nullable()->after('quantity');
            $table->decimal('unit_price', 15, 2)->default(0)->after('unit');
            $table->date('due_date')->nullable()->after('unit_price');
            $table->string('project_name')->nullable()->after('due_date');
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete()->after('project_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('monthly_usages', function (Blueprint $table) {
            $table->dropForeign(['product_id']);
            $table->dropColumn([
                'expense_type',
                'supplier',
                'quantity',
                'unit',
                'unit_price',
                'due_date',
                'project_name',
                'product_id',
            ]);
        });
    }
};
