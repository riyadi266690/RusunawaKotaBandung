<?php

namespace App\Http\Controllers;

use App\Http\Requests\Kontrak\StoreKontrak;
use App\Http\Requests\Kontrak\UpdateKontrak;
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
    public function kontrakAktif()
    {
        $kontrak = Kontrak::with(['unit.gedung.lokasi', 'penghuni'])
            ->where('status_kontrak', 1)
            ->orderBy('id', 'desc')
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'no_kontrak' => $item->no_kontrak,
                    'nama_pihak1' => $item->nama_pihak1,
                    'nama_penghuni' => $item->penghuni->nama ?? '-',
                    'lokasi_nama' => $item->unit->gedung->lokasi->nama_lokasi ?? '-',
                    'gedung_nama' => $item->unit->gedung->nama_gedung ?? '-',
                    'unit_lantai' => $item->unit->lantai ?? '-',
                    'unit_nomor' => $item->unit->nomor ?? '-',
                    'tgl_awal' => $item->tgl_awal,
                    'tgl_akhir' => $item->tgl_akhir,
                    'harga_sewa' => $item->harga_sewa,
                    'dok_kontrak' => $item->dok_kontrak
                ];
            });

        return response()->json(['message' => 'Data ditemukan', 'data' => $kontrak]);
    }

    public function store(StoreKontrak $request)
    {
        try {
            DB::beginTransaction();

            $data = $request->validated();
            $data['status_kontrak'] = 1;

            $kontrak = Kontrak::create($data);
            $kontrakId = $kontrak->id;

            $this->generateDocument($kontrak, $data['tipe_kontrak']);

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Kontrak berhasil dibuat.']);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error($e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    private function generateDocument($kontrak, $tipe)
    {
        $filepath = storage_path('app/public/kontrak/' . $kontrak->id . '/');
        if (!File::exists($filepath)) File::makeDirectory($filepath, 0777, true, true);

        $templatePath = ($tipe == 1)
            ? public_path('template_document/HUNIAN.docx')
            : public_path('template_document/RBH.docx');

        if (file_exists($templatePath)) {
            $templateProcessor = new TemplateProcessor($templatePath);

            $templateProcessor->setValues([
                'no_kontrak' => $kontrak->no_kontrak,
                'nama_pihak1' => strtoupper($kontrak->nama_pihak1),
                'nama_penghuni' => strtoupper($kontrak->penghuni->nama ?? '-'),
                'nik_penghuni' => $kontrak->penghuni->nik ?? '-',
                'alamat_penghuni' => $kontrak->penghuni->alamat ?? '-',
                'pekerjaan_penghuni' => $kontrak->penghuni->pekerjaan ?? '-',
                'harga_sewa' => number_format($kontrak->harga_sewa, 0, ',', '.'),
                'tgl_awal' => Carbon::parse($kontrak->tgl_awal)->translatedFormat('d F Y'),
                'tgl_akhir' => Carbon::parse($kontrak->tgl_akhir)->translatedFormat('d F Y'),
            ]);

            $docxPath = $filepath . $kontrak->id . '.docx';
            $templateProcessor->saveAs($docxPath);

            try {
                $converterPath = PHP_OS_FAMILY === 'Windows' ? 'C:\Program Files\LibreOffice\program\soffice' : null;
                $convert = new OfficeConverter($docxPath, null, $converterPath, false);
                $convert->convertTo($kontrak->id . '.pdf');

                $kontrak->update([
                    'dok_kontrak' => 'kontrak/' . $kontrak->id . '/' . $kontrak->id . '.pdf',
                    'status_ttd' => 1
                ]);
            } catch (\Exception $e) {
                Log::warning('PDF Convert failed: ' . $e->getMessage());
            }
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
        $units = Unit::select('unit.id', 'unit.nomor', 'unit.lantai', 'gedung.nama_gedung', 'lokasi.nama_lokasi')
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

    public function kontrakNonAktif()
    {
        $kontrak = Kontrak::with(['unit.gedung.lokasi', 'penghuni'])
            ->where('status_kontrak', 0)
            ->orderBy('tgl_keluar', 'desc')
            ->orderBy('updated_at', 'desc')
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'no_kontrak' => $item->no_kontrak,
                    'nama_penghuni' => $item->penghuni->nama ?? 'Penghuni Terhapus',
                    'lokasi_nama' => $item->unit->gedung->lokasi->nama_lokasi ?? '-',
                    'gedung_nama' => $item->unit->gedung->nama_gedung ?? '-',
                    'unit_lantai' => $item->unit->lantai ?? '-',
                    'unit_nomor' => $item->unit->nomor ?? '-',
                    'tgl_awal' => $item->tgl_awal,
                    'tgl_akhir' => $item->tgl_akhir,
                    'tgl_keluar' => $item->tgl_keluar,
                    'masa_kontrak' => $item->masa_kontrak,
                    'dok_kontrak' => $item->dok_kontrak,
                    'keterangan' => 'Kontrak Berakhir/Diputus'
                ];
            });

        return response()->json([
            'success' => true,
            'message' => 'Data riwayat ditemukan',
            'data' => $kontrak
        ]);
    }
}
