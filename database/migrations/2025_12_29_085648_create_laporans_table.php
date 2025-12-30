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
    Schema::create('laporans', function (Blueprint $table) {
        $table->id();
        
        // 1. Identitas Pelapor
        // Kita simpan user_id dan unit_id biar data historisnya kuat
        $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
        $table->foreignId('unit_id')->constrained('unit')->onDelete('cascade'); 
        
        // 2. Isi Laporan
        $table->string('judul'); // Contoh: "Kran Bocor"
        $table->text('deskripsi'); // Detail keluhan
        $table->string('foto')->nullable(); // Bukti foto (wajib fitur jaman now)
        
        // 3. Status Tracking
        // Default 'Terkirim' saat warga baru submit
        $table->enum('status', ['Terkirim', 'Diproses', 'Dikerjakan', 'Selesai'])->default('Terkirim');
        
        // 4. Penugasan (Diisi nanti oleh Admin)
        // Kalau null berarti belum ada petugas yang ditunjuk
        $table->foreignId('pegawai_id')->nullable()->constrained('pegawai')->onDelete('set null');

        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('laporans');
    }
};
