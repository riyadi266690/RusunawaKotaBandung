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
            $table->string('nik_hmac')->nullable();
            $table->string('nama');
            $table->string('nama_hmac')->nullable();
            $table->string('email');
            $table->string('email_hmac')->nullable();
            $table->date('tgl_lahir');
            $table->string('tempat_lahir')->nullable();
            $table->string('no_tlp');
            $table->string('no_tlp_hmac')->nullable();
            $table->integer('jenis_kelamin');
            $table->integer('status_kawin');
            $table->integer('agama');
            $table->string('pekerjaan')->nullable();
            $table->string('alamat')->nullable();
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
        Schema::dropIfExists('penghuni');
    }
};
