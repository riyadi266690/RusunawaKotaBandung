<?php

namespace App\Http\Controllers;

use App\Http\Requests\Pendaftar\StorePendaftar;
use App\Http\Requests\Pendaftar\UpdatePendaftar;
use App\Http\Requests\Pendaftar\UpdateTanggalSelesaiPendaftaran;
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

            $data = new Pendaftaran();
            $data->nama = $request->nama;
            $data->telp_pendaftar = $request->telp_pendaftar;
            $data->telp_pendaftar_hash = $phoneHmac;
            $data->suket = $filePath;
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
