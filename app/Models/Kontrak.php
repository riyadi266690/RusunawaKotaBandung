<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Kontrak extends Model
{
    protected $table = 'kontrak';
    protected $guarded = [];

    // Relasi ke Unit
    public function unit()
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }

    // Relasi ke Penghuni 
    public function penghuni1()
    {

        return $this->belongsTo(Penghuni::class, 'penghuni_1');
    }
    public function penghuni2()
    {

        return $this->belongsTo(Penghuni::class, 'penghuni_2');
    }
    public function penghuni3()
    {

        return $this->belongsTo(Penghuni::class, 'penghuni_3');
    }
    public function penghuni4()
    {

        return $this->belongsTo(Penghuni::class, 'penghuni_4');
    }

    // Relasi ke User (created_by)
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Relasi ke User (updated_by)
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // Relasi ke User (deleted_by)
    public function deleter()
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    protected static function boot()
    {
        parent::boot();

        // Mengisi created_by saat membuat record baru
        static::creating(function ($model) {
            if (Auth::check()) {
                $model->created_by = Auth::id();
            }
        });

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
                $model->save();
            }
        });
    }
}
