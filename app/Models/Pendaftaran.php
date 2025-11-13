<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Pendaftaran extends Model
{
    protected $table = 'pendaftar';
    protected $guarded = [];
    public function Updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
    public function deleter()
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
