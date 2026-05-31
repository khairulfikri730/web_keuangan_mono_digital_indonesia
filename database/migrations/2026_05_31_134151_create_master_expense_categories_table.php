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
        Schema::create('master_expense_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('worksheet_id')->nullable()->constrained('worksheets')->onDelete('cascade');
            $table->string('name');
            $table->string('color')->default('blue'); // to store tailwind color prefix like blue, emerald, purple, amber
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_expense_categories');
    }
};
