<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Batas jam dinding terakhir crew masih boleh absen keluar untuk sesi hari itu,
     * diisi per lokasi (Studio 00:00, Jamsit 02:00). Lewat jam itu hanya owner yang
     * boleh melengkapinya.
     */
    public function up(): void
    {
        Schema::table('schedule_locations', function (Blueprint $table) {
            $table->time('check_out_cutoff')->nullable()->after('radius');
        });

        DB::table('schedule_locations')->where('name', 'Studio')->update(['check_out_cutoff' => '00:00:00']);
        DB::table('schedule_locations')->where('name', 'Jamsit')->update(['check_out_cutoff' => '02:00:00']);
    }

    public function down(): void
    {
        Schema::table('schedule_locations', function (Blueprint $table) {
            $table->dropColumn('check_out_cutoff');
        });
    }
};
