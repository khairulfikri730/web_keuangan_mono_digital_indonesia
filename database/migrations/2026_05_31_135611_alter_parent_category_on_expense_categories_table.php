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
        // Using raw SQL to avoid doctrine/dbal requirement for modifying enum columns
        \Illuminate\Support\Facades\DB::statement('ALTER TABLE expense_categories MODIFY parent_category VARCHAR(255) NOT NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverting back to enum could be destructive if new categories exist
        // \Illuminate\Support\Facades\DB::statement("ALTER TABLE expense_categories MODIFY parent_category ENUM('operasional', 'consumable', 'bahan_baku', 'variabel') NOT NULL");
    }
};
