<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('schedule_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('schedule_shift_id')->constrained()->cascadeOnDelete();
            $table->foreignId('schedule_crew_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->string('notes')->nullable();
            $table->timestamps();

            $table->unique(['schedule_shift_id', 'schedule_crew_id', 'date'], 'shift_crew_date_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('schedule_assignments');
    }
};
