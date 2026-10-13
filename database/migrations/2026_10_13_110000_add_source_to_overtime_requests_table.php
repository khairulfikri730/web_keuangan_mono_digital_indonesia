<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Membedakan lembur hasil hitungan sistem (ikut jam absen & jendela shift) dari
     * lembur yang diisi sendiri oleh owner, supaya yang pertama dapat dihitung ulang
     * saat jam absennya dikoreksi.
     */
    public function up(): void
    {
        Schema::table('overtime_requests', function (Blueprint $table) {
            $table->string('source', 10)->default('manual')->after('status');
        });

        DB::table('overtime_requests')
            ->where('note', 'Lembur terdeteksi otomatis dari absen keluar')
            ->update(['source' => 'auto']);
    }

    public function down(): void
    {
        Schema::table('overtime_requests', function (Blueprint $table) {
            $table->dropColumn('source');
        });
    }
};
