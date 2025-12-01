<?php

namespace App\Http\Controllers;

use App\Models\Penghuni;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;
use Yajra\DataTables\Facades\DataTables;

class PenghuniController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('penghuni.index'); // Pastikan Anda memiliki view 'penghuni.index'
    }

    public function ajax_DTPenghuni(Request $request)
    {
        $query = Penghuni::query()
            ->select(
                'id',
                'nik',
                'nik_hmac',
                'nama',
                'nama_hmac',
                'no_tlp',
                'no_tlp_hmac',
                'email',
                'email_hmac',
                'tgl_lahir',
                'jenis_kelamin',
                'status_kawin',
                'agama',
                'pekerjaan',
                'alamat',
                'tempat_lahir'
            )
            ->orderBy('id', 'desc');

        // filter 
        if ($request->has('search') && trim($request->search) !== '') {

            $keyword = trim($request->search);

            // HMAC keyword
            $keywordHmac = hmac($keyword);

            // Pencarian cocok dengan HMAC
            $query->where(function ($q) use ($keywordHmac) {
                $q->where('nik_hmac', $keywordHmac)
                    ->orWhere('no_tlp_hmac', $keywordHmac)
                    ->orWhere('email_hmac', $keywordHmac)
                    ->orWhere('nama_hmac', $keywordHmac);
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
        // --- LOGGING CSRF TOKEN ---
        Log::info('CSRF Token from Request (store): ' . $request->input('_token'));
        Log::info('CSRF Token from Session (store): ' . Session::token());
        // --- END LOGGING ---

        // Validasi input dari form
        $validator = Validator::make($request->all(), [
            'nik' => 'required|string|max:255',
            'nama' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'tgl_lahir' => 'required|date',
            'tempat_lahir' => 'required',
            'no_tlp' => 'required|numeric',
            'jenis_kelamin' => 'required|integer|in:1,2',
            'status_kawin' => 'required|integer|in:1,2,3,4',
            'agama' => 'required|integer|in:1,2,3,4,5,6,7',
            'pekerjaan' => 'required|string|max:255', // Validasi baru
            'alamat' => 'required|string|max:255',    // Validasi baru
        ], [
            'nik.required' => 'NIK harus diisi.',
            'nama.required' => 'Nama harus diisi.',
            'email.required' => 'Email harus diisi.',
            'email.email' => 'Format email tidak valid.',
            'tgl_lahir.required' => 'Tanggal lahir harus diisi.',
            'tempat_lahir.required' => 'Tempat lahir harus diisi.',
            'tgl_lahir.date' => 'Format tanggal lahir tidak valid.',
            'no_tlp.required' => 'Nomor telepon harus diisi.',
            'no_tlp.numeric' => 'Nomor telepon harus angka.',
            'jenis_kelamin.required' => 'Jenis kelamin harus dipilih.',
            'status_kawin.required' => 'Status kawin harus dipilih.',
            'agama.required' => 'Agama harus dipilih.',
            'pekerjaan.required' => 'Pekerjaan harus diisi.',
            'alamat.required' => 'Alamat harus diisi.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            DB::beginTransaction();

            $namaFormatted = strtoupper($request->nama);
            $emailFormatted = strtolower($request->email);

            $nikHmac = generateHmac($request->nik);
            $noTlpHmac = generateHmac($request->no_tlp);
            $emailHmac = generateHmac($emailFormatted);
            $namaHmac = generateHmac($namaFormatted);

            if (!$nikHmac || !$noTlpHmac || !$emailHmac || !$namaHmac) {
                Log::error('Gagal mendapatkan HMAC dari API untuk data penghuni.');
                throw new \Exception('Gagal mendapatkan HMAC dari API.');
            }

            // Pengecekan keunikan menggunakan HMAC
            $existingPenghuni = Penghuni::where('nik_hmac', $nikHmac)
                ->orWhere('no_tlp_hmac', $noTlpHmac)
                ->orWhere('email_hmac', $emailHmac)
                ->first();

            if ($existingPenghuni) {
                DB::rollback();
                $errorMessage = '';
                if (Penghuni::where('nik_hmac', $nikHmac)->exists()) {
                    $errorMessage .= 'NIK sudah terdaftar. ';
                }
                if (Penghuni::where('no_tlp_hmac', $noTlpHmac)->exists()) {
                    $errorMessage .= 'Nomor Telepon sudah terdaftar. ';
                }
                if (Penghuni::where('email_hmac', $emailHmac)->exists()) {
                    $errorMessage .= 'Email sudah terdaftar. ';
                }
                return response()->json(['success' => false, 'message' => $errorMessage], Response::HTTP_CONFLICT); // 409 Conflict
            }

            // 3. Simpan data ke database
            $penghuni = new Penghuni();
            $penghuni->nik = $request->nik; // Simpan NIK yang sudah dienkripsi
            $penghuni->nik_hmac = $nikHmac;
            $penghuni->nama = $namaFormatted;
            $penghuni->nama_hmac = $namaHmac; // Simpan HMAC nama
            $penghuni->email = $emailFormatted;
            $penghuni->email_hmac = $emailHmac;
            $penghuni->tgl_lahir = $request->tgl_lahir;
            $penghuni->tempat_lahir = $request->tempat_lahir; // Simpan tempat lahir
            $penghuni->no_tlp = $request->no_tlp;
            $penghuni->no_tlp_hmac = $noTlpHmac;
            $penghuni->jenis_kelamin = $request->jenis_kelamin;
            $penghuni->status_kawin = $request->status_kawin;
            $penghuni->agama = $request->agama;
            $penghuni->pekerjaan = $request->pekerjaan; // Simpan pekerjaan
            $penghuni->alamat = $request->alamat;     // Simpan alamat
            // created_by dan updated_by akan otomatis diisi oleh model boot method
            $penghuni->save();

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Data penghuni berhasil ditambahkan.']);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error saat menyimpan data penghuni: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan saat menyimpan data: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
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
    public function edit($id)
    {
        try {
            $penghuni = Penghuni::findOrFail($id);

            // Kumpulkan semua data terenkripsi yang perlu didekripsi
            $encryptedTexts = [
                $penghuni->nik,
                $penghuni->nama,
                $penghuni->no_tlp,
                $penghuni->email
            ];

            $decryptedTextsMap = unsealNames($encryptedTexts);

            // Siapkan data yang didekripsi untuk dikirim ke frontend
            $data = [
                'id' => $penghuni->id,
                'nik' => $decryptedTextsMap[$penghuni->nik] ?? 'Error Dekripsi',
                'nama' => $decryptedTextsMap[$penghuni->nama] ?? 'Error Dekripsi',
                'email' => $decryptedTextsMap[$penghuni->email] ?? 'Error Dekripsi',
                'tgl_lahir' => $penghuni->tgl_lahir,
                'tempat_lahir' => $penghuni->tempat_lahir,
                'no_tlp' => $decryptedTextsMap[$penghuni->no_tlp] ?? 'Error Dekripsi',
                'jenis_kelamin' => $penghuni->jenis_kelamin,
                'status_kawin' => $penghuni->status_kawin,
                'agama' => $penghuni->agama,
                'pekerjaan' => $penghuni->pekerjaan,
                'alamat' => $penghuni->alamat,
            ];

            return response()->json(['success' => true, 'data' => $data]);
        } catch (\Exception $e) {
            Log::error('Error fetching penghuni for edit: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['success' => false, 'message' => 'Gagal mengambil data penghuni: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Memperbarui data penghuni di database.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        // Validasi input dari form
        $validator = Validator::make($request->all(), [
            // NIK tidak divalidasi required karena disabled di frontend saat edit
            // 'nik' => 'required|string|max:255',
            'nama' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'tgl_lahir' => 'required|date',
            'tempat_lahir' => 'required',
            'no_tlp' => 'required|string|max:20',
            'jenis_kelamin' => 'required|integer|in:1,2',
            'status_kawin' => 'required|integer|in:1,2,3,4',
            'agama' => 'required|integer|in:1,2,3,4,5,6,7',
            'pekerjaan' => 'nullable|string|max:255',
            'alamat' => 'nullable|string|max:255',
        ], [
            // 'nik.required' => 'NIK harus diisi',
            'nama.required' => 'Nama harus diisi.',
            'email.required' => 'Email harus diisi.',
            'email.email' => 'Format email tidak valid.',
            'tgl_lahir.required' => 'Tanggal lahir harus diisi.',
            'tempat_lahir.required' => 'Tempat lahir harus diisi.',
            'tgl_lahir.date' => 'Format tanggal lahir tidak valid.',
            'no_tlp.required' => 'Nomor telepon harus diisi.',
            'jenis_kelamin.required' => 'Jenis kelamin harus dipilih.',
            'status_kawin.required' => 'Status kawin harus dipilih.',
            'agama.required' => 'Agama harus dipilih.',
            'pekerjaan.required' => 'Pekerjaan harus diisi.',
            'alamat.required' => 'Alamat harus diisi.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            DB::beginTransaction();

            $penghuni = Penghuni::findOrFail($id);

            // Konversi nama ke uppercase dan email ke lowercase
            $namaFormatted = strtoupper($request->nama);
            $emailFormatted = strtolower($request->email);

            // Hashing data unik yang mungkin berubah (No. Telp, Email)
            // NIK tidak di-hash ulang karena tidak diubah
            $namaHmac = generateHmac($request->nama);
            $noTlpHmac = generateHmac($request->no_tlp);
            $emailHmac = generateHmac($emailFormatted);

            if (!$noTlpHmac || !$emailHmac || !$namaHmac) {
                Log::error('Gagal mendapatkan HMAC dari API untuk data penghuni saat update.');
                throw new \Exception('Gagal mendapatkan HMAC dari API.');
            }

            // Pengecekan keunikan untuk No. Telp dan Email (kecuali data penghuni yang sedang diedit)
            $existingPenghuniCheck = Penghuni::where(function ($query) use ($noTlpHmac, $emailHmac) {
                $query->where('no_tlp_hmac', $noTlpHmac)
                    ->orWhere('email_hmac', $emailHmac);
            })
                ->where('id', '!=', $id)
                ->first();

            if ($existingPenghuniCheck) {
                DB::rollback();
                $errorMessage = '';
                if (Penghuni::where('no_tlp_hmac', $noTlpHmac)->where('id', '!=', $id)->exists()) {
                    $errorMessage .= 'Nomor Telepon sudah terdaftar pada penghuni lain. ';
                }
                if (Penghuni::where('email_hmac', $emailHmac)->where('id', '!=', $id)->exists()) {
                    $errorMessage .= 'Email sudah terdaftar pada penghuni lain. ';
                }
                return response()->json(['success' => false, 'message' => $errorMessage], Response::HTTP_CONFLICT);
            }

            // Update data di database
            // NIK tidak diubah
            $penghuni->nama = $namaFormatted;
            $penghuni->email = $emailFormatted;
            $penghuni->email_hmac = $emailHmac;
            $penghuni->tgl_lahir = $request->tgl_lahir;
            $penghuni->tempat_lahir = $request->tempat_lahir;
            $penghuni->no_tlp = $request->no_tlp;
            $penghuni->no_tlp_hmac = $noTlpHmac;
            $penghuni->jenis_kelamin = $request->jenis_kelamin;
            $penghuni->status_kawin = $request->status_kawin;
            $penghuni->agama = $request->agama;
            $penghuni->pekerjaan = $request->pekerjaan;
            $penghuni->alamat = $request->alamat;
            $penghuni->save();

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Data penghuni berhasil diperbarui.']);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error saat memperbarui data penghuni: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan saat memperbarui data: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Menghapus data penghuni dari database.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            $penghuni = Penghuni::findOrFail($id);
            $penghuni->delete(); // Menggunakan soft delete jika diaktifkan di model

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Data penghuni berhasil dihapus.']);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error deleting penghuni: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan saat menghapus data: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getDataIndividuFromAPI(Request $request)
    {
        // --- LOGGING CSRF TOKEN ---
        Log::info('CSRF Token from Request (getDataIndividuFromAPI): ' . $request->input('_token'));
        Log::info('CSRF Token from Session (getDataIndividuFromAPI): ' . Session::token());
        // --- END LOGGING ---

        $nik = $request->input('nik');

        if (empty($nik)) {
            return response()->json(['success' => false, 'message' => 'NIK tidak boleh kosong.'], Response::HTTP_BAD_REQUEST);
        }

        try {
            $mappedData = Penghuni::where('nik', $nik);

            if ($nik) {
                return response()->json(['success' => true, 'data' => $mappedData]);
            } else {
                return response()->json(['success' => false, 'message' => 'NIK tidak ditemukan atau terjadi kesalahan saat mengambil data.'], Response::HTTP_NOT_FOUND);
            }
        } catch (\Exception $e) {
            Log::error('Error in getDataIndividuFromAPI (Controller): ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan internal saat memproses permintaan: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
