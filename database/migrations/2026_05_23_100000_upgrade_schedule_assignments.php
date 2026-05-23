<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('schedule_assignments', function (Blueprint $table) {
            $table->string('status')->default('open')->after('notes'); // open, close
            $table->string('closed_by')->nullable()->after('status'); // nama penutup (dari user login)
            $table->string('closed_reason')->nullable()->after('closed_by'); // alasan tutup
            $table->unsignedBigInteger('original_crew_id')->nullable()->after('closed_reason'); // crew asli sebelum diganti
            $table->string('changed_by')->nullable()->after('original_crew_id'); // siapa yang mengganti
        });
    }

    public function down(): void
    {
        Schema::table('schedule_assignments', function (Blueprint $table) {
            $table->dropColumn(['status', 'closed_by', 'closed_reason', 'original_crew_id', 'changed_by']);
        });
    }
};
