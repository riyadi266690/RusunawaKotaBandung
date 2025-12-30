<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Laporan extends Model
{
    use HasFactory;

    protected $guarded = ['id']; // Biar semua kolom bisa diisi kecuali ID

    // Relasi: Tiket milik User siapa?
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relasi: Tiket ini kejadian di Unit mana?
    public function unit()
    {
        return $this->belongsTo(Unit::class, 'unit_id'); // Sesuaikan nama model Unit kamu jika beda
    }

    // Relasi: Siapa pegawai yang ngerjain?
    public function pegawai()
    {
        return $this->belongsTo(Pegawai::class, 'pegawai_id');
    }
}