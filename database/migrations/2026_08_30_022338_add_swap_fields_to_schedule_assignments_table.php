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
        Schema::table('schedule_assignments', function (Blueprint $table) {
            $table->unsignedBigInteger('swap_requested_to')->nullable()->after('original_user_id');
            $table->string('swap_status')->nullable()->after('swap_requested_to'); // pending, rejected
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('schedule_assignments', function (Blueprint $table) {
            $table->dropColumn(['swap_requested_to', 'swap_status']);
        });
    }
};
