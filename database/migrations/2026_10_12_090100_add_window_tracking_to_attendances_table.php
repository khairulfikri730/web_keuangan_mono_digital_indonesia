<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->date('date')->nullable()->after('type');
            $table->foreignId('schedule_shift_id')->nullable()->after('date')
                ->constrained('schedule_shifts')->nullOnDelete();
            $table->json('violations')->nullable()->after('status');
        });

        DB::table('attendances')->whereNull('date')->update(['date' => DB::raw('DATE(created_at)')]);

        Schema::table('attendances', function (Blueprint $table) {
            $table->date('date')->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropForeign(['schedule_shift_id']);
            $table->dropColumn(['date', 'schedule_shift_id', 'violations']);
        });
    }
};
