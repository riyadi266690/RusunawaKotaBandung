<?php

namespace App\Http\Controllers;

use App\Models\Kontrak;
use App\Models\Penghuni;
use App\Models\Unit;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use NcJoes\OfficeConverter\OfficeConverter;
use PhpOffice\PhpWord\TemplateProcessor;
use Symfony\Component\HttpFoundation\Response;
use Yajra\DataTables\Facades\DataTables;

class KontrakController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }
    public function kontrakAktif()
    {
        $kontrak = Kontrak::where('status_kontrak', 1)->get();
        if ($kontrak->isEmpty()) {
            return response()->json([
                'message' => 'Data kosong',
            ]);
        }
        return response()->json([
            'message' => 'Data ditemukan',
            'data' => $kontrak
        ]);
    }
    public function kontrakNonAktif()
    {
        $kontrak = Kontrak::where('status_kontrak', 0)->get();
        if ($kontrak->isEmpty()) {
            return response()->json([
                'message' => 'Data kosong',
            ]);
        }
        return response()->json([
            'message' => 'Data ditemukan',
            'data' => $kontrak
        ]);
    }


    public function ajax_DTKontrakNonAktif(Request $request)
    {
        $query = Kontrak::query()
            ->select(
                'kontrak.id',
                'kontrak.no_kontrak',
                'kontrak.tipe_kontrak',
                'kontrak.tgl_awal',
                'kontrak.tgl_akhir',
                'kontrak.tgl_keluar',
                'kontrak.status_ttd',
                'kontrak.harga_sewa',
                'kontrak.nama_pihak1',
                'kontrak.dok_kontrak',
                'kontrak.penghuni_id1',
                'kontrak.penghuni_id2',
                'kontrak.penghuni_id3',
                'kontrak.penghuni_id4',
                'unit.nomor as unit_nomor',
                'unit.lantai as unit_lantai',
                'unit.tipe_unit as unit_tipe_unit',
                'gedung.nama_gedung as gedung_nama',
                'lokasi.nama_lokasi as lokasi_nama'
            )

            ->with('penghuni1', 'penghuni2', 'penghuni3', 'penghuni4')
            ->join('unit', 'kontrak.unit_id', '=', 'unit.id')
            ->join('gedung', 'unit.gedung_id', '=', 'gedung.id')
            ->join('lokasi', 'gedung.lokasi_id', '=', 'lokasi.id')
            ->where('kontrak.status_kontrak', 0)
            ->orderBy('kontrak.id', 'desc');

        $kontraks = $query->get();

        return response()->json([
            'message' => 'Data kontrak nonaktif ditemukan',
            'data' => $kontraks
        ]);
    }

    public function ajax_DTKontrakAktif(Request $request)
    {
        $query = Kontrak::query()
            ->select(
                'kontrak.id',
                'kontrak.no_kontrak',
                'kontrak.tipe_kontrak',
                'kontrak.tgl_awal',
                'kontrak.tgl_akhir',
                'kontrak.tgl_keluar',
                'kontrak.status_ttd',
                'kontrak.harga_sewa',
                'kontrak.nama_pihak1',
                'kontrak.dok_kontrak',
                'kontrak.penghuni_id1',
                'kontrak.penghuni_id2',
                'kontrak.penghuni_id3',
                'kontrak.penghuni_id4',
                'unit.nomor as unit_nomor',
                'unit.lantai as unit_lantai',
                'unit.tipe_unit as unit_tipe_unit',
                'gedung.nama_gedung as gedung_nama',
                'lokasi.nama_lokasi as lokasi_nama'
            )

            ->with('penghuni1', 'penghuni2', 'penghuni3', 'penghuni4')
            ->join('unit', 'kontrak.unit_id', '=', 'unit.id')
            ->join('gedung', 'unit.gedung_id', '=', 'gedung.id')
            ->join('lokasi', 'gedung.lokasi_id', '=', 'lokasi.id')
            ->where('kontrak.status_kontrak', 1)
            ->orderBy('kontrak.id', 'desc');

        $kontraks = $query->get();

        return response()->json([
            'message' => 'Data kontran aktif ditemukan',
            'data' => $kontraks
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'unit_id' => 'required|exists:unit,id|unique:kontrak,unit_id,NULL,id,status_kontrak,1',
            'no_kontrak' => 'required|string|max:255|unique:kontrak,no_kontrak',
            'tipe_kontrak' => 'required|integer|in:1,2',
            'harga_sewa' => 'required|integer',
            'harga_air' => 'nullable|integer',
            'jenis_usaha' => 'nullable|string|max:255',
            'luas_usaha' => 'nullable|numeric',
            'tgl_awal' => 'required|date',
            'tgl_akhir' => 'required|date|after_or_equal:tgl_awal',
            'nama_pihak1' => 'required|string|max:255',
            'status_ttd' => 'required|integer|in:0,1',
            'penghuni_id1' => [
                'required',
                'exists:penghuni,id',
                function ($attribute, $value, $fail) use ($request) {
                    $existing = Kontrak::where('status_kontrak', 1)
                        ->where(function ($query) use ($value) {
                            $query->where('penghuni_id1', $value)
                                ->orWhere('penghuni_id2', $value)
                                ->orWhere('penghuni_id3', $value)
                                ->orWhere('penghuni_id4', $value);
                        })
                        ->where('tipe_kontrak', $request->input('tipe_kontrak'))
                        ->first();

                    if ($existing) {
                        $fail("Penghuni yang dipilih sudah memiliki kontrak aktif dengan tipe yang sama.");
                    }
                }
            ],
            'penghuni_id2' => 'nullable|exists:penghuni,id|different:penghuni_id1|different:penghuni_id3|different:penghuni_id4',
            'penghuni_id3' => 'nullable|exists:penghuni,id|different:penghuni_id1|different:penghuni_id2|different:penghuni_id4',
            'penghuni_id4' => 'nullable|exists:penghuni,id|different:penghuni_id1|different:penghuni_id2|different:penghuni_id3',

        ], [
            'unit_id.required' => 'Unit harus dipilih.',
            'unit_id.exists' => 'Unit tidak valid.',
            'unit_id.unique' => 'Unit ini sudah memiliki kontrak aktif.',
            'no_kontrak.required' => 'Nomor kontrak harus diisi.',
            'no_kontrak.unique' => 'Nomor kontrak sudah ada.',
            'harga_sewa.required' => 'Harga sewa harus diisi.',
            'harga_sewa.integer' => 'Harga sewa harus berupa angka.',
            'harga_air.integer' => 'Harga air harus berupa angka.',
            'jenis_usaha.string' => 'Jenis usaha harus berupa teks.',
            'jenis_usaha.max' => 'Jenis usaha maksimal 255 karakter.',
            'luas_usaha.numeric' => 'Luas usaha harus berupa angka.',
            'tipe_kontrak.required' => 'Tipe kontrak harus diisi.',
            'tipe_kontrak.in' => 'Tipe kontrak tidak valid.',
            'tgl_awal.required' => 'Tanggal awal kontrak harus diisi.',
            'tgl_awal.date' => 'Format tanggal awal tidak valid.',
            'tgl_akhir.required' => 'Tanggal akhir kontrak harus diisi.',
            'tgl_akhir.date' => 'Format tanggal akhir tidak valid.',
            'tgl_akhir.after_or_equal' => 'Tanggal akhir harus setelah atau sama dengan tanggal awal.',
            'nama_pihak1.required' => 'Nama Pihak 1 harus diisi.',
            'status_ttd.required' => 'Status tanda tangan harus dipilih.',
            'status_ttd.in' => 'Status tanda tangan tidak valid.',
            'penghuni_id1.required' => 'Penghuni 1 harus dipilih.',
            'penghuni_id1.exists' => 'Penghuni 1 tidak valid.',
            'penghuni_id2.exists' => 'Penghuni 2 tidak valid.',
            'penghuni_id2.different' => 'Penghuni 2 tidak boleh sama dengan penghuni lainnya.',
            'penghuni_id3.exists' => 'Penghuni 3 tidak valid.',
            'penghuni_id3.different' => 'Penghuni 3 tidak boleh sama dengan penghuni lainnya.',
            'penghuni_id4.exists' => 'Penghuni 4 tidak valid.',
            'penghuni_id4.different' => 'Penghuni 4 tidak boleh sama dengan penghuni lainnya.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            DB::beginTransaction();

            $data = $request->all();
            $data['status_kontrak'] = 1;
            unset($data['masa_kontrak']);
            unset($data['_method']);

            $kontrak = Kontrak::create($data);
            $kontrakId = $kontrak->id;
            $filepath = storage_path('app/public/kontrak/' . $kontrakId . '/');
            if (!File::exists($filepath)) {
                File::makeDirectory($filepath, 0777, true, true);
            }
            $totalHarga = ($kontrak->harga_sewa ?? 0) + ($kontrak->harga_air ?? 0);
            switch ($data['tipe_kontrak']) {
                case 1:
                    $templatePath = public_path('template_document/HUNIAN.docx');
                    $templateProcessor = new TemplateProcessor($templatePath);
                    $templateProcessor->setValues([
                        'tahun'             => date('Y'),
                        'no_kontrak'        => $kontrak->no_kontrak,
                        'nama_pihak1'       => strtoupper($kontrak->nama_pihak1),
                        'nama_penghuni1'    => $kontrak->penghuni1 ? $kontrak->penghuni1->nama : '-',
                        'alamat'            => $kontrak->penghuni1 ? $kontrak->penghuni1->alamat : '-',
                        'tempat_lahir'      => $kontrak->penghuni1 ? $kontrak->penghuni1->tempat_lahir : '-',
                        'tgl_lahir'         => $kontrak->penghuni1 ? Carbon::parse($kontrak->penghuni1->tgl_lahir)->translatedFormat('d F Y') : '-',
                        'pekerjaan'         => $kontrak->penghuni1 ? $kontrak->penghuni1->pekerjaan : '-',
                        'nik'               => $kontrak->penghuni1 ? $kontrak->penghuni1->nik : '-',
                        'nama_gedung'       => $kontrak->unit->gedung->nama_gedung,
                        'lantai'            => $kontrak->unit->lantai,
                        'nomor'             => $kontrak->unit->nomor,
                        'harga_sewa'        => number_format($kontrak->harga_sewa, 0, ',', '.'),
                        'harga_sewa_bahasa' => ucwords(terbilang($kontrak->harga_sewa)) . ' Rupiah',
                        'tgl_akhir'         => Carbon::parse($kontrak->tgl_akhir)->translatedFormat('d F Y'),
                        'tgl_awal_lengkap'  => Carbon::parse($kontrak->tgl_awal)->translatedFormat('l, d F ') . ucwords(terbilang(Carbon::parse($kontrak->tgl_awal)->year)),
                    ]);
                    $templateProcessor->saveAs(storage_path('app/public/kontrak/' . $kontrakId . '/' . $kontrakId . '.docx'));
                    if (PHP_OS_FAMILY === 'Windows') {
                        $convert = new OfficeConverter(
                            storage_path('app/public/kontrak/' . $kontrakId . '/' . $kontrakId . '.docx'),
                            null,
                            'C:\Program Files\LibreOffice\program\soffice',
                            true
                        );
                    } else {
                        $convert = new OfficeConverter(
                            storage_path('app/public/kontrak/' . $kontrakId . '/' . $kontrakId . '.docx')
                        );
                    }
                    $convert->convertTo($kontrakId . '.pdf');
                    $kontrak->dok_kontrak = 'kontrak/' . $kontrakId . '/' . $kontrakId . '.pdf';
                    $kontrak->status_ttd = 1;
                    $kontrak->save();
                    break;
                case 2:
                    $templatePath = public_path('template_document/RBH.docx');
                    $templateProcessor = new TemplateProcessor($templatePath);
                    $templateProcessor->setValues([
                        'nama_lokasi'       => strtoupper($kontrak->unit->gedung->lokasi->nama_lokasi),
                        'no_kontrak'        => $kontrak->no_kontrak,
                        'tgl_awal_lengkap'  => Carbon::parse($kontrak->tgl_awal)->translatedFormat('l, d F ') . ucwords(terbilang(Carbon::parse($kontrak->tgl_awal)->year)),
                        'nama_pihak1'       => strtoupper($kontrak->nama_pihak1),
                        'nama_penghuni1'    => $kontrak->penghuni1 ? $kontrak->penghuni1->nama : '-',
                        'nik'               => $kontrak->penghuni1 ? $kontrak->penghuni1->nik : '-',
                        'alamat'            => $kontrak->penghuni1 ? $kontrak->penghuni1->alamat : '-',
                        'jenis_usaha'        => $kontrak->jenis_usaha ?? '-',
                        'nama_gedung'       => $kontrak->unit->gedung->nama_gedung,
                        'lantai'            => $kontrak->unit->lantai,
                        'nomor'             => $kontrak->unit->nomor,
                        'alamat_lokasi'     => $kontrak->unit->gedung->lokasi->alamat_lokasi,
                        'luas_usaha'        => $kontrak->luas_usaha ? $kontrak->luas_usaha : '-',
                        'total_harga'       => number_format($totalHarga, 0, ',', '.'),
                        'total_harga_eja'   => ucwords(terbilang($totalHarga)) . ' Rupiah',
                        'harga_sewa'        => number_format($kontrak->harga_sewa, 0, ',', '.'), // 'harga_sewa' diambil dari input
                        'harga_air'        => $kontrak->harga_air ? number_format($kontrak->harga_air, 0, ',', '.') : '-',
                    ]);
                    $templateProcessor->saveAs(storage_path('app/public/kontrak/' . $kontrakId . '/' . $kontrakId . '.docx'));
                    if (PHP_OS_FAMILY === 'Windows') {
                        $convert = new OfficeConverter(
                            storage_path('app/public/kontrak/' . $kontrakId . '/' . $kontrakId . '.docx'),
                            null,
                            'C:\Program Files\LibreOffice\program\soffice',
                            true
                        );
                    } else {
                        $convert = new OfficeConverter(
                            storage_path('app/public/kontrak/' . $kontrakId . '/' . $kontrakId . '.docx')
                        );
                    }
                    $convert->convertTo($kontrakId . '.pdf');


                    //update dok_kontrak di tabel kontrak
                    $kontrak->dok_kontrak = 'kontrak/' . $kontrakId . '/' . $kontrakId . '.pdf';
                    $kontrak->status_ttd = 1; //set status ttd ke 0 (draft) setiap buat kontrak baru
                    $kontrak->save();
                    break;
                default:
                    throw new \Exception('Tipe kontrak tidak valid.');
            }

            DB::commit();



            return response()->json(['success' => true, 'message' => 'Data kontrak berhasil ditambahkan.']);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error storing kontrak: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan saat menyimpan data kontrak: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function edit(Kontrak $kontrak)
    {
        try {
            $kontrak->tipe_kontrak_raw = ($kontrak->tipe_kontrak == 1) ? 'Hunian' : 'RBH';
            return response()->json(['success' => true, 'data' => $kontrak]);
        } catch (\Exception $e) {
            Log::error('Error fetching kontrak for edit: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['success' => false, 'message' => 'Gagal mengambil data kontrak: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function update(Request $request, Kontrak $kontrak)
    {
        $validator = Validator::make($request->all(), [
            'unit_id' => 'required|exists:unit,id|unique:kontrak,unit_id,' . $kontrak->id . ',id,status_kontrak,1',
            'no_kontrak' => 'required|string|max:255|unique:kontrak,no_kontrak,' . $kontrak->id,
            'tipe_kontrak' => 'required|integer|in:1,2',
            'tgl_awal' => 'required|date',
            'tgl_akhir' => 'required|date|after_or_equal:tgl_awal',
            'nama_pihak1' => 'required|string|max:255',
            'status_ttd' => 'required|integer|in:0,1',
            'penghuni_id1' => 'required|exists:penghuni,id',
            'penghuni_id2' => 'nullable|exists:penghuni,id|different:penghuni_id1|different:penghuni_id3|different:penghuni_id4',
            'penghuni_id3' => 'nullable|exists:penghuni,id|different:penghuni_id1|different:penghuni_id2|different:penghuni_id4',
            'penghuni_id4' => 'nullable|exists:penghuni,id|different:penghuni_id1|different:penghuni_id2|different:penghuni_id3',
        ], [
            'unit_id.required' => 'Unit harus dipilih.',
            'unit_id.exists' => 'Unit tidak valid.',
            'unit_id.unique' => 'Unit ini sudah memiliki kontrak aktif.',
            'no_kontrak.required' => 'Nomor kontrak harus diisi.',
            'no_kontrak.unique' => 'Nomor kontrak sudah ada.',
            'tipe_kontrak.required' => 'Tipe kontrak harus diisi.',
            'tipe_kontrak.in' => 'Tipe kontrak tidak valid.',
            'tgl_awal.required' => 'Tanggal awal kontrak harus diisi.',
            'tgl_awal.date' => 'Format tanggal awal tidak valid.',
            'tgl_akhir.required' => 'Tanggal akhir kontrak harus diisi.',
            'tgl_akhir.date' => 'Format tanggal akhir tidak valid.',
            'tgl_akhir.after_or_equal' => 'Tanggal akhir harus setelah atau sama dengan tanggal awal.',
            'nama_pihak1.required' => 'Nama Pihak 1 harus diisi.',
            'status_ttd.required' => 'Status tanda tangan harus dipilih.',
            'status_ttd.in' => 'Status tanda tangan tidak valid.',
            'penghuni_id1.required' => 'Penghuni 1 harus dipilih.',
            'penghuni_id1.exists' => 'Penghuni 1 tidak valid.',
            'penghuni_id2.exists' => 'Penghuni 2 tidak valid.',
            'penghuni_id2.different' => 'Penghuni 2 tidak boleh sama dengan penghuni lainnya.',
            'penghuni_id3.exists' => 'Penghuni 3 tidak valid.',
            'penghuni_id3.different' => 'Penghuni 3 tidak boleh sama dengan penghuni lainnya.',
            'penghuni_id4.exists' => 'Penghuni 4 tidak valid.',
            'penghuni_id4.different' => 'Penghuni 4 tidak boleh sama dengan penghuni lainnya.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            DB::beginTransaction();

            $data = $request->all();
            $data['tipe_kontrak'] = ($request->tipe_kontrak == 'Hunian') ? 1 : 2;
            $data['status_kontrak'] = 1;
            unset($data['masa_kontrak']);
            unset($data['_method']);

            $kontrak->update($data);
            DB::commit();
            return response()->json(['success' => true, 'message' => 'Data kontrak berhasil diperbarui.']);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error updating kontrak: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan saat memperbarui data kontrak: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function destroy(Kontrak $kontrak)
    {
        try {
            DB::beginTransaction();
            if ($kontrak->dok_kontrak) {
                $directory = dirname($kontrak->dok_kontrak);
                Storage::disk('public')->deleteDirectory($directory);
            }

            $kontrak->delete();
            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Data kontrak, file, dan folder berhasil dihapus.'
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error deleting kontrak: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menghapus data kontrak: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getUnitOptions()
    {
        try {
            $units = Unit::select(
                'unit.id',
                'unit.nomor',
                'unit.lantai',
                'unit.tipe_unit',
                'gedung.nama_gedung as gedung_nama',
                'lokasi.nama_lokasi as lokasi_nama'
            )
                ->join('gedung', 'unit.gedung_id', '=', 'gedung.id')
                ->join('lokasi', 'gedung.lokasi_id', '=', 'lokasi.id')
                ->where('unit.status_jual', '1')
                ->whereDoesntHave('kontrak', function ($query) {
                    $query->where('status_kontrak', 1);
                })
                ->get();

            return response()->json(['success' => true, 'data' => $units]);
        } catch (\Exception $e) {
            Log::error('Error fetching unit options for contract: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['success' => false, 'message' => 'Gagal memuat opsi unit: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    public function getUnitDetails($unitId)
    {
        try {
            $unit = Unit::find($unitId);
            if ($unit) {
                $tipe_unit_cleaned = strtolower(trim($unit->tipe_unit));

                $tipe_kontrak_int = ($tipe_unit_cleaned == "hunian") ? 1 : 2;

                return response()->json(['success' => true, 'data' => [
                    'tipe_kontrak_int' => $tipe_kontrak_int,
                    'tipe_kontrak_label' => $unit->tipe_unit,
                    'kepala_lokasi' => $unit->gedung->lokasi->kepala_lokasi ?? 'N/A',
                ]]);
            }
            return response()->json(['success' => false, 'message' => 'Unit tidak ditemukan.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }


    public function getPenghuniOptions(Request $request)
    {
        try {
            $search = trim($request->input('q'));
            $ids = $request->input('ids');

            // Cari penghuni yang punya kontrak tipe 1 dan aktif
            $penghuniWithTipe1 = Kontrak::where('status_kontrak', 1)
                ->where('tipe_kontrak', 1)
                ->pluck('penghuni_id1', 'penghuni_id2', 'penghuni_id3', 'penghuni_id4')
                ->flatten()
                ->unique()
                ->filter();

            // Cari penghuni yang punya kontrak tipe 2 dan aktif
            $penghuniWithTipe2 = Kontrak::where('status_kontrak', 1)
                ->where('tipe_kontrak', 2)
                ->pluck('penghuni_id1', 'penghuni_id2', 'penghuni_id3', 'penghuni_id4')
                ->flatten()
                ->unique()
                ->filter();

            // Dapatkan ID penghuni yang ada di kedua daftar (intersection)
            $excludedIds = $penghuniWithTipe1->intersect($penghuniWithTipe2)->toArray();

            $query = Penghuni::select('id', 'nama', 'nik', 'nik_hmac');

            $query->whereNotIn('id', $excludedIds);

            if (!empty($search)) {
                $hashedSearch = generateHmac($search);

                // Pastikan hashing berhasil sebelum melakukan pencarian
                if ($hashedSearch) {
                    $query->where('nik_hmac', $hashedSearch)
                        ->limit(20);
                } else {
                    return response()->json(['success' => true, 'data' => []]);
                }
            } elseif (!empty($ids)) {
                $query->whereIn('id', $ids);
            } else {
                $query->limit(20);
            }

            $penghunis = $query->get();

            // Siapkan format untuk Select2
            $results = $penghunis->map(function ($item) {
                // Tampilkan NIK yang tidak di-hash
                return [
                    'id' => $item->id,
                    'text' => $item->nama . ' (' . $item->nik . ')',
                ];
            })->toArray();

            return response()->json(['success' => true, 'data' => $results]);
        } catch (\Exception $e) {
            Log::error('Error fetching penghuni options for contract: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['success' => false, 'message' => 'Gagal memuat opsi penghuni: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    public function putusKontrak(Request $request, Kontrak $kontrak)
    {
        try {
            DB::beginTransaction();

            $validated = $request->validate([
                'tgl_keluar' => 'required|date|after_or_equal:tgl_awal', // Menambahkan validasi
            ]);

            $tglAwal = Carbon::parse($kontrak->tgl_awal);
            $tglKeluar = Carbon::parse($validated['tgl_keluar']);

            // Hitung selisih dalam hari
            $masaKontrakBerjalan = $tglAwal->diffInDays($tglKeluar);

            $kontrak->update([
                'status_kontrak' => 0,
                'tgl_keluar' => $validated['tgl_keluar'],
                'masa_kontrak' => $masaKontrakBerjalan,
            ]);

            Log::info("Kontrak #{$kontrak->id} diputus. Masa kontrak berjalan: {$masaKontrakBerjalan} hari.");

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Kontrak berhasil diputus!',
                'masa_kontrak_berjalan' => $masaKontrakBerjalan,
            ]);
        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal memutus kontrak: ' . $e->getMessage(),
                'errors' => $e->errors(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error putting kontrak: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memutus kontrak: ' . $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
