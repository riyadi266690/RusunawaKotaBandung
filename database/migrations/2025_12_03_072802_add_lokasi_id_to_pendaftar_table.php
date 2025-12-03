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
        Schema::table('pendaftar', function (Blueprint $table) {
            // 1. Menambahkan kolom lokasi_id
            $table->unsignedBigInteger('lokasi_id')
                  ->nullable() // Dibuat nullable, meskipun sebaiknya not nullable jika pendaftaran wajib memilih lokasi
                  ->after('status_daftar'); // Sesuaikan posisi kolom jika diperlukan
                  
            // 2. Menetapkan Foreign Key constraint
            $table->foreign('lokasi_id')
                  ->references('id')
                  ->on('lokasi')
                  ->onDelete('cascade'); // Jika lokasi dihapus, lokasi_id di pendaftar diatur ke NULL
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pendaftar', function (Blueprint $table) {
            // Drop Foreign Key constraint terlebih dahulu sebelum drop kolom
            $table->dropForeign(['lokasi_id']);
            $table->dropColumn('lokasi_id');
        });
    }
};
