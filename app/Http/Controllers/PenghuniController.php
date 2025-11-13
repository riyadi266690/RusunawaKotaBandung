<?php

namespace App\Http\Controllers;

use App\Models\Penghuni;
use Carbon\Carbon;
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
        // Berikan query builder langsung ke Datatables
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

        // Gunakan Yajra DataTables dengan query builder
        return DataTables::of($query)
            ->addColumn('id', function($w){
                return $w->id;
            })
            // Gunakan `editColumn` untuk menampilkan nilai yang sudah didekripsi oleh accessor.
            // Tidak perlu lagi memanggil fungsi dekripsi manual.
            ->editColumn('nik', function($w){
                // Mengakses atribut nik yang sudah didekripsi oleh accessor
                return $w->nik;
            })
            ->editColumn('nama', function($w){
                // Mengakses atribut nama yang sudah didekripsi oleh accessor
                return $w->nama;
            })
            ->editColumn('email', function($w){
                // Mengakses atribut email yang sudah didekripsi oleh accessor
                return $w->email;
            })
            ->editColumn('tgl_lahir', function($w){
                return Carbon::parse($w->tgl_lahir)->format('d-m-Y');
            })
            ->editColumn('no_tlp', function($w){
                // Mengakses atribut no_tlp yang sudah didekripsi oleh accessor
                return $w->no_tlp;
            })
            ->editColumn('jenis_kelamin', function($w){
                return $w->jenis_kelamin == 1 ? 'Laki-laki' : 'Perempuan';
            })
            ->editColumn('status_kawin', function($w){
                $status = [
                    1 => 'Belum Kawin',
                    2 => 'Kawin/Nikah',
                    3 => 'Cerai Hidup',
                    4 => 'Cerai Mati'
                ];
                return $status[$w->status_kawin] ?? 'Tidak Diketahui';
            })
            ->editColumn('agama', function($w){
                $agama = [
                    1 => 'Islam',
                    2 => 'Kristen',
                    3 => 'Katolik',
                    4 => 'Hindu',
                    5 => 'Buddha',
                    6 => 'Konghucu',
                    7 => 'Penghayat Kepercayaan'
                ];
                return $agama[$w->agama] ?? 'Tidak Diketahui';
            })
            ->addColumn('aksi', function($w){
                return '<div class="btn-group">
                            <button type="button" class="btn btn-primary btn-xs dropdown-toggle" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Aksi</button>
                            <div class="dropdown-menu">                                                          

                                <a class="dropdown-item" href="#" onclick="hapusPenghuni(' . $w->id . ')">Hapus</a>                        
                            </div>
                        </div>';
            })
            // Tambahkan logika filter
            ->filter(function ($query) use ($request) {
                if ($keyword = $request->get('search')['value']) {
                    // Panggil helper hmac() untuk mendapatkan nilai HMAC dari kata kunci pencarian.
                    // Pastikan helper ini ada dan berfungsi.
                    $hmac = hmac($keyword);

                    // Lakukan pencarian langsung pada kolom HMAC di database
                    $query->where(function($q) use ($hmac) {
                        $q->where('nik_hmac', $hmac)
                          ->orWhere('no_tlp_hmac', $hmac)
                          ->orWhere('email_hmac', $hmac)
                          ->orwhere('nama_hmac', $hmac); // Jika Anda menambahkan kolom nama_hmac di database
                    });
                }
            })
            ->rawColumns(['aksi'])
            ->toJson();
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

        // --- HANYA UNTUK TESTING ---
        // Panggil fungsi hmac() dan langsung dd() hasilnya
        //$nikHmac = hmac($request->nik);
        //dd($request->all(),$nikHmac);
        // --- AKHIR DARI TESTING ---

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
            'pekerjaan' => 'nullable|string|max:255', // Validasi baru
            'alamat' => 'nullable|string|max:255',    // Validasi baru
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
            'pekerjaan.required' => 'Pekerjaan harus diisi.', // Pesan validasi
            'alamat.required' => 'Alamat harus diisi.',       // Pesan validasi
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
            // 1. Hashing data unik (NIK, No. Telp, Email)
            $nikHmac = hmac($request->nik);
            $noTlpHmac = hmac($request->no_tlp);
            $emailHmac = hmac($emailFormatted);
            $namaHmac = hmac($namaFormatted); // HMAC untuk nama juga

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

            // 2. Enkripsi data sensitif (NIK, Nama, No. Telp, Email)
            $plainTextsToSeal = [
                $request->nik, // Tambahkan NIK
                $namaFormatted,
                $request->no_tlp,
                $emailFormatted
            ];
            
            // Log data plaintext sebelum seal
            Log::info('Plain texts sent for seal:', $plainTextsToSeal);

            $sealedTexts = sealNames($plainTextsToSeal);

            // Log data terenkripsi setelah seal
            Log::info('Sealed texts received:', $sealedTexts);

            // Pastikan semua respons enkripsi valid
            if (empty($sealedTexts) || count($sealedTexts) < 4) { // Ubah menjadi 4 karena NIK ditambahkan
                Log::error('Gagal mendapatkan respons enkripsi yang valid dari API untuk data penghuni.');
                throw new \Exception('Gagal mendapatkan respons enkripsi yang valid dari API.');
            }

            $encryptedNik = $sealedTexts[$request->nik]; // Dapatkan NIK terenkripsi
            $encryptedNama = $sealedTexts[$namaFormatted];
            $encryptedNoTlp = $sealedTexts[$request->no_tlp];
            $encryptedEmail = $sealedTexts[$emailFormatted];

            // 3. Simpan data ke database
            $penghuni = new Penghuni();
            $penghuni->nik = $encryptedNik; // Simpan NIK yang sudah dienkripsi
            $penghuni->nik_hmac = $nikHmac;
            $penghuni->nama = $encryptedNama;
            $penghuni->nama_hmac = $namaHmac; // Simpan HMAC nama
            $penghuni->email = $encryptedEmail;
            $penghuni->email_hmac = $emailHmac;
            $penghuni->tgl_lahir = $request->tgl_lahir;
            $penghuni->tempat_lahir = $request->tempat_lahir; // Simpan tempat lahir
            $penghuni->no_tlp = $encryptedNoTlp;
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
            $noTlpHmac = hmac($request->no_tlp);
            $emailHmac = hmac($emailFormatted);

            if (!$noTlpHmac || !$emailHmac) {
                Log::error('Gagal mendapatkan HMAC dari API untuk data penghuni saat update.');
                throw new \Exception('Gagal mendapatkan HMAC dari API.');
            }

            // Pengecekan keunikan untuk No. Telp dan Email (kecuali data penghuni yang sedang diedit)
            $existingPenghuniCheck = Penghuni::where(function($query) use ($noTlpHmac, $emailHmac) {
                                            $query->where('no_tlp_hmac', $noTlpHmac)
                                                  ->orWhere('email_hmac', $emailHmac);
                                        })
                                        ->where('id', '!=', $id) // Kecualikan data yang sedang diedit
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

            // Enkripsi data sensitif yang mungkin berubah (Nama, No. Telp, Email)
            $plainTextsToSeal = [
                $namaFormatted,
                $request->no_tlp,
                $emailFormatted
            ];
            
            $sealedTexts = sealNames($plainTextsToSeal);

            if (empty($sealedTexts) || count($sealedTexts) < 3) { // Hanya 3 karena NIK tidak di-seal ulang
                Log::error('Gagal mendapatkan respons enkripsi yang valid dari API untuk data penghuni saat update.');
                throw new \Exception('Gagal mendapatkan respons enkripsi yang valid dari API.');
            }

            $encryptedNama = $sealedTexts[$namaFormatted];
            $encryptedNoTlp = $sealedTexts[$request->no_tlp];
            $encryptedEmail = $sealedTexts[$emailFormatted];

            // Update data di database
            // NIK tidak diubah
            $penghuni->nama = $encryptedNama;
            $penghuni->email = $encryptedEmail;
            $penghuni->email_hmac = $emailHmac;
            $penghuni->tgl_lahir = $request->tgl_lahir;
            $penghuni->tempat_lahir = $request->tempat_lahir; // Simpan tempat lahir
            $penghuni->no_tlp = $encryptedNoTlp;
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
            // Panggil fungsi getDataIndividu dari helper
            $mappedData = getDataIndividu($nik);

            if ($mappedData) {
                return response()->json(['success' => true, 'data' => $mappedData]);
            } else {
                // Pesan error dari helper sudah cukup informatif
                return response()->json(['success' => false, 'message' => 'NIK tidak ditemukan atau terjadi kesalahan saat mengambil data.'], Response::HTTP_NOT_FOUND);
            }
        } catch (\Exception $e) {
            Log::error('Error in getDataIndividuFromAPI (Controller): ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan internal saat memproses permintaan: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
