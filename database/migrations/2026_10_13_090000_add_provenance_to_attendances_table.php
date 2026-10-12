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
            $table->string('source', 20)->default('device')->after('status');
            $table->string('corrected_by')->nullable()->after('source');
        });

        DB::table('attendances')->whereNull('source')->update(['source' => 'device']);
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropColumn(['source', 'corrected_by']);
        });
    }
};
