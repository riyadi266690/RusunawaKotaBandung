<?php

namespace App\Http\Controllers;

use App\Models\Gedung;
use App\Models\Lokasi;
use App\Models\Unit;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;
use Yajra\DataTables\Facades\DataTables;

class PengaturanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('pengaturan.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    // --- LOGIKA CRUD LOKASI ---

    /**
     * Memproses data lokasi untuk DataTables.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function ajax_DTLokasi(Request $request)
    {
        $query = Lokasi::query()
            ->aksesUser()
            ->select('id', 'nama_lokasi', 'kepala_lokasi', 'alamat_lokasi')
            ->orderBy('id', 'asc');
        
        return DataTables::of($query)
            ->addColumn('lokasi', function($lokasi){
                return $lokasi->nama_lokasi;
            })
            ->addColumn('penanggung_jawab', function($lokasi){
                return $lokasi->kepala_lokasi;
            })
            ->addColumn('alamat', function($lokasi){
                return $lokasi->alamat_lokasi;
            })
            ->addColumn('aksi', function($lokasi){
                return '<div class="btn-group">
                            <button type="button" class="btn btn-primary btn-xs dropdown-toggle" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Aksi</button>
                            <div class="dropdown-menu">                                                          
                                <a class="dropdown-item" href="#" onclick="editLokasi(' . $lokasi->id . ')">Edit</a>
                                <a class="dropdown-item" href="#" onclick="hapusLokasi(' . $lokasi->id . ')">Hapus</a>                        
                            </div>
                        </div>';
            })
            ->rawColumns(['aksi'])
            ->toJson();
    }

    /**
     * Menyimpan data lokasi baru.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function storeLokasi(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama_lokasi' => 'required|string|max:255|unique:lokasi,nama_lokasi',
            'kepala_lokasi' => 'required|string|max:255',
            'alamat_lokasi' => 'required|string|max:255',
        ], [
            'nama_lokasi.required' => 'Nama lokasi harus diisi.',
            'nama_lokasi.unique' => 'Nama lokasi sudah ada.',
            'kepala_lokasi.required' => 'Penanggung jawab harus diisi.',
            'alamat_lokasi.required' => 'Alamat harus diisi.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            DB::beginTransaction();
            $lokasi = Lokasi::create($request->all());
            $userIds = [Auth::id()];
            $lokasi->users()->attach($userIds);
            DB::commit();
            return response()->json(['success' => true, 'message' => 'Data lokasi berhasil ditambahkan.']);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error storing lokasi: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan saat menyimpan data lokasi: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Menampilkan data lokasi untuk diedit.
     *
     * @param  \App\Models\Lokasi  $lokasi
     * @return \Illuminate\Http\JsonResponse
     */
    public function editLokasi(Lokasi $lokasi)
    {
        try {
            return response()->json(['success' => true, 'data' => $lokasi]);
        } catch (\Exception $e) {
            Log::error('Error fetching lokasi for edit: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['success' => false, 'message' => 'Gagal mengambil data lokasi: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Memperbarui data lokasi.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Lokasi  $lokasi
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateLokasi(Request $request, Lokasi $lokasi)
    {
        $validator = Validator::make($request->all(), [
            'nama_lokasi' => 'required|string|max:255|unique:lokasi,nama_lokasi,' . $lokasi->id,
            'kepala_lokasi' => 'required|string|max:255',
            'alamat_lokasi' => 'required|string|max:255',
        ], [
            'nama_lokasi.required' => 'Nama lokasi harus diisi.',
            'nama_lokasi.unique' => 'Nama lokasi sudah ada.',
            'kepala_lokasi.required' => 'Penanggung jawab harus diisi.',
            'alamat_lokasi.required' => 'Alamat harus diisi.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            DB::beginTransaction();
            $lokasi->update($request->all());
            DB::commit();
            return response()->json(['success' => true, 'message' => 'Data lokasi berhasil diperbarui.']);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error updating lokasi: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan saat memperbarui data lokasi: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Menghapus data lokasi.
     *
     * @param  \App\Models\Lokasi  $lokasi
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroyLokasi(Lokasi $lokasi)
    {
        try {
            DB::beginTransaction();
            $lokasi->delete(); // Ini akan menghapus cascading gedung yang terkait karena onDelete('cascade')
            DB::commit();
            return response()->json(['success' => true, 'message' => 'Data lokasi dan gedung terkait berhasil dihapus.']);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error deleting lokasi: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan saat menghapus data lokasi: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Mengambil daftar lokasi untuk dropdown.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getLokasiOptions()
    {
        try {
            $lokasi = Lokasi::query()
            // Terapkan scope AksesUser di sini!
            // Hanya lokasi yang dimiliki oleh user yang login yang akan diambil
            ->aksesUser() 
            
            ->select('id', 'nama_lokasi')
            ->get();
            
            return response()->json(['success' => true, 'data' => $lokasi]);
        } catch (\Exception $e) {
            Log::error('Error fetching lokasi options: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['success' => false, 'message' => 'Gagal memuat opsi lokasi: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // --- LOGIKA CRUD GEDUNG ---

    /**
     * Memproses data gedung untuk DataTables.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function ajax_DTGedung(Request $request)
    {
        $query = Gedung::query()
            ->aksesUser()
            ->select('gedung.id', 'gedung.nama_gedung', 'gedung.tipe_gedung', 'lokasi.nama_lokasi as lokasi')
            ->join('lokasi', 'gedung.lokasi_id', '=', 'lokasi.id')
            ->orderBy('gedung.id', 'asc');
        
        return DataTables::of($query)
            ->addColumn('lokasi', function($gedung){
                return $gedung->lokasi; // Sudah di-join dan di-alias
            })
            ->addColumn('aksi', function($gedung){
                return '<div class="btn-group">
                            <button type="button" class="btn btn-primary btn-xs dropdown-toggle" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Aksi</button>
                            <div class="dropdown-menu">                                                          
                                <a class="dropdown-item" href="#" onclick="editGedung(' . $gedung->id . ')">Edit</a>
                                <a class="dropdown-item" href="#" onclick="hapusGedung(' . $gedung->id . ')">Hapus</a>                        
                            </div>
                        </div>';
            })
            ->rawColumns(['aksi'])
            ->toJson();
    }

    /**
     * Menyimpan data gedung baru.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function storeGedung(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama_gedung' => 'required|string|max:255|unique:gedung,nama_gedung,NULL,id,lokasi_id,' . $request->lokasi_id,
            'tipe_gedung' => 'required|string|max:255',
            'lokasi_id' => 'required|exists:lokasi,id',
        ], [
            'nama_gedung.required' => 'Nama gedung harus diisi.',
            'nama_gedung.unique' => 'Nama gedung sudah ada untuk lokasi ini.', // Pesan error diperbarui
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

        try {
            DB::beginTransaction();
            Gedung::create($request->all());
            DB::commit();
            return response()->json(['success' => true, 'message' => 'Data gedung berhasil ditambahkan.']);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error storing gedung: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan saat menyimpan data gedung: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Menampilkan data gedung untuk diedit.
     *
     * @param  \App\Models\Gedung  $gedung
     * @return \Illuminate\Http\JsonResponse
     */
    public function editGedung(Gedung $gedung)
    {
        try {
            return response()->json(['success' => true, 'data' => $gedung]);
        } catch (\Exception $e) {
            Log::error('Error fetching gedung for edit: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['success' => false, 'message' => 'Gagal mengambil data gedung: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Memperbarui data gedung.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Gedung  $gedung
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateGedung(Request $request, Gedung $gedung)
    {
        $validator = Validator::make($request->all(), [
            'nama_gedung' => 'required|string|max:255|unique:gedung,nama_gedung,' . $gedung->id . ',id,lokasi_id,' . $request->lokasi_id,
            'tipe_gedung' => 'required|string|max:255',
            'lokasi_id' => 'required|exists:lokasi,id',
        ], [
            'nama_gedung.required' => 'Nama gedung harus diisi.',
            'nama_gedung.unique' => 'Nama gedung sudah ada untuk lokasi ini.', // Pesan error diperbarui
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

        try {
            DB::beginTransaction();
            $gedung->update($request->all());
            DB::commit();
            return response()->json(['success' => true, 'message' => 'Data gedung berhasil diperbarui.']);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error updating gedung: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan saat memperbarui data gedung: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Menghapus data gedung.
     *
     * @param  \App\Models\Gedung  $gedung
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroyGedung(Gedung $gedung)
    {
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


    /**
     * Mengambil daftar gedung untuk dropdown.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getGedungOptions()
    {
        try {
            $gedung = Gedung::query()
                            ->aksesUser()
                            ->select('gedung.id', 'gedung.nama_gedung', 'lokasi.nama_lokasi as lokasi_nama')
                            ->join('lokasi', 'gedung.lokasi_id', '=', 'lokasi.id')
                            ->get();
            // Map data untuk format yang lebih mudah di frontend
            $formattedGedung = $gedung->map(function($item) {
                return [
                    'id' => $item->id,
                    'nama_gedung' => $item->nama_gedung,
                    'lokasi' => $item->lokasi_nama // Menggunakan alias yang sudah di-join
                ];
            });
            return response()->json(['success' => true, 'data' => $formattedGedung]);
        } catch (\Exception $e) {
            Log::error('Error fetching gedung options: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['success' => false, 'message' => 'Gagal memuat opsi gedung: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    // --- LOGIKA CRUD UNIT ---

    /**
     * Memproses data unit untuk DataTables.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function ajax_DTUnit(Request $request)
    {
        $query = Unit::query()
            ->aksesUser()
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
            ->addColumn('gedung', function($unit){
                return $unit->lokasi_nama . ' ' . $unit->gedung_nama; 
            })
            ->addColumn('status_jual', function($unit){
                return $unit->status_jual == '1' ? 'Tersedia' : 'Dalam Perbaikan';
            })
            ->addColumn('aksi', function($unit){
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
   public function storeUnit(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'gedung_id' => 'required|exists:gedung,id',
            'nomor' => [
                'required',
                'string',
                'max:255',
                // Aturan unik kompleks: nomor harus unik untuk kombinasi gedung_id, lantai, dan tipe_unit
                \Illuminate\Validation\Rule::unique('unit')->where(function ($query) use ($request) {
                    return $query->where('gedung_id', $request->gedung_id)
                                 ->where('lantai', $request->lantai)
                                 ->where('tipe_unit', $request->tipe_unit);
                })
            ],
            'lantai' => 'required|integer|min:1|max:5',
            'tipe_unit' => 'required|string|in:Hunian,RBH',
            'status_jual' => 'required|string|in:0,1',
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
            Unit::create($request->all());
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
    public function updateUnit(Request $request, Unit $unit)
    {
        $validator = Validator::make($request->all(), [
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
            'lantai' => 'required|integer|min:1|max:5',
            'tipe_unit' => 'required|string|in:Hunian,RBH',
            'status_jual' => 'required|string|in:0,1',
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
            $unit->update($request->all());
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
