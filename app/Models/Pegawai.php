<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pegawai extends Model
{
    use HasFactory;

    // Kasih tau nama tabelnya biar gak dicari 'pegawais'
    protected $table = 'pegawai'; 
    
    protected $guarded = ['id'];
}