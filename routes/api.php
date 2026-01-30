<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\KontrakController;
use App\Http\Controllers\PendaftaranController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DokumenController;
use App\Http\Controllers\GedungController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\LokasiController;
use App\Http\Controllers\PegawaiController;
use App\Http\Controllers\PenghuniController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\UserController;
use App\Models\Pegawai;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

Route::post('/authenticate', [AuthController::class, 'authenticate']);
Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);

Route::post('/pendaftaran', [PendaftaranController::class, 'store']);
Route::middleware(['auth:sanctum', 'token.expiry', 'CekUser:Super Admin,Admin'])->group(function () {

    // Dashboard
    Route::get('/dashboard-data', [DashboardController::class, 'index']);

    // Pendaftar
    Route::get('/pendaftaran', [PendaftaranController::class, 'allDataPendaftaran']);
    Route::put('/pendaftaran/tanggalWawancara/{id}', [PendaftaranController::class, 'updateTanggalWawancara']);
    Route::put('/pendaftaran/tanggalSelesai/{id}', [PendaftaranController::class, 'updateTanggalSelesai']);
    Route::delete('/pendaftaran/{id}', [PendaftaranController::class, 'destroy']);

    // Penghuni
    Route::get('/penghuni', [PenghuniController::class, 'allDataPenghuni']);
    Route::post('/penghuni', [PenghuniController::class, 'store']);
    Route::put('/penghuni/{id}', [PenghuniController::class, 'update']);
    Route::delete('/penghuni/{id}', [PenghuniController::class, 'destroy']);

    // Kontrak Aktif
    Route::get('/kontrak/aktif', [KontrakController::class, 'kontrakAktif']);

    Route::get('/dataUnit', [KontrakController::class, 'getUnitOptions']);
    Route::get('/unitDetails/{id}', [KontrakController::class, 'getUnitDetails']);
    Route::get('/penghuniOptions', [KontrakController::class, 'getPenghuniOptions']);

    Route::post('/kontrak', [KontrakController::class, 'store']);
    Route::put('/kontrak/{kontrak}', [KontrakController::class, 'putusKontrak']);
    Route::delete('/kontrak/{kontrak}', [KontrakController::class, 'hapusKontrak']);

    Route::post('/kontrak/uploadRevisi/{id}', [KontrakController::class, 'uploadRevisi']);
    Route::post('/kontrak/sign/{id}', [KontrakController::class, 'signDocument']);

    // Kontrak Non Aktif
    Route::get('/kontrak/nonAktif', [KontrakController::class, 'kontrakNonAktif']);

    // Pengaturan Lokasi
    Route::get('/lokasi', [LokasiController::class, 'allDataLokasi']);
    Route::post('/lokasi', [LokasiController::class, 'storeLokasi']);
    Route::put('/lokasi/{lokasi}', [LokasiController::class, 'updateLokasi']);
    Route::delete('/lokasi/{lokasi}', [LokasiController::class, 'destroyLokasi']);

    // Pengaturan Gedung
    Route::get('/gedung', [GedungController::class, 'allDataGedung']);
    Route::post('/gedung', [GedungController::class, 'storeGedung']);
    Route::put('/gedung/{gedung}', [GedungController::class, 'updateGedung']);
    Route::delete('/gedung/{gedung}', [GedungController::class, 'destroyGedung']);

    // Pengaturan Unit
    Route::get('/unit', [UnitController::class, 'ajax_DTUnit']);
    Route::post('/unit', [UnitController::class, 'storeUnit']);
    Route::put('/unit/{unit}', [UnitController::class, 'updateUnit']);
    Route::delete('/unit/{unit}', [UnitController::class, 'destroyUnit']);

    Route::put('/laporan/{id}', [LaporanController::class, 'update']);
    Route::get('/pegawai', [PegawaiController::class, 'index']);

    // Pegawai
    Route::get('/pegawai', [PegawaiController::class, 'allDataPegawai']);
    Route::post('/pegawai', [PegawaiController::class, 'storePegawai']);
    Route::put('/pegawai/{pegawai}', [PegawaiController::class, 'editPegawai']);
    Route::delete('/pegawai/{pegawai}', [PegawaiController::class, 'destroyPegawai']);
});

Route::middleware(['auth:sanctum', 'token.expiry', 'CekUser:Super Admin'])->group(function () {
    Route::get('/user', [UserController::class, 'index']);
    Route::post('/user', [UserController::class, 'store']);
    Route::put('/user/{user}', [UserController::class, 'update']);
    Route::delete('/user/{user}', [UserController::class, 'destroy']);
});

Route::middleware(['auth:sanctum', 'token.expiry', 'CekUser:Super Admin,Admin,Penghuni'])->group(function () {
    Route::get('/laporan', [LaporanController::class, 'index']);
    Route::post('/laporan', [LaporanController::class, 'store']);
    Route::get('/selfUser', function (Request $request) {
        return $request->user();
    });
});
