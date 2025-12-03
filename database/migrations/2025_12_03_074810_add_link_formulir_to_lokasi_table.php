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
       Schema::table('lokasi', function (Blueprint $table) {
            // Kolom untuk menyimpan URL formulir pendaftaran
            $table->string('link_formulir', 500)->nullable()->after('mulai_dari'); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lokasi', function (Blueprint $table) {
            $table->dropColumn('link_formulir');
        });
    }
};
