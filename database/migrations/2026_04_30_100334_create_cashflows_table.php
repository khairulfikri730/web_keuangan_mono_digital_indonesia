<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cashflows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('restrict');
            $table->foreignId('shift_id')->nullable()->constrained()->onDelete('set null');
            $table->enum('type', ['income', 'expense']);
            $table->string('category'); // Penjualan, Modal, Gaji, Sewa, dll
            $table->string('description');
            $table->decimal('amount', 15, 2);
            $table->string('reference')->nullable(); // invoice number jika dari transaksi
            $table->date('transaction_date');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cashflows');
    }
};
