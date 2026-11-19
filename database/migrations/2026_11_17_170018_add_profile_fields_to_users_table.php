<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone', 50)->nullable()->after('email');
            $table->char('country_code', 2)->nullable()->after('phone');
            $table->string('role', 40)->default('customer')->index()->after('country_code');
            $table->string('status', 30)->default('active')->index()->after('role');
            $table->timestamp('last_login_at')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['phone', 'country_code', 'role', 'status', 'last_login_at']);
        });
    }
};
