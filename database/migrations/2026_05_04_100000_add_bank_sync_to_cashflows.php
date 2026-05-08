<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cashflows', function (Blueprint $table) {
            // null = not applicable (cash/manual), pending = not yet in bank, synced = confirmed in bank
            $table->enum('bank_sync_status', ['pending', 'synced'])->nullable()->after('source');
        });

        // Existing bank records (pos_bank, transfer) from POS transactions = mark as synced (historical)
        // Only NEW records will be auto-set to pending by the listener
        DB::table('cashflows')
            ->whereIn('source', ['pos_bank', 'transfer'])
            ->whereNotNull('reference')
            ->update(['bank_sync_status' => 'synced']);

        // Manual bank entries (no reference / entered manually) = also synced
        DB::table('cashflows')
            ->whereIn('source', ['pos_bank', 'transfer'])
            ->whereNull('reference')
            ->update(['bank_sync_status' => 'synced']);
    }

    public function down(): void
    {
        Schema::table('cashflows', function (Blueprint $table) {
            $table->dropColumn('bank_sync_status');
        });
    }
};
