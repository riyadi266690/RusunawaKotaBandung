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
        return view('kontrak.aktif');
    }
    public function kontrakNonAktif()
    {
        return view('kontrak.non_aktif');
    }
    /**
     * Memproses data kontrak aktif untuk DataTables.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function ajax_DTKontrakNonAktif(Request $request)
    {
        // Menggunakan query builder tanpa get()
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
                // Pastikan untuk menambahkan kolom foreign key di sini
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
            // Eager load relasi penghuni
            ->with('penghuni1', 'penghuni2', 'penghuni3', 'penghuni4')
            ->join('unit', 'kontrak.unit_id', '=', 'unit.id')
            ->join('gedung', 'unit.gedung_id', '=', 'gedung.id')
            ->join('lokasi', 'gedung.lokasi_id', '=', 'lokasi.id')
            ->where('kontrak.status_kontrak', 0)
            ->orderBy('kontrak.id', 'desc');

        // Berikan query builder ke DataTables
        return DataTables::of($query)
            // Kolom untuk menampilkan data
            
            ->addColumn('unit_info', function ($kontrak) {
                return $kontrak->unit_nomor . ' (Lantai ' . $kontrak->unit_lantai . ' - ' . $kontrak->unit_tipe_unit . ') <br> ' . $kontrak->gedung_nama . '<br> (' . $kontrak->lokasi_nama . ')';
            })
            ->addColumn('tipe_kontrak_label', function ($kontrak) {
                return $kontrak->tipe_kontrak == 1 ? 'Unit Hunian' : 'Unit RBH';
            })
            ->addColumn('status_ttd_label', function ($kontrak) {
                return $kontrak->status_ttd == 1 ? 'Sudah TTD' : 'Draft';
            })
            ->addColumn('masa_kontrak', function ($kontrak) {
                $tglAwal = Carbon::parse($kontrak->tgl_awal);
                $tglAkhir = $kontrak->tgl_keluar ? Carbon::parse($kontrak->tgl_keluar) : Carbon::parse($kontrak->tgl_akhir);
                return $tglAwal->diffInMonths($tglAkhir) . ' bulan';
            })
            ->editColumn('no_kontrak', function ($kontrak) {
                // Menampilkan nomor kontrak dan tautan dokumen dalam satu kolom
                $dokumenLink = $kontrak->dok_kontrak 
                    ? '<br><a href="' . asset('storage/' . $kontrak->dok_kontrak) . '" target="_blank">Lihat Dokumen</a>' 
                    : '-';
                return $kontrak->no_kontrak . $dokumenLink;
            })
            ->editColumn('tgl_awal', function ($kontrak) {
                return Carbon::parse($kontrak->tgl_awal)->translatedFormat('d F Y');
            })
            ->editColumn('tgl_akhir', function ($kontrak) {
                return Carbon::parse($kontrak->tgl_akhir)->translatedFormat('d F Y');
            })
            ->editColumn('tgl_keluar', function ($kontrak) {
                return Carbon::parse($kontrak->tgl_keluar)->translatedFormat('d F Y');
            })
            // Kolom untuk menampilkan nama penghuni yang sudah didekripsi oleh accessor
            ->editColumn('penghuni1_nama', function ($kontrak) {
                return optional($kontrak->penghuni1)->nama ?? '-';
            })
            ->editColumn('penghuni2_nama', function ($kontrak) {
                return optional($kontrak->penghuni2)->nama ?? '-';
            })
            ->editColumn('penghuni3_nama', function ($kontrak) {
                return optional($kontrak->penghuni3)->nama ?? '-';
            })
            ->editColumn('penghuni4_nama', function ($kontrak) {
                return optional($kontrak->penghuni4)->nama ?? '-';
            })
             // Tambahkan kolom baru untuk data child row
             ->addColumn('details', function ($kontrak) {
                // Mengambil nilai dari relasi dan memberikan nilai default jika null
                $penghuni1 = $kontrak->penghuni1->nama ?? '-';
                $penghuni2 = $kontrak->penghuni2->nama ?? '-';
                $penghuni3 = $kontrak->penghuni3->nama ?? '-';
                $penghuni4 = $kontrak->penghuni4->nama ?? '-';
                
                // Menghitung sisa hari dari tanggal saat ini ke tanggal akhir
                $tglAkhir = Carbon::parse($kontrak->tgl_akhir);
                $sisaHari = now()->diffInDays($tglAkhir);

                return [
                    'Masa Kontrak' => floor($sisaHari) . ' hari tersisa',
                    'Status TTD' => $kontrak->status_ttd == 1 ? 'Sudah TTD' : 'Draft',
                    'Harga Sewa' => number_format($kontrak->harga_sewa, 0, ',', '.') . ' ('. terbilang($kontrak->harga_sewa).' Rupiah)',
                    'Pihak 1' => $kontrak->nama_pihak1 ?? '-',
                    'Penghuni 1' => $penghuni1,
                    'Penghuni 2' => $penghuni2,
                    'Penghuni 3' => $penghuni3,
                    'Penghuni 4' => $penghuni4,
                ];
            })
            // Kolom untuk aksi
            ->addColumn('aksi', function ($row) {
                    $btn = '<div class="btn-group" role="group">';
                    //$btn .= '<button type="button" class="btn btn-warning btn-sm" onclick="editKontrak(' . $row->id . ')">Edit</button>';
                    // Tombol baru untuk memutuskan kontrak
                    $btn .= '<button type="button" class="btn btn-info btn-sm" onclick="putusKontrak(' . $row->id . ')">Putus</button>';
                    $btn .= '<button type="button" class="btn btn-danger btn-sm" onclick="hapusKontrak(' . $row->id . ')">Hapus</button>';
                    $btn .= '</div>';
                    return $btn;
                })
             ->filter(function ($query) use ($request) {
                if ($keyword = $request->get('search')['value']) {
                    // Lakukan pencarian langsung pada kolom no_kontrak
                    $query->where('kontrak.no_kontrak', 'like', "%{$keyword}%");
                }
            })
            ->rawColumns(['aksi', 'unit_info','no_kontrak'])
            ->toJson();
    }
    public function ajax_DTKontrakAktif(Request $request)
    {
        // Menggunakan query builder tanpa get()
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
                // Pastikan untuk menambahkan kolom foreign key di sini
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
            // Eager load relasi penghuni
            ->with('penghuni1', 'penghuni2', 'penghuni3', 'penghuni4')
            ->join('unit', 'kontrak.unit_id', '=', 'unit.id')
            ->join('gedung', 'unit.gedung_id', '=', 'gedung.id')
            ->join('lokasi', 'gedung.lokasi_id', '=', 'lokasi.id')
            ->where('kontrak.status_kontrak', 1)
            ->orderBy('kontrak.id', 'desc');

        // Berikan query builder ke DataTables
        return DataTables::of($query)
            // Kolom untuk menampilkan data
            
            ->addColumn('unit_info', function ($kontrak) {
                return $kontrak->unit_nomor . ' (Lantai ' . $kontrak->unit_lantai . ' - ' . $kontrak->unit_tipe_unit . ') <br> ' . $kontrak->gedung_nama . '<br> (' . $kontrak->lokasi_nama . ')';
            })
            ->addColumn('tipe_kontrak_label', function ($kontrak) {
                return $kontrak->tipe_kontrak == 1 ? 'Unit Hunian' : 'Unit RBH';
            })
            ->addColumn('status_ttd_label', function ($kontrak) {
                return $kontrak->status_ttd == 1 ? 'Sudah TTD' : 'Draft';
            })
            ->addColumn('masa_kontrak', function ($kontrak) {
                $tglAwal = Carbon::parse($kontrak->tgl_awal);
                $tglAkhir = $kontrak->tgl_keluar ? Carbon::parse($kontrak->tgl_keluar) : Carbon::parse($kontrak->tgl_akhir);
                return $tglAwal->diffInMonths($tglAkhir) . ' bulan';
            })
            ->editColumn('no_kontrak', function ($kontrak) {
                // Menampilkan nomor kontrak dan tautan dokumen dalam satu kolom
                $dokumenLink = $kontrak->dok_kontrak 
                    ? '<br><a href="' . asset('storage/' . $kontrak->dok_kontrak) . '" target="_blank">Lihat Dokumen</a>' 
                    : '-';
                return $kontrak->no_kontrak . $dokumenLink;
            })
            ->editColumn('tgl_awal', function ($kontrak) {
                return Carbon::parse($kontrak->tgl_awal)->translatedFormat('d F Y');
            })
            ->editColumn('tgl_akhir', function ($kontrak) {
                return Carbon::parse($kontrak->tgl_akhir)->translatedFormat('d F Y');
            })
            // Kolom untuk menampilkan nama penghuni yang sudah didekripsi oleh accessor
            ->editColumn('penghuni1_nama', function ($kontrak) {
                return optional($kontrak->penghuni1)->nama ?? '-';
            })
            ->editColumn('penghuni2_nama', function ($kontrak) {
                return optional($kontrak->penghuni2)->nama ?? '-';
            })
            ->editColumn('penghuni3_nama', function ($kontrak) {
                return optional($kontrak->penghuni3)->nama ?? '-';
            })
            ->editColumn('penghuni4_nama', function ($kontrak) {
                return optional($kontrak->penghuni4)->nama ?? '-';
            })
             // Tambahkan kolom baru untuk data child row
             ->addColumn('details', function ($kontrak) {
                // Mengambil nilai dari relasi dan memberikan nilai default jika null
                $penghuni1 = $kontrak->penghuni1->nama ?? '-';
                $penghuni2 = $kontrak->penghuni2->nama ?? '-';
                $penghuni3 = $kontrak->penghuni3->nama ?? '-';
                $penghuni4 = $kontrak->penghuni4->nama ?? '-';
                
                // Menghitung sisa hari dari tanggal saat ini ke tanggal akhir
                $tglAkhir = Carbon::parse($kontrak->tgl_akhir);
                $sisaHari = now()->diffInDays($tglAkhir);

                return [
                    'Masa Kontrak' => floor($sisaHari) . ' hari tersisa',
                    'Status TTD' => $kontrak->status_ttd == 1 ? 'Sudah TTD' : 'Draft',
                    'Harga Sewa' => number_format($kontrak->harga_sewa, 0, ',', '.') . ' ('. terbilang($kontrak->harga_sewa).' Rupiah)',
                    'Pihak 1' => $kontrak->nama_pihak1 ?? '-',
                    'Penghuni 1' => $penghuni1,
                    'Penghuni 2' => $penghuni2,
                    'Penghuni 3' => $penghuni3,
                    'Penghuni 4' => $penghuni4,
                ];
            })
            // Kolom untuk aksi
            ->addColumn('aksi', function ($row) {
                    $btn = '<div class="btn-group" role="group">';
                    //$btn .= '<button type="button" class="btn btn-warning btn-sm" onclick="editKontrak(' . $row->id . ')">Edit</button>';
                    // Tombol baru untuk memutuskan kontrak
                    $btn .= '<button type="button" class="btn btn-info btn-sm" onclick="putusKontrak(' . $row->id . ')">Putus</button>';
                    $btn .= '<button type="button" class="btn btn-danger btn-sm" onclick="hapusKontrak(' . $row->id . ')">Hapus</button>';
                    $btn .= '</div>';
                    return $btn;
                })
             ->filter(function ($query) use ($request) {
                if ($keyword = $request->get('search')['value']) {
                    // Lakukan pencarian langsung pada kolom no_kontrak
                    $query->where('kontrak.no_kontrak', 'like', "%{$keyword}%");
                }
            })
            ->rawColumns(['aksi', 'unit_info','no_kontrak'])
            ->toJson();
    }

    /**
     * Menyimpan data kontrak baru.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        //dd($request->all());
        $validator = Validator::make($request->all(), [
            'unit_id' => 'required|exists:unit,id|unique:kontrak,unit_id,NULL,id,status_kontrak,1',
            'no_kontrak' => 'required|string|max:255|unique:kontrak,no_kontrak',
            'tipe_kontrak' => 'required|integer|in:1,2', // 1: Hunian, 2: RBH
            'harga_sewa' => 'required|integer',
            'harga_air' => 'nullable|integer',
            'jenis_usaha' => 'nullable|string|max:255',
            'luas_usaha' => 'nullable|numeric',
            'tgl_awal' => 'required|date',
            'tgl_akhir' => 'required|date|after_or_equal:tgl_awal',
            'nama_pihak1' => 'required|string|max:255',
            'status_ttd' => 'required|integer|in:0,1',
            'penghuni_id1' => ['required', 'exists:penghuni,id',
                // Aturan validasi kustom untuk memastikan penghuni hanya punya 1 kontrak aktif per tipe
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
            $data['status_kontrak'] = 1; // Selalu 1 untuk kontrak aktif
            // Masa kontrak dihitung di frontend atau di DataTables, tidak perlu disimpan di sini
            unset($data['masa_kontrak']); 
            unset($data['_method']); // Hapus _method dari data yang akan disimpan

            $kontrak = Kontrak::create($data);
             // Setelah model dibuat, properti `id` akan terisi otomatis
            $kontrakId = $kontrak->id;
            $filepath = storage_path('app/public/kontrak/'.$kontrakId.'/');
                     if (!File::exists($filepath)) {
                        File::makeDirectory($filepath, 0777, true, true);
                    }
            $totalHarga = ($kontrak->harga_sewa ?? 0) + ($kontrak->harga_air ?? 0);
            //tambahkan fungsi pembuatan dokumen kontrak di sini
            switch ($data['tipe_kontrak']) {
                case 1:                    
                    // Panggil fungsi untuk membuat dokumen kontrak unit hunian
                    $templatePath = public_path('template_document/HUNIAN.docx');
                    $templateProcessor = new TemplateProcessor($templatePath);
                    $templateProcessor->setValues([
                        'tahun'             => date('Y'), //tahun saat ini date('Y'), 
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
                        'harga_sewa'        => number_format($kontrak->harga_sewa, 0, ',', '.'), // 'harga_sewa' diambil dari input
                        'harga_sewa_bahasa' => ucwords(terbilang($kontrak->harga_sewa)).' Rupiah', //fungsi terbilang ada di helper.php
                        'tgl_akhir'         => Carbon::parse($kontrak->tgl_akhir)->translatedFormat('d F Y'),
                        'tgl_awal_lengkap'  => Carbon::parse($kontrak->tgl_awal)->translatedFormat('l, d F ').ucwords(terbilang(Carbon::parse($kontrak->tgl_awal)->year)),
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
                case 2:
// Panggil fungsi untuk membuat dokumen kontrak unit hunian
                    $templatePath = public_path('template_document/RBH.docx');                    
                    $templateProcessor = new TemplateProcessor($templatePath);
                    $templateProcessor->setValues([
                        'nama_lokasi'       => strtoupper($kontrak->unit->gedung->lokasi->nama_lokasi), 
                        'no_kontrak'        => $kontrak->no_kontrak,
                        'tgl_awal_lengkap'  => Carbon::parse($kontrak->tgl_awal)->translatedFormat('l, d F ').ucwords(terbilang(Carbon::parse($kontrak->tgl_awal)->year)),
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
                        'total_harga_eja'   => ucwords(terbilang($totalHarga)).' Rupiah',
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

    /**
     * Menampilkan data kontrak untuk diedit.
     *
     * @param  \App\Models\Kontrak  $kontrak
     * @return \Illuminate\Http\JsonResponse
     */
    public function edit(Kontrak $kontrak)
    {
        try {
            // Tambahkan tipe_kontrak_raw untuk mengisi input readonly di frontend
            $kontrak->tipe_kontrak_raw = ($kontrak->tipe_kontrak == 1) ? 'Hunian' : 'RBH';
            return response()->json(['success' => true, 'data' => $kontrak]);
        } catch (\Exception $e) {
            Log::error('Error fetching kontrak for edit: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['success' => false, 'message' => 'Gagal mengambil data kontrak: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Memperbarui data kontrak.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Kontrak  $kontrak
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Kontrak $kontrak)
    {
        $validator = Validator::make($request->all(), [
            'unit_id' => 'required|exists:unit,id|unique:kontrak,unit_id,' . $kontrak->id . ',id,status_kontrak,1',
            'no_kontrak' => 'required|string|max:255|unique:kontrak,no_kontrak,' . $kontrak->id,
            'tipe_kontrak' => 'required|integer|in:1,2', // 1: Hunian, 2: RBH
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
            // Tipe kontrak dari form adalah string 'Hunian' atau 'RBH', ubah ke integer
            $data['tipe_kontrak'] = ($request->tipe_kontrak == 'Hunian') ? 1 : 2;
            $data['status_kontrak'] = 1; // Selalu 1 untuk kontrak aktif
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

    /**
     * Menghapus data kontrak.
     *
     * @param  \App\Models\Kontrak  $kontrak
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Kontrak $kontrak)
     {
        try {
            // Mulai transaksi database untuk memastikan operasi atomik
            DB::beginTransaction();

            // Periksa apakah ada nama file dokumen kontrak yang tersimpan
            // Asumsi kolom `dok_kontrak` berisi path file di storage disk 'public'
            if ($kontrak->dok_kontrak) {
                // Ekstrak nama folder dari path file.
                // Misal, 'dokumen-kontrak/nama-unik-file.pdf'
                // kita ingin mendapatkan 'dokumen-kontrak'
                $directory = dirname($kontrak->dok_kontrak);

                // Hapus seluruh folder secara permanen dari disk
                // 'public' adalah nama disk yang digunakan
                Storage::disk('public')->deleteDirectory($directory);
            }

            // Lakukan soft delete pada model Kontrak
            $kontrak->delete(); 

            // Komit transaksi jika semua operasi berhasil
            DB::commit();

            return response()->json([
                'success' => true, 
                'message' => 'Data kontrak, file, dan folder berhasil dihapus.'
            ]);

        } catch (\Exception $e) {
            // Rollback transaksi jika terjadi kesalahan
            DB::rollback();
            
            Log::error('Error deleting kontrak: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            
            return response()->json([
                'success' => false, 
                'message' => 'Terjadi kesalahan saat menghapus data kontrak: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Mengambil daftar unit yang tersedia untuk dropdown kontrak.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUnitOptions()
    {
        try {
            // Ambil unit yang belum memiliki kontrak aktif (status_jual = 1)
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
            ->where('unit.status_jual', '1') // Hanya unit yang tersedia
            ->whereDoesntHave('kontrak', function ($query) {
                $query->where('status_kontrak', 1); // Tidak memiliki kontrak aktif
            })
            ->get();

            return response()->json(['success' => true, 'data' => $units]);
        } catch (\Exception $e) {
            Log::error('Error fetching unit options for contract: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['success' => false, 'message' => 'Gagal memuat opsi unit: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Mengambil detail unit (tipe_unit dan kepala_lokasi) berdasarkan unit_id.
     *
     * @param  \App\Models\Unit  $unit
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUnitDetails($unitId)
    {
         try {
            $unit = Unit::find($unitId);
            if ($unit) {
                // Perbaiki logika konversi dengan membersihkan string
                $tipe_unit_cleaned = strtolower(trim($unit->tipe_unit));
                
                // Konversi tipe_unit (string) menjadi tipe_kontrak (integer)
                $tipe_kontrak_int = ($tipe_unit_cleaned == "hunian") ? 1 : 2;

                return response()->json(['success' => true, 'data' => [
                    'tipe_kontrak_int' => $tipe_kontrak_int,
                    'tipe_kontrak_label' => $unit->tipe_unit, // Label untuk ditampilkan
                    'kepala_lokasi' => $unit->gedung->lokasi->kepala_lokasi ?? 'N/A',
                ]]);
            }
            return response()->json(['success' => false, 'message' => 'Unit tidak ditemukan.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Mengambil daftar penghuni untuk dropdown kontrak.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPenghuniOptions(Request $request)
    {
        try {
            // Ambil parameter pencarian dan IDs
            $search = trim($request->input('q'));
            $ids = $request->input('ids');

            // --- Logika untuk menemukan penghuni yang sudah memiliki 2 kontrak aktif ---
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
            // -------------------------------------------------------------------------
            
            $query = Penghuni::select('id', 'nama', 'nik', 'nik_hmac');
            
            // Tambahkan kondisi untuk mengecualikan penghuni yang sudah memiliki dua kontrak aktif
            $query->whereNotIn('id', $excludedIds);

            // Logika baru untuk menangani pencarian dan pemuatan awal
            if (!empty($search)) {
                // Hashing input pencarian menggunakan helper function kustom
                $hashedSearch = hmac($search);
                
                // Pastikan hashing berhasil sebelum melakukan pencarian
                if ($hashedSearch) {
                    // Hanya mencari berdasarkan NIK yang di-hash
                    $query->where('nik_hmac', $hashedSearch)
                          ->limit(20); // Batasi hasil pencarian
                } else {
                    // Jika gagal hash, kembalikan hasil kosong
                    return response()->json(['success' => true, 'data' => []]);
                }
                
            } elseif (!empty($ids)) {
                // Jika tidak ada pencarian, tapi ada IDs, muat data berdasarkan IDs
                $query->whereIn('id', $ids);
            } else {
                // Tanpa parameter, muat 20 data pertama
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
                // Catatan: Jika Anda memiliki kolom 'masa_kontrak_berjalan' di database dengan tipe data integer,
                // Anda bisa menyimpannya di sini.
                // Contoh: 'masa_kontrak_berjalan' => $masaKontrakBerjalan,
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
