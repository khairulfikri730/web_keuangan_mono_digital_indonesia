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
        // Pivot table for users and worksheets
        Schema::create('worksheet_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('worksheet_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('worksheet_id')->nullable()->constrained()->nullOnDelete();
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->foreignId('worksheet_id')->nullable()->constrained()->nullOnDelete();
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->foreignId('worksheet_id')->nullable()->constrained()->nullOnDelete();
        });

        Schema::table('cashflows', function (Blueprint $table) {
            $table->foreignId('worksheet_id')->nullable()->constrained()->nullOnDelete();
        });

        Schema::table('shifts', function (Blueprint $table) {
            $table->foreignId('worksheet_id')->nullable()->constrained()->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shifts', function (Blueprint $table) {
            $table->dropForeign(['worksheet_id']);
            $table->dropColumn('worksheet_id');
        });

        Schema::table('cashflows', function (Blueprint $table) {
            $table->dropForeign(['worksheet_id']);
            $table->dropColumn('worksheet_id');
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->dropForeign(['worksheet_id']);
            $table->dropColumn('worksheet_id');
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->dropForeign(['worksheet_id']);
            $table->dropColumn('worksheet_id');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['worksheet_id']);
            $table->dropColumn('worksheet_id');
        });

        Schema::dropIfExists('worksheet_user');
    }
};
