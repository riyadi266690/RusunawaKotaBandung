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
            $table->integer('harga_sewa')->nullable()->after('status_ttd'); // Menambahkan kolom dok_kontrak setelah status_ttd
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kontrak', function (Blueprint $table) {
            $table->dropColumn('harga_sewa'); // Menghapus kolom dok_kontrak jika rollback
        });
    }
};
