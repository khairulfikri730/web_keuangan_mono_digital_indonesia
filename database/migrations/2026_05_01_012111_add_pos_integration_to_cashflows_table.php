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
            if (!Schema::hasColumn('cashflows', 'source')) {
                $table->string('source')->default('manual')->after('description');
            }
            if (!Schema::hasColumn('cashflows', 'reference_id')) {
                $table->unsignedBigInteger('reference_id')->nullable()->after('source');
                $table->foreign('reference_id')->references('id')->on('transactions')->onDelete('cascade');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cashflows', function (Blueprint $table) {
            if (Schema::hasColumn('cashflows', 'reference_id')) {
                $table->dropForeign(['reference_id']);
                $table->dropColumn('reference_id');
            }
            if (Schema::hasColumn('cashflows', 'source')) {
                $table->dropColumn('source');
            }
        });
    }
};
