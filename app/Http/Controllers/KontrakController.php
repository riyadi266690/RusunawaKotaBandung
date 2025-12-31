<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Unit;
use App\Models\User;
use App\Models\Kontrak;
use App\Models\Penghuni;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use PhpOffice\PhpWord\TemplateProcessor;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\Kontrak\StoreKontrak;
use NcJoes\OfficeConverter\OfficeConverter;
use App\Http\Requests\Kontrak\UpdateKontrak;
use App\Http\Resources\Kontrak\KontrakResource;
use App\Http\Requests\Dokumen\SignDocumentRequest;
use App\Http\Requests\Dokumen\UploadRevisiRequest;

use function PHPUnit\Framework\isFalse;

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
            $query->where(function ($q) use ($search) {
                $q->where('no_kontrak', 'like', "%{$search}%")
                    ->orWhereHas('penghuni1', function ($subQ) use ($search) {
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
        $kontrak = Kontrak::with([
            'unit.gedung.lokasi',
            'penghuni1',
            'penghuni2',
            'penghuni3',
            'penghuni4'
        ])
            ->where('status_kontrak', 0)
            ->orderBy('tgl_keluar', 'desc')
            ->orderBy('updated_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Data riwayat ditemukan',
            'data' => KontrakResource::collection($kontrak)
        ]);
    }

    public function store(StoreKontrak $request)
    {
        DB::beginTransaction();

        try {
            $data = $request->validated();
            $data['status_kontrak'] = 1;
            $kontrak = Kontrak::create($data);

            $penghuni = Penghuni::find($data['penghuni_1']);

            if ($penghuni && $penghuni->email) {
                $user = User::where('email', $penghuni->email)->first();

                if (!$user) {
                    $randomPassword = Str::random(8);

                    $user = User::create([
                        'name' => $penghuni->nama,
                        'email' => $penghuni->email,
                        'password' => Hash::make('password'),
                        'role_id' => 3,
                        'unit_id' => $data['unit_id']
                    ]);
                } else {
                    $user->update([
                        'unit_id' => $data['unit_id'],
                        'role_id' => 3
                    ]);
                }

                $penghuni->update(['user_id' => $user->id]);
            }

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
                'nama_penghuni'    => strtoupper($kontrak->penghuni1->nama ?? '-'),
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
                'harga_sewa_bahasa' => $hargaTerbilang,
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

    public function uploadRevisi(UploadRevisiRequest $request, $id)
    {
        try {
            $kontrak = Kontrak::findOrFail($id);

            $file = $request->file('file_revisi');
            $extension = $file->getClientOriginalExtension();
            $fileName = $kontrak->id . '_revisi_' . time() . '.' . $extension;
            $folderPath = 'kontrak/' . $kontrak->id;

            $path = $file->storeAs($folderPath, $fileName, 'public');

            $kontrak->update([
                'dok_kontrak' => $path,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Dokumen revisi berhasil diupload!',
                'path' => $path
            ]);
        } catch (\Exception $e) {
            Log::error("Upload Revisi Error: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Gagal upload: ' . $e->getMessage()], 500);
        }
    }

    public function signDocument(SignDocumentRequest $request, $id)
    {
        try {
            $kontrak = Kontrak::findOrFail($id);

            $relativePath = $kontrak->dok_kontrak;
            $fullPath = storage_path('app/public/' . $relativePath);

            if (!file_exists($fullPath)) {
                return response()->json(['success' => false, 'message' => 'Dokumen fisik tidak ditemukan.'], 404);
            }

            $image_parts = explode(";base64,", $request->signature_image);
            $image_base64 = base64_decode($image_parts[1]);

            $tempDir = storage_path('app/public/temp');
            if (!file_exists($tempDir)) mkdir($tempDir, 0777, true);

            $tempImageName = 'temp_ttd_' . $kontrak->id . '_' . time() . '.png';
            $tempImagePath = $tempDir . '/' . $tempImageName;

            file_put_contents($tempImagePath, $image_base64);

            $templateProcessor = new TemplateProcessor($fullPath);

            $templateProcessor->setImageValue('ttd_penghuni', [
                'path' => $tempImagePath,
                'width' => 200,
                'height' => 200,
                'ratio' => true
            ]);

            $tempOutputPath = $tempDir . '/output_' . $kontrak->id . '_' . time() . '.docx';
            $templateProcessor->saveAs($tempOutputPath);

            if (file_exists($tempOutputPath)) {
                if (file_exists($fullPath)) unlink($fullPath);
                rename($tempOutputPath, $fullPath);
            }

            if (file_exists($tempImagePath)) unlink($tempImagePath);
            $kontrak->update(['status_ttd' => 2]);
            return response()->json([
                'success' => true,
                'message' => 'Dokumen berhasil ditandatangani secara digital!'
            ]);
        } catch (\Exception $e) {
            Log::error("Sign Document Error: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Gagal tanda tangan: ' . $e->getMessage()], 500);
        }
    }

    public function putusKontrak(UpdateKontrak $request, Kontrak $kontrak)
    {
        $kontrak->update(['status_kontrak' => 0, 'tgl_keluar' => $request->tgl_keluar]);
        return response()->json(['success' => true]);
    }

    function hapusKontrak(Kontrak $kontrak)
    {
        User::where('email', $kontrak->email)->delete();
        $kontrak->delete();
        return response()->json(['success' => true]);
    }
}
