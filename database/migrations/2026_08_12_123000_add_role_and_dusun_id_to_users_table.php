<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['superadmin', 'admin', 'kepala_dusun'])->default('kepala_dusun')->after('password');
            $table->foreignId('dusun_id')->nullable()->constrained('dusun')->nullOnDelete()->after('role');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['dusun_id']);
            $table->dropColumn(['role', 'dusun_id']);
        });
    }
};