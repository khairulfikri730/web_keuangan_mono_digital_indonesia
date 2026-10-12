<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('schedule_shifts', function (Blueprint $table) {
            $table->time('check_in_start')->nullable()->after('end_time');
            $table->time('check_in_end')->nullable()->after('check_in_start');
            $table->time('check_out_start')->nullable()->after('check_in_end');
            $table->time('check_out_end')->nullable()->after('check_out_start');
        });
    }

    public function down(): void
    {
        Schema::table('schedule_shifts', function (Blueprint $table) {
            $table->dropColumn([
                'check_in_start',
                'check_in_end',
                'check_out_start',
                'check_out_end',
            ]);
        });
    }
};
