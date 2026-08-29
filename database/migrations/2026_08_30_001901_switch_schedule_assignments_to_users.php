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
        Schema::disableForeignKeyConstraints();
        
        \DB::table('schedule_assignments')->delete();

        Schema::table('schedule_assignments', function (Blueprint $table) {
            $table->dropColumn('schedule_crew_id');

            if (Schema::hasColumn('schedule_assignments', 'original_crew_id')) {
                $table->dropColumn('original_crew_id');
            }

            $table->foreignId('user_id')->after('schedule_shift_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->foreignId('original_user_id')->after('closed_at_time')->nullable()->constrained('users')->onDelete('set null');
        });

        Schema::dropIfExists('schedule_crews');
        
        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        // No simple reverse since we dropped a table and truncated data
    }
};
