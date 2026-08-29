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
        Schema::create('payrolls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('period', 7); // e.g. "2026-08"
            
            // Komponen Tambahan
            $table->integer('photographer_fee')->default(0);
            $table->integer('overtime_fee')->default(0);
            $table->integer('bonus')->default(0);
            $table->integer('deduction')->default(0); // Potongan / Kasbon
            
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->unique(['user_id', 'period']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payrolls');
    }
};
