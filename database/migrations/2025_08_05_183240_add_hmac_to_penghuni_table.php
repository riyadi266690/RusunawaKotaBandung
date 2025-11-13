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
        Schema::table('penghuni', function (Blueprint $table) {
            $table->string('nik_hmac')->nullable()->after('nik');
            $table->string('no_tlp_hmac')->nullable()->after('no_tlp');
            $table->string('email_hmac')->nullable()->after('email');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('penghuni', function (Blueprint $table) {
            $table->dropColumn('nik_hmac');
            $table->dropColumn('no_tlp_hmac');
            $table->dropColumn('email_hmac');
        });
    }
};
