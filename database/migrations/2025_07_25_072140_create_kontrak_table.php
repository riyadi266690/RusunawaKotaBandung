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
            $table->foreignId('unit_id')->constrained('unit')->onDelete('cascade');            
            $table->string('no_kontrak')->unique(); 
            $table->integer('tipe_kontrak'); 
            $table->date('tgl_awal');
            $table->date('tgl_akhir');
            $table->date('tgl_keluar')->nullable(); 
            $table->bigInteger('harga_sewa'); 
            $table->bigInteger('harga_air')->nullable()->default(0);
            $table->integer('masa_kontrak')->nullable(); 
            $table->integer('status_kontrak')->default(1); 
            $table->string('nama_pihak1'); // Pejabat
            $table->foreignId('penghuni_id')->constrained('penghuni')->onDelete('cascade');
            $table->string('dok_kontrak')->nullable(); 
            $table->integer('status_ttd')->default(0); 
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