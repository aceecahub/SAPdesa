<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tambah rw_id ke users (untuk role ketua_rw)
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('rw_id')
                ->nullable()
                ->constrained('rw')
                ->nullOnDelete()
                ->after('dusun_id');

            // Tambah role ketua_rw — karena SQLite tidak support modifyColumn enum,
            // kita tambahkan sebagai string check di application layer.
            // (di migration sebelumnya sudah pakai enum, ini hanya komentar)
        });

        // Ganti dusun_id → rw_id di laporan
        Schema::table('laporan', function (Blueprint $table) {
            // Hapus FK lama dusun_id
            $table->dropForeign(['dusun_id']);
            $table->dropColumn('dusun_id');

            // Tambah rw_id
            $table->foreignId('rw_id')
                ->after('subkategori_id')
                ->constrained('rw')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['rw_id']);
            $table->dropColumn('rw_id');
        });

        Schema::table('laporan', function (Blueprint $table) {
            $table->dropForeign(['rw_id']);
            $table->dropColumn('rw_id');

            $table->foreignId('dusun_id')
                ->after('subkategori_id')
                ->constrained('dusun')
                ->cascadeOnDelete();
        });
    }
};
