<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rw', function (Blueprint $table) {
            $table->string('foto_ketua')->nullable()->after('nama_ketua');
        });
    }

    public function down(): void
    {
        Schema::table('rw', function (Blueprint $table) {
            $table->dropColumn('foto_ketua');
        });
    }
};
