<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
}
