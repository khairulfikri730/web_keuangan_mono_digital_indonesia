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
        // Hapus perilaku ON UPDATE CURRENT_TIMESTAMP dari kolom opened_at
        \Illuminate\Support\Facades\DB::statement("ALTER TABLE shifts MODIFY opened_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP");

        // Perbaiki data shift yang sudah terlanjur salah (kembalikan opened_at sama dengan created_at)
        \Illuminate\Support\Facades\DB::statement("UPDATE shifts SET opened_at = created_at WHERE created_at IS NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shifts', function (Blueprint $table) {
            //
        });
    }
};
