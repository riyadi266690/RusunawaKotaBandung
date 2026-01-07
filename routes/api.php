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
    Route::get('/dataPendaftar', [PendaftaranController::class, 'allDataPendaftaran']);
    Route::put('/updateTanggalWawancara/{id}', [PendaftaranController::class, 'updateTanggalWawancara']);
    Route::put('/updateTanggalSelesai/{id}', [PendaftaranController::class, 'updateTanggalSelesai']);
    Route::delete('/deletePendaftar/{id}', [PendaftaranController::class, 'destroy']);

    // Penghuni
    Route::get('/dataPenghuniAll', [PenghuniController::class, 'allDataPenghuni']);
    Route::get('/dataPenghuni', [PenghuniController::class, 'ajax_DTPenghuni']);
    Route::post('/storePenghuni', [PenghuniController::class, 'store']);
    Route::put('/updatePenghuni/{id}', [PenghuniController::class, 'update']);
    Route::delete('/deletePenghuni/{id}', [PenghuniController::class, 'destroy']);

    // Kontrak Aktif
    Route::get('/dataKontrak', [KontrakController::class, 'kontrakAktif']);
    Route::get('/dataUnit', [KontrakController::class, 'getUnitOptions']);
    Route::get('/getUnitDetails/{id}', [KontrakController::class, 'getUnitDetails']);
    Route::get('/getPenghuniOption', [KontrakController::class, 'getPenghuniOptions']);
    Route::post('/storeKontrak', [KontrakController::class, 'store']);
    Route::put('/putusKontrak/{kontrak}', [KontrakController::class, 'putusKontrak']);
    Route::delete('/hapusKontrak/{kontrak}', [KontrakController::class, 'hapusKontrak']);

    Route::post('/kontrak/upload-revisi/{id}', [KontrakController::class, 'uploadRevisi']);
    Route::post('/kontrak/sign/{id}', [KontrakController::class, 'signDocument']);

    // Kontrak Non Aktif
    Route::get('/dataKontrakNonAktif', [KontrakController::class, 'kontrakNonAktif']);
    Route::post('/dataKontrakNonAktif/{data}', [KontrakController::class, 'ajax_DTKontrakNonAktif']);
    Route::post('/storeKontrak', [KontrakController::class, 'store']);

    // Pengaturan Lokasi
    Route::get('/dataLokasiAll', [LokasiController::class, 'allDataLokasi']);
    Route::post('/storeLokasi', [LokasiController::class, 'storeLokasi']);
    Route::post('/dataLokasi/{request}', [LokasiController::class, 'ajax_DTLokasi']);
    Route::put('/updateLokasi/{lokasi}', [LokasiController::class, 'updateLokasi']);
    Route::delete('/deleteLokasi/{lokasi}', [LokasiController::class, 'destroyLokasi']);
    Route::get('/getDataLokasi', [LokasiController::class, 'getLokasiOptions']);

    // Pengaturan Gedung
    Route::get('/dataGedungAll', [GedungController::class, 'allDataGedung']);
    Route::post('/storeGedung', [GedungController::class, 'storeGedung']);
    Route::post('/dataGedung/{request}', [GedungController::class, 'ajax_DTGedung']);
    Route::put('/updateGedung/{gedung}', [GedungController::class, 'updateGedung']);
    Route::delete('/deleteGedung/{gedung}', [GedungController::class, 'destroyGedung']);

    // Pengaturan Unit
    Route::get('/dataUnitAll', [UnitController::class, 'allDataUnit']);
    Route::get('/dataUnit', [UnitController::class, 'ajax_DTUnit']);
    Route::post('/storeUnit', [UnitController::class, 'storeUnit']);
    Route::put('/updateUnit/{unit}', [UnitController::class, 'updateUnit']);
    Route::delete('/deleteUnit/{unit}', [UnitController::class, 'destroyUnit']);

    Route::put('/laporan/{id}', [LaporanController::class, 'update']);
    Route::get('/pegawai', [PegawaiController::class, 'index']);

    // Pegawai
    Route::get('/dataPegawaiAll', [PegawaiController::class, 'allDataPegawai']);
    Route::post('/storePegawai', [PegawaiController::class, 'storePegawai']);
    Route::put('/updatePegawai/{pegawai}', [PegawaiController::class, 'editPegawai']);
    Route::delete('/deletePegawai/{pegawai}', [PegawaiController::class, 'destroyPegawai']);
});

Route::middleware(['auth:sanctum', 'token.expiry', 'CekUser:Super Admin'])->group(function () {
    Route::get('/dataUser', [UserController::class, 'index']);
    Route::post('/storeUser', [UserController::class, 'store']);
    Route::put('/updateUser/{user}', [UserController::class, 'update']);
    Route::delete('/deleteUser/{user}', [UserController::class, 'destroy']);
});

Route::middleware(['auth:sanctum', 'token.expiry', 'CekUser:Super Admin,Admin,Penghuni'])->group(function () {
    Route::get('/laporan', [LaporanController::class, 'index']);
    Route::post('/laporan', [LaporanController::class, 'store']);
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
});
