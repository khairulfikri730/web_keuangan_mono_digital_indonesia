<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('overtime_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('schedule_location_id')->nullable()->constrained('schedule_locations')->nullOnDelete();
            $table->foreignId('schedule_assignment_id')->nullable()->constrained('schedule_assignments')->nullOnDelete();
            $table->date('date');
            $table->dateTime('window_end_at');  // batas akhir jendela absen keluar
            $table->dateTime('check_out_at');    // waktu absen keluar sebenarnya
            $table->integer('minutes');          // durasi lembur
            $table->integer('rate_per_hour');    // tarif yang berlaku saat diajukan
            $table->integer('amount');           // nominal hasil hitungan
            $table->text('note')->nullable();    // catatan lembur dari crew
            $table->string('status')->default('pending'); // pending, approved, rejected
            $table->string('reviewed_by')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->unsignedBigInteger('payroll_entry_id')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'date', 'schedule_location_id'], 'overtime_user_date_location_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('overtime_requests');
    }
};
