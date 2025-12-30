<?php

namespace App\Http\Controllers;

use App\Models\Kontrak;
use App\Models\Pendaftaran;
use App\Models\Penghuni;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
<<<<<<< HEAD
        $summary = [
            'total_pendaftar' => Pendaftaran::count(),
            'total_penghuni'  => Penghuni::count(),
            'total_menunggu'  => Pendaftaran::where('status_daftar', 1)->count(),
            'total_unit'      => DB::table('unit')->count(),
        ];

        $statusCounts = Pendaftaran::selectRaw('status_daftar, count(*) as total')
            ->groupBy('status_daftar')
            ->pluck('total', 'status_daftar')->all();

        $chartStatusData = [
            $statusCounts[1] ?? 0,
            $statusCounts[2] ?? 0,
            $statusCounts[3] ?? 0,
            $statusCounts[4] ?? 0
        ];

        $lokasiData = DB::table('lokasi as l')
=======
        $userId = Auth::id();

        // Query untuk mendapatkan total unit dan total terkontrak per lokasi
        $dashboardData = DB::table('lokasi as l')
>>>>>>> 5c6a2b57844312b01f859e1310df9b6d880a8244
            ->select(
                'l.nama_lokasi as lokasi_nama',
                DB::raw('COUNT(u.id) as total_unit'),
                DB::raw('COUNT(k.id) as total_terkontrak')
            )
            // --- FILTER LOKASI BERDASARKAN USER YANG LOGIN ---
            ->join('lokasi_user as lu', 'l.id', '=', 'lu.lokasi_id')
            ->where('lu.user_id', $userId)
            // --------------------------------------------------
            
            ->join('gedung as g', 'l.id', '=', 'g.lokasi_id')
            ->join('unit as u', 'g.id', '=', 'u.gedung_id')
<<<<<<< HEAD
            ->leftJoin('kontrak as k', function ($join) {
=======
            ->leftJoin('kontrak as k', function($join) {
>>>>>>> 5c6a2b57844312b01f859e1310df9b6d880a8244
                $join->on('u.id', '=', 'k.unit_id')
                    ->where('k.status_kontrak', '=', 1);
            })
            ->groupBy('l.id', 'l.nama_lokasi')
            ->get();
<<<<<<< HEAD

        foreach ($lokasiData as $data) {
=======
        
        // Menambahkan persentase terhuni
        foreach ($dashboardData as $data) {
>>>>>>> 5c6a2b57844312b01f859e1310df9b6d880a8244
            $data->persentase_terhuni = 0;
            if ($data->total_unit > 0) {
                $data->persentase_terhuni = round(($data->total_terkontrak / $data->total_unit) * 100);
            }
        }
<<<<<<< HEAD

        $forecastData = [
            'labels' => [],
            'data' => [],
        ];

        $allContracts = Kontrak::all();
        $numMonths = 12;
        $currentDate = Carbon::now();

        for ($i = 0; $i < $numMonths; $i++) {
            $forecastDate = $currentDate->copy()->addMonths($i)->startOfMonth();
            $forecastLabel = $forecastDate->format('M Y');

            $monthlyIncome = 0;

            foreach ($allContracts as $contract) {
                $contractStartDate = Carbon::parse($contract->tgl_awal);

                if ($contract->status_kontrak == 1) {
                    $contractEndDate = Carbon::parse($contract->tgl_akhir);
                } else {
                    $contractEndDate = $contract->tgl_keluar ? Carbon::parse($contract->tgl_keluar) : Carbon::parse($contract->tgl_akhir);
                }

                if ($forecastDate->betweenIncluded($contractStartDate->startOfMonth(), $contractEndDate->endOfMonth())) {
                    $monthlyIncome += $contract->harga_sewa;
                }
            }

            $forecastData['labels'][] = $forecastLabel;
            $forecastData['data'][] = $monthlyIncome;
        }

        return response()->json([
            'summary' => $summary,
            'chart_status' => $chartStatusData,
            'lokasi_stats' => $lokasiData,
            'revenue_forecast' => $forecastData
        ]);
=======
        $forecastData = [
        'labels' => [],
        'data' => [],
    ];
    
    // AMBIL SEMUA KONTRAK YANG DI AKSES USER (Menggunakan Scope Eloquent)
    $allContracts = Kontrak::aksesUser()->get();

    // Tentukan periode prakiraan (misalnya, 12 bulan ke depan)
    $numMonths = 12;
    $currentDate = Carbon::now();

    for ($i = 0; $i < $numMonths; $i++) {
        $forecastDate = $currentDate->copy()->addMonths($i);
        
        $monthlyIncome = 0;
        
        foreach ($allContracts as $contract) {
            $contractStartDate = Carbon::parse($contract->tgl_awal);
            
            // Tentukan tanggal akhir yang sesuai
            $contractEndDate = $contract->status_kontrak == 1 
                               ? Carbon::parse($contract->tgl_akhir) 
                               : Carbon::parse($contract->tgl_keluar);
            
            // Periksa apakah kontrak masih berlaku dalam periode prakiraan
            if ($contractStartDate->lte($forecastDate) && $contractEndDate->gte($forecastDate)) {
                // HANYA TAMBAHKAN JIKA STATUS KONTRAK AKTIF (1)
                // ATAU JIKA INGIN MEMASUKKAN PENDAPATAN DARI KONTRAK YANG SUDAH BERAKHIR (0),
                // tetapi perhitungannya harus sesuai dengan masa berlaku.
                
                // Disarankan: Untuk forecast pendapatan masa depan, hanya kontrak AKTIF yang relevan
                if ($contract->status_kontrak == 1) {
                     $monthlyIncome += $contract->harga_sewa;
                }
            }
        }
        
        $forecastData['labels'][] = $forecastDate->format('M Y');
        $forecastData['data'][] = $monthlyIncome;
    }

    // Mengirim data ke view
    return view('dashboard.index', compact('dashboardData','forecastData'));
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
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
>>>>>>> 5c6a2b57844312b01f859e1310df9b6d880a8244
    }
}
