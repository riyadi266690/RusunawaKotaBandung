<?php

namespace App\Http\Controllers;

use App\Models\Lokasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\Lokasi\StoreData;
use App\Http\Requests\Lokasi\UpdateData;
use App\Http\Resources\Lokasi\LokasiResource;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpCache\Store;

class LokasiController extends Controller
{
    public function allDataLokasi()
    {
        try {
            $user = Auth::user();

            if ($user->role_id == 1) {
                $lokasi = Lokasi::all();

                return response()->json([
                    'success' => true,
                    'data' => LokasiResource::collection($lokasi)
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
                'data' => LokasiResource::collection($lokasi)
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching all data lokasi: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['success' => false, 'message' => 'Gagal memuat data lokasi: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
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
            $data = $request->validated();
            if ($request->hasFile('format_kontrak')) {
                $data['format_kontrak'] = $request->file('format_kontrak')->store('format_kontrak');
            }

            $data['id_user'] = Auth::id();
            DB::transaction(function () use ($data) {
                Lokasi::create($data);
            });
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
            DB::transaction(function () use ($request, $lokasi) {
                $lokasi->update($request->validated());
            });

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Data lokasi berhasil diperbarui.'
            ]);
        } catch (\Exception $e) {
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
