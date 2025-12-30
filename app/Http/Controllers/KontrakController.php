<?php

namespace App\Http\Controllers;

use App\Http\Requests\Kontrak\StoreKontrak;
use App\Http\Requests\Kontrak\UpdateKontrak;
use App\Http\Resources\Kontrak\KontrakResource;
use App\Models\Kontrak;
use App\Models\Penghuni;
use App\Models\Unit;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use NcJoes\OfficeConverter\OfficeConverter;
use PhpOffice\PhpWord\TemplateProcessor;

class KontrakController extends Controller
{
    public function kontrakAktif(Request $request)
    {
        $query = Kontrak::with([
            'unit.gedung.lokasi',
            'penghuni1', 
            'penghuni2', 
            'penghuni3', 
            'penghuni4'  
        ])
        ->where('status_kontrak', 1)
        ->orderBy('id', 'desc');

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('no_kontrak', 'like', "%{$search}%")
                  ->orWhereHas('penghuni1', function($subQ) use ($search) {
                      $subQ->where('nama', 'like', "%{$search}%");
                  });
            });
        }

        $kontrak = $query->get();

        return response()->json([
            'message' => 'Data ditemukan',
            'data' => KontrakResource::collection($kontrak)
        ]);
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
            $generated = $this->generateDocument($kontrak, $data['tipe_kontrak']);

            if (!$generated) {
                DB::commit(); 
                return response()->json([
                    'success' => true,
                    'message' => 'Kontrak tersimpan, tetapi dokumen gagal dibuat'
                ], 201);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Gagal simpan kontrak: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan data kontrak: ' . $e->getMessage()
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Kontrak dan dokumen berhasil dibuat'
        ], 201);
    }

private function generateDocument($kontrak, $data): bool
    {
        try {
            $kontrak->load('penghuni1', 'unit.gedung.lokasi');

            $dir = storage_path("app/public/kontrak/{$kontrak->id}");
            if (!File::exists($dir)) {
                File::makeDirectory($dir, 0777, true);
            }

            $template = $data == 1
                ? public_path('template_document/HUNIAN.docx')
                : public_path('template_document/RBH.docx');

            if (!file_exists($template)) {
                Log::error("Template tidak ditemukan: $template");
                return false;
            }

            $processor = new TemplateProcessor($template);

            $hargaTerbilang = function_exists('terbilang') 
                ? ucwords(terbilang($kontrak->harga_sewa)) . ' Rupiah' 
                : $kontrak->harga_sewa . ' Rupiah';

            $processor->setValues([
                'no_kontrak'       => $kontrak->no_kontrak,
                'nama_pihak1'      => strtoupper($kontrak->nama_pihak1),
                'nama_penghuni'    => strtoupper($kontrak->penghuni1->nama ?? '-'), // Ganti nama_penghuni1 jadi nama_penghuni
                'tempat_lahir'     => strtoupper($kontrak->penghuni1->tempat_lahir ?? '-'),
                'tgl_lahir'        => $kontrak->penghuni1->tgl_lahir ? Carbon::parse($kontrak->penghuni1->tgl_lahir)->translatedFormat('d F Y') : '-',
                'nik_penghuni'     => $kontrak->penghuni1->nik ?? '-',
                'alamat_penghuni'  => $kontrak->penghuni1->alamat ?? '-',
                'pekerjaan_penghuni' => $kontrak->penghuni1->pekerjaan ?? '-',
                'nomor'            => $kontrak->unit->nomor ?? '-',
                'lantai'           => $kontrak->unit->lantai ?? '-',
                'nama_gedung'      => $kontrak->unit->gedung->nama_gedung ?? '-',
                'lokasi'           => $kontrak->unit->gedung->lokasi->nama_lokasi ?? '-',
                'harga_sewa'       => number_format($kontrak->harga_sewa, 0, ',', '.'),
                'harga_sewa_bahasa'=> $hargaTerbilang, 
                'tahun'            => Carbon::now()->year,
                'tgl_awal'         => Carbon::parse($kontrak->tgl_awal)->translatedFormat('d F Y'),
                'tgl_akhir'        => Carbon::parse($kontrak->tgl_akhir)->translatedFormat('d F Y'),
            ]);

            $pathTtdPejabat = public_path('assets/images/favicon.png'); 

            if (file_exists($pathTtdPejabat)) {
                $processor->setImageValue('ttd_pihak1', [
                    'path' => $pathTtdPejabat,
                    'width' => 150,   
                    'height' => 80,   
                    'ratio' => true
                ]);
            } else {
                $processor->setValue('ttd_pihak1', ''); 
            }

            $processor->setValue('ttd_penghuni', ''); 
            $fileName = $kontrak->id . '.docx';
            $docxPath = "$dir/$fileName";
            $processor->saveAs($docxPath);

            $kontrak->update([
                'dok_kontrak' => "kontrak/{$kontrak->id}/$fileName",
                'status_ttd' => 1 
            ]);

            return true;

        } catch (\Throwable $e) {
            Log::error('Generate dokumen error: ' . $e->getMessage());
            return false;
        }
    }
    public function getPenghuniOptions(Request $request)
    {
        $query = Penghuni::select('id', 'nama', 'nik');
        if ($request->has('q')) {
            $query->where('nama', 'like', '%' . $request->q . '%')
                ->orWhere('nik', 'like', '%' . $request->q . '%');
        }

        $penghunis = $query->limit(20)->get()->map(function ($p) {
            return ['id' => $p->id, 'text' => $p->nama . ' (' . $p->nik . ')'];
        });

        return response()->json(['success' => true, 'data' => $penghunis]);
    }

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
            ->where('unit.status_jual', '1')
            ->whereDoesntHave('kontrak', function ($q) {
                $q->where('status_kontrak', 1);
            })
            ->get()
            ->map(function ($u) {
                $u->lokasi_nama = $u->nama_lokasi;
                $u->gedung_nama = $u->nama_gedung;
                return $u;
            });

        return response()->json(['success' => true, 'data' => $units]);
    }

    public function getUnitDetails($unitId)
    {
        $unit = Unit::with('gedung.lokasi')->find($unitId);
        if (!$unit) return response()->json(['success' => false]);

        $tipe = (strtolower(trim($unit->tipe_unit)) == "hunian") ? 1 : 2;
        return response()->json(['success' => true, 'data' => [
            'tipe_kontrak_int' => $tipe,
            'harga_bulan' => $unit->harga_bulan ?? 0,
            'kepala_lokasi' => $unit->gedung->lokasi->kepala_lokasi ?? 'Kepala UPTD'
        ]]);
    }

    public function putusKontrak(UpdateKontrak $request, Kontrak $kontrak)
    {
        $kontrak->update(['status_kontrak' => 0, 'tgl_keluar' => $request->tgl_keluar]);
        return response()->json(['success' => true]);
    }
}