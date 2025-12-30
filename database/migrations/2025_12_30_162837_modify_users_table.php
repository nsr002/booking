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
        Schema::table('users', function (Blueprint $table) {
            $table->string('line_user_id')->nullable()->index()->after('id');
            $table->string('phone')->nullable()->after('email');
            $table->enum('role', ['user', 'admin', 'trainer'])->default('user')->after('phone');
            $table->string('avatar')->nullable()->after('role');
            $table->string('password')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['line_user_id', 'phone', 'role', 'avatar']);
        });
    }
};
