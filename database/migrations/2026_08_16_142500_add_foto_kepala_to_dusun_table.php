<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dusun', function (Blueprint $table) {
            $table->string('foto_kepala')->nullable()->after('nama_kepala');
        });
    }

    public function down(): void
    {
        Schema::table('dusun', function (Blueprint $table) {
            $table->dropColumn('foto_kepala');
        });
    }
};
