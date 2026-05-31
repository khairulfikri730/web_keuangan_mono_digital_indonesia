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
        Schema::table('master_expense_categories', function (Blueprint $table) {
            $table->boolean('is_pos_hidden')->default(false)->after('color');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('master_expense_categories', function (Blueprint $table) {
            $table->dropColumn('is_pos_hidden');
        });
    }
};
