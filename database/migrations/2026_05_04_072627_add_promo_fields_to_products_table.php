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
        Schema::table('products', function (Blueprint $blueprint) {
            $blueprint->boolean('is_promo')->default(false)->after('is_active');
            $blueprint->decimal('discount_price', 15, 2)->nullable()->after('price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $blueprint) {
            $blueprint->dropColumn(['is_promo', 'discount_price']);
        });
    }
};
