<?php

namespace App\Http\Controllers;

use App\Models\Lokasi;
use Carbon\Carbon;
use Exception;
use App\Models\Pendaftaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Resources\Pendaftaran\PendaftaranResource;

class PendaftaranController extends Controller
{
    public function allDataPendaftaran()
    {
        $lokasi = Lokasi::query()
            ->withCount('unitAvailable')
            //->withCount('unit')total unit
            ->get();
        //$lokasi = Lokasi::all();
        return view('pendaftaran.index', compact('lokasi'));
    }
    public function index_pengelola()
    {
        return view('pendaftaran.index_pengelola');
    }
    public function ajax_DTpendaftar(Request $request)
    {
        $query = Pendaftaran::query()
            ->aksesUser()
            ->select(
                'nama',
                'pendaftar.id',
                'telp_pendaftar',
                'suket',
                'status_daftar',
                'tgl_daftar',
                'tgl_wawancara',
                'tgl_final',
                'ket_wawancara',
                'suket',
                'updated_by',
                'lokasi.nama_lokasi'
            )
            ->join('lokasi', 'pendaftar.lokasi_id', '=', 'lokasi.id')
            ->orderBy('id', 'desc');

        $pendaftars = $query->get();

        // Kumpulkan semua nama dan telp terenkripsi
        $encryptedTexts = $pendaftars->pluck('nama')->merge($pendaftars->pluck('telp_pendaftar'))->toArray();
        $decryptedTextsMap = unsealNames($encryptedTexts);

        $pendaftars->transform(function ($item) use ($decryptedTextsMap) {
            $item->nama = $decryptedTextsMap[$item->nama] ?? 'Invalid response';
            $item->telp_pendaftar = $decryptedTextsMap[$item->telp_pendaftar] ?? 'Invalid response';
            return $item;
        });

        return DataTables::of($pendaftars)
            ->addColumn('status', function ($w) {
                switch ($w->status_daftar) {
                    case 1:
                        $badge = '<span class="badge bg-warning">Menunggu</span>';
                        break;
                    case 2:
                        $badge = '<span class="badge bg-info">Wawancara</span>';
                        break;
                    case 3:
                        $badge = '<span class="badge bg-success">Selesai</span>';
                        break;
                    default:
                        $badge = '<span class="badge bg-secondary">Tidak Diketahui</span>';
                        break;
                }

                // Tampilkan Nama Lokasi diikuti Status
                return '<p class="fw-bold mb-1">' . $w->nama_lokasi . '</p>' . $badge;
            })
            ->addColumn('daftar', function ($w) {
                return $w->tgl_daftar ? Carbon::parse($w->tgl_daftar)->format('d-m-Y') : '-';
            })
            ->addColumn('nama', function ($w) {
                if ($w->suket) {
                    $downloadUrl = asset('storage/' . $w->suket);
                    return $w->nama . '<br>' . '<a href="' . $downloadUrl . '" class="edit edit-primary edit-sm mt-1" target="_blank">Download Suket</a>';
                }

                return $w->nama;
            })
            ->addColumn('wawancara', function ($item) {
                $tglWawancaraFormatted = $item->tgl_wawancara ? Carbon::parse($item->tgl_wawancara)->format('d-m-Y') : 'Pilih Tanggal';
                $itemId = $item->id;
                $isSet = $item->tgl_wawancara ? 'true' : 'false';

                return '
                <a href="#" 
                   class="edit-wawancara-btn" 
                   data-id="' . $itemId . '"
                   data-tgl="' . ($item->tgl_wawancara ?? '') . '"
                   data-isset="' . $isSet . '">
                    ' . $tglWawancaraFormatted . '
                </a>
            ';
            })
            // Kolom BARU: tombol interaktif untuk tanggal selesai
            ->addColumn('selesai', function ($item) {
                $tglFinalFormatted = $item->tgl_final ? Carbon::parse($item->tgl_final)->format('d-m-Y') : 'Pilih Tanggal';
                $itemId = $item->id;
                $isSet = $item->tgl_final ? 'true' : 'false';


                // Logika untuk menonaktifkan tombol jika tgl_wawancara null
                if (empty($item->tgl_wawancara)) {
                    return '<span class="disabled-link"
                            data-id="' . $itemId . '"
                            data-tgl="' . ($item->tgl_final ?? '') . '"
                            data-catatan="' . ($item->ket_wawancara ?? '') . '"
                            data-isset="' . $isSet . '">
                            -
                        </span>';
                } else {
                    return '
                    <a href="#" 
                       class="edit-selesai-btn" 
                       data-id="' . $itemId . '"
                       data-tgl="' . ($item->tgl_final ?? '') . '"
                       data-catatan="' . ($item->ket_wawancara ?? '') . '"
                       data-isset="' . $isSet . '">
                        ' . $tglFinalFormatted . '
                    </a>
                ';
                }
                //return '
                //    <a href="#" 
                //       class="edit-selesai-btn" 
                //       data-id="' . $itemId . '"
                //       data-tgl="' . ($item->tgl_final ?? '') . '"
                //       data-catatan="' . ($item->ket_wawancara ?? '') . '"
                //       data-isset="' . $isSet . '">
                //        ' . $tglFinalFormatted . '
                //    </a>
                //';
            })
            ->addColumn('ket_wawancara', function ($w) {
                // Membungkus catatan dengan tag span agar bisa di-styling
                return '<span class="text-wrap">' . $w->ket_wawancara . '</span>';
            })
            ->addColumn('aksi', function ($w) {
                return '<div class="btn-group">
                        <button type="button" class="btn btn-primary btn-xs dropdown-toggle" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Aksi</button>
                        <div class="dropdown-menu">                                                          
                            <a class="dropdown-item" href="#" onclick="hapus">Hapus</a>                        
                        </div>
                    </div>';
            })
            ->rawColumns(['aksi', 'status', 'daftar', 'wawancara', 'selesai', 'nama', 'ket_wawancara'])
            ->toJson();
    }
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validasi data awal tanpa memeriksa unik
        $validator = Validator::make($request->all(), [
            'nama' => 'required|string|max:255',
            'telp_pendaftar' => 'required|numeric',
            'suket' => 'required|file|mimes:pdf|max:2048',
            'lokasi_id' => 'required|integer|exists:lokasi,id',
        ], [
            'nama.required' => 'Nama lengkap harus diisi.',
            'telp_pendaftar.required' => 'No Telp / WhatsApp harus diisi.',
            'telp_pendaftar.numeric' => 'No Telp / WhatsApp harus berupa angka.',
            'suket.required' => 'Unggah Formulir Pendaftaran harus diisi.',
            'suket.mimes' => 'File harus berupa PDF.',
            'suket.max' => 'Ukuran file tidak boleh lebih dari 2MB.',
            'lokasi_id.required' => 'Lokasi harus dipilih (Kesalahan Form).', // <-- PESAN ERROR LOKASI
            'lokasi_id.exists' => 'ID Lokasi tidak valid.',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            $errorString = implode('<br>', $errors);
            return back()->with('gagal', $errorString)->withInput();
        }

        try {
            $pendaftar = Pendaftaran::all();

            return response()->json([
                'success' => true,
                'data' => PendaftaranResource::collection($pendaftar)
            ]);
        } catch (\Exception $e) {
            Log::error($e->getMessage());

            return response()->json([
                'sukses' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function ajax_DTpendaftar(Request $request)
    {
        try {
            $query = Pendaftaran::query()
                ->select(
                    'nama',
                    'id',
                    'telp_pendaftar',
                    'suket',
                    'status_daftar',
                    'tgl_daftar',
                    'tgl_wawancara',
                    'tgl_final',
                    'ket_wawancara',
                    'suket',
                    'updated_by'
                )
                ->orderBy('id', 'desc');

            $pendaftars = $query->get();

            $encryptedTexts = $pendaftars->pluck('nama')->merge($pendaftars->pluck('telp_pendaftar'))->toArray();
            $decryptedTextsMap = unsealNames($encryptedTexts);

            $pendaftars->transform(function ($item) use ($decryptedTextsMap) {
                $item->nama = $decryptedTextsMap[$item->nama] ?? 'Invalid response';
                $item->telp_pendaftar = $decryptedTextsMap[$item->telp_pendaftar] ?? 'Invalid response';
                return $item;
            });

            return response()->json([
                'sukses' => true,
                'data' => PendaftaranResource::collection($pendaftars)
            ]);
        } catch (\Exception $e) {
            Log::error($e->getMessage());

            return response()->json([
                'sukses' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function store(StorePendaftar $request)
    {
        try {
            DB::beginTransaction();

            $phoneHmac = generateHmac($request->telp_pendaftar);

            if (!$phoneHmac) {
                throw new Exception('Gagal mendapatkan HMAC dari API.');
            }

            $existingPendaftar = Pendaftaran::where('telp_pendaftar_hash', $phoneHmac)->first();
            if ($existingPendaftar) {
                DB::rollback();
                return response()->json([
                    'gagal' => 'No Telp / WhatsApp sudah terdaftar.'
                ]);
            }

            $filePath = $request->file('suket')->store('suket', 'public');

            $lokasiId = $request->lokasi_id;
            // Buat record pendaftar baru
            $data = new Pendaftaran();
            $data->nama = $request->nama;
            $data->telp_pendaftar = $request->telp_pendaftar;
            $data->telp_pendaftar_hash = $phoneHmac;
            $data->suket = $filePath;
            $data->lokasi_id = $lokasiId;
            $data->status_daftar = 1;
            $data->tgl_daftar = now();
            $data->save();

            DB::commit();
            return response()->json([
                'message' => 'Pendaftaran berhasil dilakukan',
                'data' => $data
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'error' => 'Terjadi kesalahan',
                'detail' => $e->getMessage()
            ]);
        }
    }


    public function updateTanggalWawancara(UpdatePendaftar $request, $id)
    {
        try {
            DB::beginTransaction();

            $pendaftar = Pendaftaran::find($id);

            if (!$pendaftar) {
                return response()->json([
                    'message' => 'Data tidak ditemukan'
                ]);
            }

            $pendaftar->tgl_wawancara = $request->tgl_wawancara;
            $pendaftar->ket_wawancara = $request->ket_wawancara;
            $pendaftar->tgl_final = $request->tgl_final;
            $pendaftar->status_daftar = 2;
            $pendaftar->save();
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Tanggal dan catatan wawancara berhasil diperbarui.'
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error update wawancara: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memperbarui data: ' . $e->getMessage()
            ]);
        }
    }

    public function updateTanggalSelesai(UpdateTanggalSelesaiPendaftaran $request, $id)
    {
        try {
            DB::beginTransaction();

            $pendaftar = Pendaftaran::findOrFail($id);
            $pendaftar->update([
                'tgl_final' => $request->tgl_final,
                'ket_wawancara' => $request->ket_wawancara,
                'status_daftar' => 3
            ]);

            DB::commit();

            return response()->json(['success' => true, 'message' => 'Tanggal dan catatan selesai berhasil diperbarui.']);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error update selesai: ' . $e->getMessage());

            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan saat memperbarui data: ' . $e->getMessage()]);
        }
    }

    public function destroy($id)
    {
        try {
            DB::beginTransaction();
            $pendaftar = Pendaftaran::findOrFail($id);

            if (!$pendaftar) {
                return response()->json([
                    'gagal' => 'Pendaftar tidak ditemukan'
                ]);
            }

            $pendaftar->delete();
            return response()->json([
                'sukses' => 'Pendaftar berhasil dihapus',
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'message' => 'Pendaftar tidak ditemukan'
            ]);
        }
    }
}
