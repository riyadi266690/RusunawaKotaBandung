<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         // Role untuk Admin
        DB::table('role')->insert([
            [
                'user_id' => 1,
                'fitur' => 'dashboard.index',
                'akses' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 1,
                'fitur' => 'pendaftar.index',
                'akses' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 1,
                'fitur' => 'pendaftar.update',
                'akses' => 1,
                'created_at' => now(),      
                'updated_at' => now(),
            ],
            [
                'user_id' => 1,
                'fitur' => 'pendaftar.ajax.DTPendaftar',
                'akses' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],[
                'user_id' => 1,
                'fitur' => 'pendaftar.updateWawancara',
                'akses' => 1,
                'created_at' => now(),
                'updated_at' => now(),
                
            ],[
                'user_id' => 1,
                'fitur' => 'pendaftar.updateSelesai',
                'akses' => 1,
                'created_at' => now(),
                'updated_at' => now(),
                
            ],[
                'user_id' => 1,
                'fitur' => 'penghuni.index',
                'akses' => 1,
                'created_at' => now(),
                'updated_at' => now(),
                
            ],[
                'user_id' => 1,
                'fitur' => 'penghuni.ajax.DTPenghuni',
                'akses' => 1,
                'created_at' => now(),
                'updated_at' => now(),
                
            ],[
                'user_id' => 1,
                'fitur' => 'penghuni.store',
                'akses' => 1,
                'created_at' => now(),
                'updated_at' => now(),
                
            ],[
                'user_id' => 1,
                'fitur' => 'penghuni.getDataIndividuFromAPI',
                'akses' => 1,
                'created_at' => now(),
                'updated_at' => now(),
                
            ],[
                'user_id' => 1,
                'fitur' => 'penghuni.edit',
                'akses' => 1,
                'created_at' => now(),
                'updated_at' => now(),
                
            ],[
                'user_id' => 1,
                'fitur' => 'penghuni.update',
                'akses' => 1,
                'created_at' => now(),
                'updated_at' => now(),
                
            ],[
                'user_id' => 1,
                'fitur' => 'penghuni.destroy',
                'akses' => 1,
                'created_at' => now(),
                'updated_at' => now(),
                
            ],[
                'user_id' => 1,
                'fitur' => 'pengaturan.index',
                'akses' => 1,
                'created_at' => now(),
                'updated_at' => now(),
                
            ],[
                'user_id' => 1,
                'fitur' => 'pengaturan.ajax.DTLokasi',
                'akses' => 1,
                'created_at' => now(),
                'updated_at' => now(),
                
            ],[
                'user_id' => 1,
                'fitur' => 'pengaturan.ajax.DTGedung',
                'akses' => 1,
                'created_at' => now(),
                'updated_at' => now(),
                
            ],[
                'user_id' => 1,
                'fitur' => 'pengaturan.ajax.ajax.DTUnit',
                'akses' => 1,
                'created_at' => now(),
                'updated_at' => now(),
                
            ],[
                'user_id' => 1,
                'fitur' => 'kontrak.aktif',
                'akses' => 1,
                'created_at' => now(),
                'updated_at' => now(),
                
            ],[
                'user_id' => 1,
                'fitur' => 'kontrak.*',
                'akses' => 1,
                'created_at' => now(),
                'updated_at' => now(),
                
            ]
        ]);

        // Role untuk User biasa (tidak bisa akses fitur)
        DB::table('role')->insert([
            [
                'user_id' => 2,
                'fitur' => 'dashboard.index',
                'akses' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 2,
                'fitur' => 'pendaftar.pengelola',
                'akses' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 2,
                'fitur' => 'pendaftar.update',
                'akses' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
