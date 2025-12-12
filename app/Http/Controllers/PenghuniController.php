<?php

namespace App\Http\Controllers;

use App\Models\Penghuni;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PenghuniController extends Controller
{
    public function ajax_DTPenghuni(Request $request)
    {
        $query = Penghuni::query()->orderBy('id', 'desc');

        if ($request->has('search') && trim($request->search) !== '') {
            $keyword = trim($request->search);
            
            $query->where(function ($q) use ($keyword) {
                $q->where('nik', 'LIKE', "%{$keyword}%")
                  ->orWhere('nama', 'LIKE', "%{$keyword}%")
                  ->orWhere('email', 'LIKE', "%{$keyword}%")
                  ->orWhere('no_tlp', 'LIKE', "%{$keyword}%");
            });
        }

        $perPage = $request->get('per_page', 10);
        $data = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Data penghuni berhasil diambil.',
            'data' => $data
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nik'           => 'required|unique:penghuni,nik',
            'email'         => 'required|email|unique:penghuni,email',
            'no_tlp'        => 'required|unique:penghuni,no_tlp',
            'nama'          => 'required|string',
            'tgl_lahir'     => 'required|date',
            'tempat_lahir'  => 'required',
            'jenis_kelamin' => 'required|in:1,2',
            'status_kawin'  => 'required',
            'agama'         => 'required',
            'pekerjaan'     => 'required',
            'alamat'        => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            Penghuni::create($request->all());

            return response()->json(['success' => true, 'message' => 'Data penghuni berhasil ditambahkan.']);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal menyimpan: ' . $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $penghuni = Penghuni::find($id);
        if(!$penghuni) return response()->json(['success' => false, 'message' => 'Data tidak ditemukan'], 404);

        $validator = Validator::make($request->all(), [
            'nik'           => 'required|unique:penghuni,nik,' . $id, // Ignore ID sendiri
            'email'         => 'required|email|unique:penghuni,email,' . $id,
            'no_tlp'        => 'required|unique:penghuni,no_tlp,' . $id,
            'nama'          => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            $penghuni->update($request->all());
            return response()->json(['success' => true, 'message' => 'Data berhasil diperbarui.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            Penghuni::findOrFail($id)->delete();
            return response()->json(['success' => true, 'message' => 'Data dihapus.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}