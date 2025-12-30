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
            $table->integer('tipe_kontrak');
            $table->date('tgl_awal');
            $table->date('tgl_akhir');
            $table->date('tgl_keluar')->nullable();
            $table->integer('masa_kontrak')->nullable();
            $table->integer('status_kontrak');
            $table->string('nama_pihak1');
            $table->integer('status_ttd');
            $table->string('dok_kontrak')->nullable();
            $table->integer('harga_sewa')->nullable();
            $table->integer('harga_air')->nullable();
            $table->string('jenis_usaha')->nullable();
            $table->double('luas_usaha')->nullable();
            $table->foreignId('penghuni_1')->references('id')->on('penghuni')->onDelete('cascade');
            $table->foreignId('penghuni_2')->references('id')->on('penghuni')->onDelete('cascade');
            $table->foreignId('penghuni_3')->references('id')->on('penghuni')->onDelete('cascade');
            $table->foreignId('penghuni_4')->references('id')->on('penghuni')->onDelete('cascade');
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('deleted_by')->nullable()->constrained('users')->onDelete('set null');
            $table->softDeletes();
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
