<?php

namespace App\Http\Controllers;

<<<<<<< HEAD
=======
use App\Http\Requests\DataLokasi\StoreData;
use App\Http\Requests\DataLokasi\UpdateData;
use App\Models\Gedung;
>>>>>>> 71f7c2cceaea65b76b7037bf5fa8d68e7ad317d3
use App\Models\Lokasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class DataLokasiController extends Controller
{
    //
    public function allDataLokasi()
    {
        $user = Auth::user();

        if ($user->role_id == 1) {
            $lokasi = Lokasi::all();

            return response()->json([
                'success' => true,
                'data' => $lokasi
            ]);
        }

        $lokasi = Lokasi::where('id_user', $user->id)->get();

        if ($lokasi->isEmpty()) {
            return response()->json([
                'success' => true,
                'message' => 'Belum ada data lokasi.',
                'data' => []
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => $lokasi
        ]);
    }



    public function ajax_DTLokasi(Request $request)
    {
        $search = $request->input('search');

        $query = Lokasi::select('id', 'nama_lokasi', 'kepala_lokasi', 'alamat_lokasi')
            ->orderBy('id', 'asc');

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('nama_lokasi', 'like', "%{$search}%")
                    ->orWhere('kepala_lokasi', 'like', "%{$search}%")
                    ->orWhere('alamat_lokasi', 'like', "%{$search}%");
            });
        }

        return response()->json([
            'sukses' => true,
            'data' => $query->get()
        ]);
    }

    public function storeLokasi(StoreData $request)
    {
        try {
            DB::beginTransaction();
            $userId = Auth::id();
            Lokasi::create([
                'nama_lokasi' => $request->nama_lokasi,
                'kepala_lokasi' => $request->kepala_lokasi,
                'alamat_lokasi' => $request->alamat_lokasi,
                'id_user' => $userId
            ]);
            DB::commit();
            return response()->json(['success' => true, 'message' => 'Data lokasi berhasil ditambahkan.']);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error storing lokasi: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan saat menyimpan data lokasi: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function updateLokasi(UpdateData $request, Lokasi $lokasi)
    {
        $user = Auth::user();


        if ($user->role_id !== 1 && $lokasi->id_user !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki akses untuk mengubah lokasi ini.'
            ], 403);
        }

        try {
            DB::beginTransaction();

            $lokasi->update([
                'nama_lokasi' => $request->nama_lokasi,
                'kepala_lokasi' => $request->kepala_lokasi,
                'alamat_lokasi' => $request->alamat_lokasi,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Data lokasi berhasil diperbarui.'
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error updating lokasi: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }


    public function destroyLokasi(Lokasi $lokasi)
    {
        $user = Auth::user();

        if ($user->role_id !== 1 && $lokasi->id_user !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki akses untuk menghapus lokasi ini.'
            ]);
        }
        try {
            DB::beginTransaction();
            $lokasi->delete();
            DB::commit();
            return response()->json(['success' => true, 'message' => 'Data lokasi dan gedung terkait berhasil dihapus.']);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error deleting lokasi: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan saat menghapus data lokasi: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getLokasiOptions()
    {
        try {
            $lokasi = Lokasi::select('id', 'nama_lokasi')->get();
            return response()->json(['success' => true, 'data' => $lokasi]);
        } catch (\Exception $e) {
            Log::error('Error fetching lokasi options: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['success' => false, 'message' => 'Gagal memuat opsi lokasi: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

}
