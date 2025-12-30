<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Unit extends Model
{
    protected $table = 'unit';
    protected $guarded = [];

    public function Gedung()
    {
        return $this->belongsTo(Gedung::class);
    }
    // app/Models/Unit.php
    public function kontrak()
    {
        return $this->hasMany(Kontrak::class, 'unit_id');
    }
    public function scopeAksesUser(Builder $query): void
    {
        // 1. Filter Unit yang memiliki relasi ke Gedung (whereHas('Gedung'))
        $query->whereHas('Gedung', function ($qGedung) {
            
            // 2. Di dalam Gedung, kita filter lagi berdasarkan relasi ke Lokasi (whereHas('lokasi'))
            $qGedung->whereHas('lokasi', function ($qLokasi) {
                
                // 3. Di dalam Lokasi, kita filter lagi berdasarkan relasi ke users (tabel pivot)
                $qLokasi->whereHas('users', function ($qUser) {
                    $qUser->where('user_id', Auth::id());
                });
            });
        });
    }
    public function scopeAvailable(Builder $query): void
    {
        $query->whereDoesntHave('kontrak') // Kondisi 1: Tidak ada kontrak sama sekali
              ->orWhereHas('kontrak', function (Builder $q) {
                  // Kondisi 2: Memiliki kontrak yang status_kontrak-nya adalah 0
                  $q->where('status_kontrak', 0); 
              });
    }
}
