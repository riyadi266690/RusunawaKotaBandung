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
        Schema::create('pendaftar', function (Blueprint $table) {
            $table->id();
            $table->string('nama');;
            $table->string('telp_pendaftar');
            $table->string('telp_pendaftar_hash')->nullable();
            $table->string('suket');
            $table->integer('status_daftar');
            $table->date('tgl_daftar');
            $table->date('tgl_wawancara')->nullable();
            $table->date('tgl_final')->nullable();
            $table->string('ket_wawancara')->nullable();
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pendaftar');
    }
};
