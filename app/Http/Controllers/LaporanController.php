<?php

namespace App\Http\Controllers;

use App\Models\Laporan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LaporanController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $query = Laporan::with(['user', 'unit', 'pegawai'])->latest();

        if ($user->role_id == 3) {
            $query->where('user_id', $user->id);
        }

        
        return response()->json([
            'success' => true,
            'data' => $query->get()
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'judul' => 'required|string|max:255',
            'deskripsi' => 'required|string',
            'foto' => 'nullable|image|max:2048', 
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        $fotoPath = null;
        if ($request->hasFile('foto')) {
            $fotoPath = $request->file('foto')->store('laporan', 'public');
        }

        // Handle case jika Admin iseng mau input laporan manual (opsional)
        // Tapi utamanya ini buat Penghuni
        $unitId = $request->user()->unit_id; 

        $laporan = Laporan::create([
            'user_id' => $request->user()->id,
            'unit_id' => $unitId,
            'judul' => $request->judul,
            'deskripsi' => $request->deskripsi,
            'foto' => $fotoPath,
            'status' => 'Terkirim',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Laporan berhasil dikirim!',
            'data' => $laporan
        ], 201);
    }

    // 3. UPDATE LAPORAN (KHUSUS ADMIN MENUGASKAN PEGAWAI)
    public function update(Request $request, $id)
    {
        // Cari Laporan
        $laporan = Laporan::find($id);
        if (!$laporan) {
            return response()->json(['message' => 'Laporan tidak ditemukan'], 404);
        }

        // Validasi Input Admin
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:Terkirim,Diproses,Dikerjakan,Selesai',
            'pegawai_id' => 'nullable|exists:pegawai,id', // Pastikan ID pegawai valid
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Update Data
        $laporan->update([
            'status' => $request->status,
            'pegawai_id' => $request->pegawai_id
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Laporan berhasil diperbarui!',
            'data' => $laporan
        ]);
    }
}