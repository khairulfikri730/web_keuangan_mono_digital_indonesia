<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('cashflows', function (Blueprint $table) {
            $table->dateTime('transaction_date')->change();
        });
    }

    public function down()
    {
        Schema::table('cashflows', function (Blueprint $table) {
            $table->date('transaction_date')->change();
        });
    }
};
