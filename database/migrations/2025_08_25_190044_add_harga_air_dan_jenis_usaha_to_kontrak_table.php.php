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
        Schema::table('kontrak', function (Blueprint $table) {
            $table->integer('harga_air')->nullable()->after('harga_sewa'); // Menambahkan kolom harga_air setelah harga_sewa
            $table->string('jenis_usaha')->nullable()->after('harga_air'); // Menambahkan kolom jenis_usaha setelah harga_air
            $table->double('luas_usaha')->nullable()->after('jenis_usaha'); // Menambahkan kolom luas_usaha setelah jenis_usaha
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kontrak', function (Blueprint $table) {
            $table->dropColumn(['harga_air', 'jenis_usaha', 'luas_usaha']); // Menghapus kolom jika rollback
        });
    }
};
