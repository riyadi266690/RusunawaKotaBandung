<?php

namespace App\Http\Controllers;

use App\Models\Penghuni;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\Penghuni\StoreRequestPenghuni;
use App\Http\Requests\Penghuni\UpdateRequestPenghuni;
use App\Http\Resources\Penghuni\PenghuniResource;

class PenghuniController extends Controller
{
    public function allDataPenghuni()
    {
        $penghuni = Penghuni::all();

        if ($penghuni->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Data penghuni kosong.'
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Data penghuni berhasil diambil.',
            'data' => PenghuniResource::collection($penghuni)
        ]);
    }

    public function ajax_DTPenghuni(Request $request)
    {
        $query = Penghuni::query()->orderBy('id', 'desc');

        if ($request->has('search') && trim($request->search) !== '') {
            $keyword = trim($request->search);

            $query->where(function ($q) use ($keyword) {
                $q->where('nik', 'LIKE', "%{$keyword}%")
                    ->orWhere('nama', 'LIKE', "%{$keyword}%")
                    ->orWhere('email', 'LIKE', "%{$keyword}%")
                    ->orWhere('no_tlp', 'LIKE', "%{$keyword}%");
            });
        }

        $perPage = $request->get('per_page', 10);
        $data = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Data penghuni berhasil diambil.',
            'data' => $data
        ]);
    }

    public function store(StoreRequestPenghuni $request)
    {
        try {
            DB::transaction(function () use ($request) {

                $nama  = $request->nama;
                $email = $request->email;

                // --- HMAC ---
                $nikHmac   = generateHmac($request->nik);
                $noTlpHmac = generateHmac($request->no_tlp);
                $emailHmac = generateHmac($email);
                $namaHmac  = generateHmac($nama);

                if (!$nikHmac || !$noTlpHmac || !$emailHmac || !$namaHmac) {
                    throw new \Exception('Gagal mendapatkan HMAC.');
                }

                // --- CEK DUPLIKAT ---
                $exists = Penghuni::where('nik_hmac', $nikHmac)
                    ->orWhere('no_tlp_hmac', $noTlpHmac)
                    ->orWhere('email_hmac', $emailHmac)
                    ->exists();

                if ($exists) {
                    abort(409, 'NIK / Email / No Telp sudah terdaftar.');
                }

                // --- SIMPAN ---
                Penghuni::create([
                    'nik'          => $request->nik,
                    'nik_hmac'     => $nikHmac,
                    'nama'         => $nama,
                    'nama_hmac'    => $namaHmac,
                    'email'        => $email,
                    'email_hmac'   => $emailHmac,
                    'no_tlp'       => $request->no_tlp,
                    'no_tlp_hmac'  => $noTlpHmac,
                    'tgl_lahir'    => $request->tgl_lahir,
                    'tempat_lahir' => $request->tempat_lahir,
                    'jenis_kelamin' => $request->jenis_kelamin,
                    'status_kawin' => $request->status_kawin,
                    'agama'        => $request->agama,
                    'pekerjaan'    => $request->pekerjaan,
                    'alamat'       => $request->alamat,
                ]);
            });

            return response()->json([
                'success' => true,
                'message' => 'Data penghuni berhasil ditambahkan.'
            ]);
        } catch (\Throwable $e) {
            Log::error('Store Penghuni Error', [
                'message' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }


    public function update(UpdateRequestPenghuni $request, $id)
    {
        $penghuni = Penghuni::find($id);
        if (!$penghuni) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan'
            ], 404);
        }

        try {
            DB::transaction(function () use ($request, $penghuni, $id) {

                $nama  = $request->nama;
                $email = $request->email;

                // --- HMAC ---
                $noTlpHmac = generateHmac($request->no_tlp);
                $emailHmac = generateHmac($email);

                if (!$noTlpHmac || !$emailHmac) {
                    throw new \Exception('Gagal mendapatkan HMAC.');
                }

                // --- CEK DUPLIKAT ---
                $exists = Penghuni::where(function ($q) use ($noTlpHmac, $emailHmac) {
                    $q->where('no_tlp_hmac', $noTlpHmac)
                        ->orWhere('email_hmac', $emailHmac);
                })
                    ->where('id', '!=', $id)
                    ->exists();

                if ($exists) {
                    abort(409, 'Email atau Nomor Telepon sudah digunakan penghuni lain.');
                }

                // --- UPDATE ---
                $penghuni->update([
                    'nama'          => $nama,
                    'email'         => $email,
                    'email_hmac'    => $emailHmac,
                    'no_tlp'        => $request->no_tlp,
                    'no_tlp_hmac'   => $noTlpHmac,
                    'tgl_lahir'     => $request->tgl_lahir,
                    'tempat_lahir'  => $request->tempat_lahir,
                    'jenis_kelamin' => $request->jenis_kelamin,
                    'status_kawin'  => $request->status_kawin,
                    'agama'         => $request->agama,
                    'pekerjaan'     => $request->pekerjaan,
                    'alamat'        => $request->alamat,
                ]);
            });

            return response()->json([
                'success' => true,
                'message' => 'Data berhasil diperbarui.'
            ]);
        } catch (\Throwable $e) {
            Log::error('Update Penghuni Error', [
                'message' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            Penghuni::findOrFail($id)->delete();
            return response()->json(['success' => true, 'message' => 'Data dihapus.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
