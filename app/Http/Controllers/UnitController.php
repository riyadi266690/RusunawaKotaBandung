<?php

namespace App\Http\Controllers;

use App\Http\Requests\Unit\StoreUnit;
use App\Http\Requests\Unit\UpdateUnit;
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

class UnitController extends Controller
{
    //
    function allDataUnit()
    {
        $user = Auth::user();
        $lokasiUser = Lokasi::where('id_user', $user->id)->pluck('id');
        $unit = Unit::with('gedung')
            ->whereHas('gedung', function ($query) use ($lokasiUser) {
                $query->whereIn('lokasi_id', $lokasiUser);
            })
            ->get();


        if ($unit->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Data unit tidak ditemukan.'
            ], 404);
        }
        return response()->json([
            'success' => true,
            'data' => $unit
        ]);
    }
    public function ajax_DTUnit(Request $request)
    {
        $query = Unit::query()
            ->select(
                'unit.id',
                'unit.nomor',
                'unit.lantai',
                'unit.tipe_unit',
                'unit.status_jual',
                'gedung.nama_gedung as gedung_nama',
                'lokasi.nama_lokasi as lokasi_nama' // Tambahkan ini
            )
            ->join('gedung', 'unit.gedung_id', '=', 'gedung.id')
            ->join('lokasi', 'gedung.lokasi_id', '=', 'lokasi.id') // Tambahkan join ke lokasi
            ->orderBy('unit.id', 'desc');
        return DataTables::of($query)
            ->addColumn('gedung', function ($unit) {
                return $unit->lokasi_nama . ' ' . $unit->gedung_nama;
            })
            ->addColumn('status_jual', function ($unit) {
                return $unit->status_jual == '1' ? 'Tersedia' : 'Dalam Perbaikan';
            })
            ->addColumn('aksi', function ($unit) {
                return '<div class="btn-group">
                            <button type="button" class="btn btn-primary btn-xs dropdown-toggle" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Aksi</button>
                            <div class="dropdown-menu">                                                          
                                <a class="dropdown-item" href="#" onclick="editUnit(' . $unit->id . ')">Edit</a>
                                <a class="dropdown-item" href="#" onclick="hapusUnit(' . $unit->id . ')">Hapus</a>                        
                            </div>
                        </div>';
            })
            ->rawColumns(['aksi'])
            ->toJson();
    }

    /**
     * Menyimpan data unit baru.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function storeUnit(StoreUnit $request)
    {
        try {
            DB::beginTransaction();
            Unit::create($request->validate());
            DB::commit();
            return response()->json(['success' => true, 'message' => 'Data unit berhasil ditambahkan.']);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error storing unit: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan saat menyimpan data unit: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Menampilkan data unit untuk diedit.
     *
     * @param  \App\Models\Unit  $unit
     * @return \Illuminate\Http\JsonResponse
     */
    public function editUnit(Unit $unit)
    {
        try {
            return response()->json(['success' => true, 'data' => $unit]);
        } catch (\Exception $e) {
            Log::error('Error fetching unit for edit: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['success' => false, 'message' => 'Gagal mengambil data unit: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Memperbarui data unit.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Unit  $unit
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateUnit(UpdateUnit $request, Unit $unit)
    {
        $validator = Validator::make($request->validate(), [
            'gedung_id' => 'required|exists:gedung,id',
            'nomor' => [
                'required',
                'string',
                'max:255',
                // Aturan unik kompleks: nomor harus unik untuk kombinasi gedung_id, lantai, dan tipe_unit, kecuali untuk unit ini sendiri
                \Illuminate\Validation\Rule::unique('unit')->where(function ($query) use ($request) {
                    return $query->where('gedung_id', $request->gedung_id)
                        ->where('lantai', $request->lantai)
                        ->where('tipe_unit', $request->tipe_unit);
                })->ignore($unit->id)
            ],
           
        ], [
            'gedung_id.required' => 'Gedung harus dipilih.',
            'gedung_id.exists' => 'Gedung tidak valid.',
            'nomor.required' => 'Nomor unit harus diisi.',
            'nomor.unique' => 'Nomor unit sudah ada untuk gedung, lantai, dan tipe unit ini.', // Pesan error diperbarui
            'lantai.required' => 'Lantai harus diisi.',
            'lantai.integer' => 'Lantai harus berupa angka.',
            'lantai.min' => 'Lantai minimal 1.',
            'lantai.max' => 'Lantai maksimal 5.',
            'tipe_unit.required' => 'Tipe unit harus dipilih.',
            'tipe_unit.in' => 'Tipe unit tidak valid.',
            'status_jual.required' => 'Status jual harus dipilih.',
            'status_jual.in' => 'Status jual tidak valid.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            DB::beginTransaction();
            $unit->update($request->validate());
            DB::commit();
            return response()->json(['success' => true, 'message' => 'Data unit berhasil diperbarui.']);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error updating unit: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan saat memperbarui data unit: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Menghapus data unit.
     *
     * @param  \App\Models\Unit  $unit
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroyUnit(Unit $unit)
    {
        try {
            DB::beginTransaction();
            $unit->delete();
            DB::commit();
            return response()->json(['success' => true, 'message' => 'Data unit berhasil dihapus.']);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error deleting unit: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan saat menghapus data unit: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
