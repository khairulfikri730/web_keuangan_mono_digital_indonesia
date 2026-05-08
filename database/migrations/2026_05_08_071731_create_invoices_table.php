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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique();
            $table->date('date');
            $table->date('due_date')->nullable();
            
            // Business Info
            $table->string('business_name')->default('MONOFRAME STUDIO');
            $table->string('business_email')->nullable();
            $table->string('business_phone')->nullable();
            $table->text('business_address')->nullable();
            $table->string('business_logo')->nullable();
            
            // Client Info
            $table->string('client_name');
            $table->string('client_company')->nullable();
            $table->string('client_phone')->nullable();
            $table->string('client_email')->nullable();
            $table->text('client_address')->nullable();
            
            // Financials
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('discount', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->decimal('paid_amount', 15, 2)->default(0);
            
            $table->enum('status', ['pending', 'partial', 'paid', 'overdue'])->default('pending');
            $table->text('notes')->nullable();
            
            $table->foreignId('worksheet_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('created_by')->constrained('users');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
