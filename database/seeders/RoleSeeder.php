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
        DB::table('role')->insert([
            [
                'nama_role' => 'Super Admin',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_role' => 'Admin',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
<<<<<<< HEAD
                'nama_role' => 'Penghuni',
=======
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
>>>>>>> 5c6a2b57844312b01f859e1310df9b6d880a8244
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
