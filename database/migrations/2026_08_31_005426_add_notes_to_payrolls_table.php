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
        Schema::table('payrolls', function (Blueprint $table) {
            $table->string('photographer_fee_note')->nullable()->after('photographer_fee');
            $table->string('overtime_fee_note')->nullable()->after('overtime_fee');
            $table->string('bonus_note')->nullable()->after('bonus');
            $table->string('deduction_note')->nullable()->after('deduction');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payrolls', function (Blueprint $table) {
            $table->dropColumn([
                'photographer_fee_note',
                'overtime_fee_note',
                'bonus_note',
                'deduction_note'
            ]);
        });
    }
};
