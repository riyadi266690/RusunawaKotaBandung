<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lokasi extends Model
{
    protected $table = 'lokasi';
    protected $guarded = [];

    public function Gedung()
    {
        return $this->hasMany(Gedung::class);
    }
}
