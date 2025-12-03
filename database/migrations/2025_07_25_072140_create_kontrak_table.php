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
        Schema::create('kontrak', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unit_id')->references('id')->on('unit')->onDelete('cascade');
            $table->string('no_kontrak');
            $table->integer('tipe_kontrak'); //1 unit hunian 2 unit RBH
            $table->date('tgl_awal');
            $table->date('tgl_akhir');
            $table->date('tgl_keluar')->nullable();
            $table->integer('masa_kontrak')->nullable(); //selisih antara tanggal awal dan tanggal akhir / tgl keluar
            $table->integer('status_kontrak');//1 aktif 0 non aktif
            $table->string('nama_pihak1');//ambil dari nama kepala lokasi di tabel lokasi
            $table->integer('status_ttd'); //draft 0 draft 1 ttd
            $table->foreignId('penghuni_id1')->references('id')->on('penghuni')->onDelete('cascade');
            $table->foreignId('penghuni_id2')->references('id')->on('penghuni')->onDelete('cascade')->nullable();
            $table->foreignId('penghuni_id3')->references('id')->on('penghuni')->onDelete('cascade')->nullable();
            $table->foreignId('penghuni_id4')->references('id')->on('penghuni')->onDelete('cascade')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('deleted_by')->nullable()->constrained('users')->onDelete('set null');
            $table->softDeletes(); // Untuk menyimpan data yang dihapus
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kontrak');
    }
};
