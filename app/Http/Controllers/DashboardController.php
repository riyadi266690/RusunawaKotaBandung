<?php

namespace App\Http\Controllers;

use App\Models\Kontrak;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Query untuk mendapatkan total unit dan total terkontrak per lokasi
        // Menggunakan nama-nama kolom dari ERD: nama_lokasi, nama_gedung
        $dashboardData = DB::table('lokasi as l')
            ->select(
                'l.nama_lokasi as lokasi_nama',
                DB::raw('COUNT(u.id) as total_unit'),
                DB::raw('COUNT(k.id) as total_terkontrak')
            )
            ->join('gedung as g', 'l.id', '=', 'g.lokasi_id')
            ->join('unit as u', 'g.id', '=', 'u.gedung_id')
            ->leftJoin('kontrak as k', function($join) {
                // Bergabung dengan kontrak yang statusnya aktif (status_kontrak = 1)
                $join->on('u.id', '=', 'k.unit_id')
                     ->where('k.status_kontrak', '=', 1);
            })
            ->groupBy('l.id', 'l.nama_lokasi')
            ->get();
        
        // Menambahkan persentase terhuni ke setiap objek data
        foreach ($dashboardData as $data) {
            $data->persentase_terhuni = 0;
            if ($data->total_unit > 0) {
                $data->persentase_terhuni = round(($data->total_terkontrak / $data->total_unit) * 100);
            }
        }
          // --- Logika untuk Mengambil Data Prakiraan dari Kontrak Asli ---
        $forecastData = [
            'labels' => [],
            'data' => [],
        ];
        
        // Ambil semua kontrak aktif dari database
        //$activeContracts = Kontrak::where('status_kontrak', 1)->get();
        
// Ambil semua kontrak (aktif dan tidak aktif) dari database
$allContracts = Kontrak::all();
        // Tentukan periode prakiraan (misalnya, 12 bulan ke depan)
        $numMonths = 12;
        $currentDate = Carbon::now();

        for ($i = 0; $i < $numMonths; $i++) {
    $forecastDate = $currentDate->copy()->addMonths($i);
    
    $monthlyIncome = 0;
    
    foreach ($allContracts as $contract) {
        $contractStartDate = Carbon::parse($contract->tgl_awal);
        
        // Periksa status kontrak dan tentukan tanggal akhir yang sesuai
        if ($contract->status_kontrak == 1) { // Kontrak aktif
            $contractEndDate = Carbon::parse($contract->tgl_akhir);
        } else { // Kontrak tidak aktif
            $contractEndDate = Carbon::parse($contract->tgl_keluar);
        }

        // Periksa apakah kontrak masih berlaku dalam periode prakiraan
        if ($contractStartDate->lte($forecastDate) && $contractEndDate->gte($forecastDate)) {
            $monthlyIncome += $contract->harga_sewa;
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
    }
}
