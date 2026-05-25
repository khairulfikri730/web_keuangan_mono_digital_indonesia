<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE shifts MODIFY COLUMN status ENUM('open', 'closed', 'pending_approval') DEFAULT 'open'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE shifts MODIFY COLUMN status ENUM('open', 'closed') DEFAULT 'open'");
    }
};
