<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Lokasi extends Model
{
    protected $table = 'lokasi';
    protected $guarded = [];

    public function Gedung()
    {
        return $this->hasMany(Gedung::class);
    }
    public function users()
    {
        // Lokasi memiliki banyak User
        return $this->belongsToMany(User::class, 'lokasi_user', 'lokasi_id', 'user_id');
    }
    public function scopeAksesUser(Builder $query): void
    {
        $query->whereHas('users', function ($q) {
            $q->where('user_id', Auth::id());
        });
        
    }
    public function unit()
    {
        return $this->hasManyThrough(
            Unit::class,     // Model akhir (Unit)
            Gedung::class,   // Model perantara (Gedung)
            'lokasi_id',     // Foreign Key di tabel Gedung yang menghubungkan ke Lokasi
            'gedung_id',     // Foreign Key di tabel Unit yang menghubungkan ke Gedung
            'id',            // Local Key di tabel Lokasi
            'id'             // Local Key di tabel Gedung
        );
    }
    public function unitAvailable()
    {
        return $this->hasManyThrough(
            Unit::class,
            Gedung::class,
            'lokasi_id', 
            'gedung_id'
        )->available(); // Panggil scope 'available' yang baru dibuat di model Unit
    }
}
