<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Gedung extends Model
{
    protected $table = 'gedung';
    protected $guarded = [];

    public function Lokasi()
    {
        return $this->belongsTo(Lokasi::class);
    }
    public function Unit()
    {
        return $this->hasMany(Unit::class);
    }
    public function scopeAksesUser(Builder $query): void
    {
        // Memfilter Gedung berdasarkan relasi ke Lokasi
        $query->whereHas('lokasi', function ($qLokasi) {
            // Di dalam lokasi, kita filter lagi berdasarkan relasi ke users (tabel pivot)
            $qLokasi->whereHas('users', function ($qUser) {
                $qUser->where('user_id', Auth::id());
            });
        });
    }
}
