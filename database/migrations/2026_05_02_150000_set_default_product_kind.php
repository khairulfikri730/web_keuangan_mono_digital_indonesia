<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('products')->whereNull('product_kind')->update([
            'product_kind' => 'regular',
        ]);

        DB::table('products')->whereNull('product_type')->update([
            'product_type' => 'finished',
        ]);
    }

    public function down(): void
    {
        // No rollback needed
    }
};
