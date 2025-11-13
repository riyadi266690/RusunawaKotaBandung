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
        Schema::create('penghuni', function (Blueprint $table) {
            $table->id();
            $table->string('nik');
            $table->string('nama');
            $table->string('email');
            $table->date('tgl_lahir');
            $table->string('no_tlp');
            $table->integer('jenis_kelamin');//1 laki laki,2 perempuan
            $table->integer('status_kawin'); //1 belum kawin, 2 kawin/nikah, 3Cerai Hidup, 4 Cerai Mati
            $table->integer('agama'); //1 islam, 2 kristen, 3 katolik, 4 Hindu, 5 budha 6 konghuchu, 7 Penghayat Kepercayaan            
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
        Schema::dropIfExists('penghuni');
    }
};
