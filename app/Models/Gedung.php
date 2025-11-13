<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
}
