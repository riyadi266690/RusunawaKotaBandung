<?php

namespace App\Http\Controllers;

use App\Http\Requests\Gedung\StoreGedung;
use App\Http\Requests\Gedung\UpdateGedung;
use App\Models\Gedung;
use App\Models\Lokasi;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Auth;

class GedungController extends Controller
{
    //
    function allDataGedung()
    {
        $user = Auth::user();
        $lokasiUser = Lokasi::where('id_user', $user->id)->pluck('id');

        if ($user->role_id === 1) {
            $gedung = Gedung::with('lokasi')->get();
            return response()->json([
                'sukses' => true,
                'data' => $gedung
            ]);
        }

        $gedung = Gedung::with('lokasi')
            ->whereIn('lokasi_id', $lokasiUser)
            ->get();

        if ($gedung->isEmpty()) {
            return response()->json([
                'sukses' => false,
                'message' => 'Data gedung tidak ditemukan.'
            ], 404);
        }

        return response()->json([
            'sukses' => true,
            'data' => $gedung
        ]);
    }


    public function ajax_DTGedung(Request $request)
    {
        $query = Gedung::query()
            ->select('gedung.id', 'gedung.nama_gedung', 'gedung.tipe_gedung', 'lokasi.nama_lokasi as lokasi')
            ->join('lokasi', 'gedung.lokasi_id', '=', 'lokasi.id')
            ->orderBy('gedung.id', 'asc');

        if (!empty($request)) {
            $query->where(function ($q) use ($request) {
                $q->where('nama_gedung', 'like', ' %{$search}%')
                    ->orWhere('tipe_gedung', 'like', '%{$search}%');
            });
        }

        $data = $query->get();

        return response()->json([
            'sukses' => true,
            'data' => $data
        ]);
    }

    public function storeGedung(StoreGedung $request)
    {
        try {
            DB::beginTransaction();
            Gedung::create($request->validate());
            DB::commit();
            return response()->json(['success' => true, 'message' => 'Data gedung berhasil ditambahkan.']);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error storing gedung: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan saat menyimpan data gedung: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Memperbarui data gedung.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Gedung  $gedung
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateGedung(UpdateGedung $request, Gedung $gedung)
    {
        $user = Auth::user();
        $validator = Validator::make($request->validate(), [
           
        ], [
            'nama_gedung.required' => 'Nama gedung harus diisi.',
            'nama_gedung.unique' => 'Nama gedung sudah ada untuk lokasi ini.',
            'tipe_gedung.required' => 'Tipe gedung harus diisi.',
            'lokasi_id.required' => 'Lokasi harus dipilih.',
            'lokasi_id.exists' => 'Lokasi tidak valid.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if ($user->role_id !== 1 && $user->id !== $gedung->Lokasi->id_user) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki izin untuk memperbarui data gedung.'
            ], Response::HTTP_FORBIDDEN);
        }

        try {
            DB::beginTransaction();
            $gedung->update($request->validate());
            DB::commit();
            return response()->json(['success' => true, 'message' => 'Data gedung berhasil diperbarui.']);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error updating gedung: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan saat memperbarui data gedung: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    function destroyGedung(Gedung $gedung)
    {
        $user = Auth::user();
        if ($user->role_id !== 1 && $user->id !== $gedung->lokasi->id_user) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki izin untuk menghapus data gedung.'
            ], Response::HTTP_FORBIDDEN);
        }
        try {
            DB::beginTransaction();
            $gedung->delete();
            DB::commit();
            return response()->json(['success' => true, 'message' => 'Data gedung berhasil dihapus.']);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error deleting gedung: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan saat menghapus data gedung: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function countUnit()
    {
        $unit = Unit::count();
        if ($unit->isEmpty()) {
            return response()->json(
                ['gagal' => 'data tidak ditemukan']
            );
        }
        return response()->json(
            ['message' => 'data ditemukan', 'data' => $unit]
        );
    }

    /**
     * Mengambil daftar gedung untuk dropdown.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getGedungOptions()
    {
        try {
            $gedung = Gedung::select('gedung.id', 'gedung.nama_gedung', 'lokasi.nama_lokasi as lokasi_nama')
                ->join('lokasi', 'gedung.lokasi_id', '=', 'lokasi.id')
                ->get();
            // Map data untuk format yang lebih mudah di frontend
            $formattedGedung = $gedung->map(function ($item) {
                return [
                    'id' => $item->id,
                    'nama_gedung' => $item->nama_gedung,
                    'lokasi' => $item->lokasi_nama
                ];
            });
            return response()->json(['success' => true, 'data' => $formattedGedung]);
        } catch (\Exception $e) {
            Log::error('Error fetching gedung options: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['success' => false, 'message' => 'Gagal memuat opsi gedung: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
