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
        Schema::table('transaction_items', function (Blueprint $table) {
            $table->boolean('is_custom_price')->default(false)->after('subtotal');
            $table->decimal('custom_price', 15, 2)->nullable()->after('is_custom_price');
            $table->decimal('custom_hpp', 15, 2)->nullable()->after('custom_price');
            $table->text('custom_price_reason')->nullable()->after('custom_hpp');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transaction_items', function (Blueprint $table) {
            $table->dropColumn(['is_custom_price', 'custom_price', 'custom_hpp', 'custom_price_reason']);
        });
    }
};
