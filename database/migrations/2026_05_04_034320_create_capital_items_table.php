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
        Schema::create('capital_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('capital_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->enum('type', ['asset', 'consumable']);
            $table->decimal('price', 15, 2)->default(0);
            $table->integer('quantity')->default(1);
            $table->decimal('total_price', 15, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('capital_items');
    }
};
