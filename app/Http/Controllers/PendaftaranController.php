<?php

namespace App\Http\Controllers;

use App\Models\Pendaftaran;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;
use Yajra\DataTables\Facades\DataTables;

class PendaftaranController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('pendaftaran.index');
    }
    public function index_pengelola()
    {
        return view('pendaftaran.index_pengelola');
    }
    public function ajax_DTpendaftar(Request $request)
    {
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

        // Kumpulkan semua nama dan telp terenkripsi
        $encryptedTexts = $pendaftars->pluck('nama')->merge($pendaftars->pluck('telp_pendaftar'))->toArray();
        $decryptedTextsMap = unsealNames($encryptedTexts);

        $pendaftars->transform(function ($item) use ($decryptedTextsMap) {
            $item->nama = $decryptedTextsMap[$item->nama] ?? 'Invalid response';
            $item->telp_pendaftar = $decryptedTextsMap[$item->telp_pendaftar] ?? 'Invalid response';
            return $item;
        });

        return response()->json([
            'sukses' => true,
            'data' => $pendaftars
        ]);
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
        // Validasi data awal tanpa memeriksa unik
        $validator = Validator::make($request->all(), [
            'nama' => 'required|string|max:255',
            'telp_pendaftar' => 'required|numeric',
            'suket' => 'required|file|mimes:pdf|max:2048',
        ], [
            'nama.required' => 'Nama lengkap harus diisi.',
            'telp_pendaftar.required' => 'No Telp / WhatsApp harus diisi.',
            'telp_pendaftar.numeric' => 'No Telp / WhatsApp harus berupa angka.',
            'suket.required' => 'Unggah Formulir Pendaftaran harus diisi.',
            'suket.mimes' => 'File harus berupa PDF.',
            'suket.max' => 'Ukuran file tidak boleh lebih dari 2MB.',
        ]);

        if ($validator->fails()) {
            return $request->expectsJson()
                ? response()->json(['errors' => $validator->errors()], 422)
                : back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            // Panggil helper untuk membuat HMAC dari nomor telepon untuk pengecekan unik
            $phoneHmac = generateHmac($request->telp_pendaftar);

            if (!$phoneHmac) {
                throw new Exception('Gagal mendapatkan HMAC dari API.');
            }

            // Pengecekan unik secara manual menggunakan HMAC
            $existingPendaftar = Pendaftaran::where('telp_pendaftar_hash', $phoneHmac)->first();
            if ($existingPendaftar) {
                DB::rollback();
                // return back()->with('gagal', 'No Telp / WhatsApp sudah terdaftar.')->withInput();
                return response()->json([
                    'gagal' => 'No Telp / WhatsApp sudah terdaftar.'
                ]);
            }

            // Simpan file yang diunggah
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
    public function updateTanggalWawancara(Request $request, $id)
    {
        // Validasi data yang masuk dari AJAX
        $validator = Validator::make($request->all(), [
            'tgl_wawancara' => 'required|date',
            'tgl_final' => 'nullable|date',
            'ket_wawancara' => 'nullable|string',
        ], [
            'tgl_wawancara.required' => 'Tanggal wawancara harus diisi.',
            'tgl_wawancara.date' => 'Format tanggal tidak valid.',
            'tgl_final.nullable' => 'Tanggal selesai harus diisi.',
            'tgl_final.date' => 'Format selesai tidak valid.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

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

    public function updateTanggalSelesai(Request $request, $id)
    {
        // Ambil data pendaftar untuk mendapatkan tgl_wawancara
        $pendaftar = Pendaftaran::findOrFail($id);
        $tglWawancara = $pendaftar->tgl_wawancara;

        // Validasi data yang masuk dari AJAX
        $validator = Validator::make($request->all(), [
            'tgl_final' => 'required|date|after_or_equal:' . $tglWawancara, // Aturan BARU
            'ket_wawancara' => 'nullable|string',
        ], [
            'tgl_final.required' => 'Tanggal selesai harus diisi.',
            'tgl_final.date' => 'Format tanggal tidak valid.',
            'tgl_final.after_or_equal' => 'Tanggal selesai tidak boleh lebih awal dari tanggal wawancara.', // Pesan BARU
            'ket_wawancara.string' => 'Catatan wawancara harus berupa teks.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            // Mulai transaksi database
            DB::beginTransaction();

            $pendaftar = Pendaftaran::findOrFail($id);
            $pendaftar->tgl_final = $request->tgl_final;
            $pendaftar->ket_wawancara = $request->ket_wawancara; // Simpan catatan
            $pendaftar->status_daftar = 3;
            $pendaftar->save();

            // Jika semua operasi berhasil, commit transaksi
            DB::commit();

            return response()->json(['success' => true, 'message' => 'Tanggal dan catatan selesai berhasil diperbarui.']);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error update selesai: ' . $e->getMessage());

            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan saat memperbarui data: ' . $e->getMessage()]);
        }
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
