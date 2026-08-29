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
        Schema::table('users', function (Blueprint $table) {
            $table->string('allowance_type')->default('none')->after('custom_rates'); // none, daily, monthly
            $table->integer('allowance_amount')->default(0)->after('allowance_type');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['allowance_type', 'allowance_amount']);
        });
    }
};
