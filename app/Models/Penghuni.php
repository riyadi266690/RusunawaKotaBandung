<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class Penghuni extends Model
{
    protected $table = 'penghuni';

    protected $guarded = [];
    
     /**
     * Dapatkan atribut nama.
     * Secara otomatis mendekripsi nilai saat diakses.
     */
  protected function nama(): Attribute
    {
        return Attribute::make(
            // Fungsi get akan dijalankan setiap kali atribut 'nama' diakses
            get: fn ($value) => $value ? unsealNames([$value])[$value] ?? '' : '',
        );
    }
     /**
     * Dapatkan atribut nik.
     * Secara otomatis mendekripsi nilai saat diakses.
     */
    protected function nik(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value ? unsealNames([$value])[$value] ?? '' : '',
        );
    }
    
    /**
     * Dapatkan atribut no_tlp.
     * Secara otomatis mendekripsi nilai saat diakses.
     */
    protected function noTlp(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value ? unsealNames([$value])[$value] ?? '' : '',
        );
    }

    /**
     * Dapatkan atribut email.
     * Secara otomatis mendekripsi nilai saat diakses.
     */
    protected function email(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value ? unsealNames([$value])[$value] ?? '' : '',
        );
    }
    
    // app/Models/Penghuni.php
    public function kontrakAsPenghuni1()
    {
        return $this->hasMany(Kontrak::class, 'penghuni_id1');
    }
    public function kontrakAsPenghuni2()
    {
        return $this->hasMany(Kontrak::class, 'penghuni_id2');
    }
    public function kontrakAsPenghuni3()
    {
        return $this->hasMany(Kontrak::class, 'penghuni_id3');
    }
    public function kontrakAsPenghuni4()
    {
        return $this->hasMany(Kontrak::class, 'penghuni_id4');
    }
    // ... dan seterusnya untuk penghuni_id2, penghuni_id3, penghuni_id4
    // Relasi dengan model User
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function deletedBy()
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }
     protected static function boot()
    {
        parent::boot();

        // Mengisi updated_by secara otomatis saat model di-update
        static::updating(function ($model) {
            if (Auth::check()) {
                $model->updated_by = Auth::id();
            }
        });

        // Mengisi deleted_by secara otomatis saat model di-soft-delete
        static::deleting(function ($model) {
            if (Auth::check()) {
                $model->deleted_by = Auth::id();
            } 
            // Tidak perlu memanggil $model->save() di sini.
            // Laravel akan menyimpannya saat proses soft delete.
        });
    }
}
