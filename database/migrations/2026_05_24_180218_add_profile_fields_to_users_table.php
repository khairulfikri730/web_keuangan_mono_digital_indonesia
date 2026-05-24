<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('avatar')->nullable()->after('email');
            $table->string('username')->nullable()->after('name');
            $table->timestamp('last_login_at')->nullable()->after('remember_token');
            $table->string('last_login_device')->nullable()->after('last_login_at');
            $table->string('last_login_ip', 45)->nullable()->after('last_login_device');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['avatar', 'username', 'last_login_at', 'last_login_device', 'last_login_ip']);
        });
    }
};
