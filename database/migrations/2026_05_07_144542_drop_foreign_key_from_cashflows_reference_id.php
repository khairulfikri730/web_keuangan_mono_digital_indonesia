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
        Schema::table('cashflows', function (Blueprint $table) {
            // Kita perlu menghapus foreign key constraint agar reference_id bisa digunakan secara polimorfik
            // (merujuk ke transactions maupun monthly_usages)
            $table->dropForeign(['reference_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cashflows', function (Blueprint $table) {
            // Jika dikembalikan, kita asumsikan merujuk ke transactions lagi
            $table->foreign('reference_id')->references('id')->on('transactions')->onDelete('cascade');
        });
    }
};
