<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Fix status column to support 'pending' for piutang transactions
        \DB::statement("ALTER TABLE `transactions` MODIFY COLUMN `status` VARCHAR(20) NOT NULL DEFAULT 'completed'");
    }

    public function down(): void
    {
        \DB::statement("ALTER TABLE `transactions` MODIFY COLUMN `status` ENUM('completed','cancelled','refunded') NOT NULL DEFAULT 'completed'");
    }
};
