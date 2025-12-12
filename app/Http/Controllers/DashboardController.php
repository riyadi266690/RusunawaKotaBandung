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
            ->select(
                'l.nama_lokasi as lokasi_nama',
                DB::raw('COUNT(u.id) as total_unit'),
                DB::raw('COUNT(k.id) as total_terkontrak')
            )
            ->join('gedung as g', 'l.id', '=', 'g.lokasi_id')
            ->join('unit as u', 'g.id', '=', 'u.gedung_id')
            ->leftJoin('kontrak as k', function($join) {
                $join->on('u.id', '=', 'k.unit_id')
                     ->where('k.status_kontrak', '=', 1);
            })
            ->groupBy('l.id', 'l.nama_lokasi')
            ->get();
        
        foreach ($lokasiData as $data) {
            $data->persentase_terhuni = 0;
            if ($data->total_unit > 0) {
                $data->persentase_terhuni = round(($data->total_terkontrak / $data->total_unit) * 100);
            }
        }

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
    }
}