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
        $userId = Auth::id();

        // Query untuk mendapatkan total unit dan total terkontrak per lokasi
        $dashboardData = DB::table('lokasi as l')
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
            ->leftJoin('kontrak as k', function($join) {
                $join->on('u.id', '=', 'k.unit_id')
                    ->where('k.status_kontrak', '=', 1);
            })
            ->groupBy('l.id', 'l.nama_lokasi')
            ->get();
        
        // Menambahkan persentase terhuni
        foreach ($dashboardData as $data) {
            $data->persentase_terhuni = 0;
            if ($data->total_unit > 0) {
                $data->persentase_terhuni = round(($data->total_terkontrak / $data->total_unit) * 100);
            }
        }
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
    }
}
