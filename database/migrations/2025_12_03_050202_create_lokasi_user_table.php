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
        Schema::create('lokasi_user', function (Blueprint $table) {
            // Kolom Kunci Asing ke tabel 'lokasi'
            $table->foreignId('lokasi_id')
                  ->references('id')
                  ->on('lokasi') // Secara default akan menunjuk ke tabel 'lokasi'
                  ->onDelete('cascade');
            
            // Kolom Kunci Asing ke tabel 'users'
            $table->foreignId('user_id')
                  ->constrained() // Secara default akan menunjuk ke tabel 'users'
                  ->onDelete('cascade');

            // Membuat kedua kolom menjadi Primary Key gabungan
            // Ini memastikan tidak ada duplikasi hubungan (satu user hanya bisa dihubungkan sekali ke satu lokasi)
            $table->primary(['lokasi_id', 'user_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lokasi_user');
    }
};
