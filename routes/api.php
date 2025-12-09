<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\KontrakController;
use App\Http\Controllers\PendaftaranController;
use App\Http\Controllers\PengaturanController;
use App\Http\Controllers\PenghuniController;
use Illuminate\Support\Facades\Route;

// login
Route::post('/authenticate', [AuthController::class, 'authenticate']);
Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);

// pendaftaran
Route::post('/pendaftaran', [PendaftaranController::class, 'store']);
Route::middleware(['auth:sanctum', 'token.expiry', 'CekUser:Super Admin,Admin'])->group(function () {

    // pendaftaran
    Route::get('/dataPendaftar', [PendaftaranController::class, 'index']);
    Route::put('/updateTanggalWawancara/{id}', [PendaftaranController::class, 'updateTanggalWawancara']);
    Route::delete('/deletePendaftar/{id}', [PendaftaranController::class, 'destroy']);

    // penghuni
    Route::get('/dataPenghuni', [PenghuniController::class, 'index']);
    Route::post('/dataPenghuni/{data}', [PenghuniController::class, 'ajax_DTPenghuni']);
    Route::post('/storePenghuni', [PenghuniController::class, 'store']);
    Route::put('/updatePenghuni/{id}', [PenghuniController::class, 'update']);
    Route::delete('/deletePenghuni/{id}', [PenghuniController::class, 'destroy']);

    // kontrak
    // aktif
    Route::get('/dataKontrak', [KontrakController::class, 'kontrakAktif']);
    Route::post('/dataKontrak/{data}', [KontrakController::class, 'ajax_DTKontrakAktif']);
    Route::delete('/deleteKontrak/{id}', [KontrakController::class, 'destroy']);
    Route::put('/updateKontrak/{id}', [KontrakController::class, 'update']);
    // non aktif
    Route::get('/dataKontrakNonAktif', [KontrakController::class, 'kontrakNonAktif']);
    Route::post('/dataKontrakNonAktif/{data}', [KontrakController::class, 'ajax_DTKontrakNonAktif']);
    Route::post('/storeKontrak', [KontrakController::class, 'store']);

    // pengaturan
    // lokasi
    Route::get('/dataLokasiAll', [PengaturanController::class, 'allDataLokasi']);
    Route::post('/storeLokasi', [PengaturanController::class, 'storeLokasi']);
    Route::post('/dataLokasi/{request}', [PengaturanController::class, 'ajax_DTLokasi']);
    Route::put('/updateLokasi/{lokasi}', [PengaturanController::class, 'updateLokasi']);
    Route::delete('/deleteLokasi/{lokasi}', [PengaturanController::class, 'destroyLokasi']);

    // gedung
    Route::get('/dataGedungAll', [PengaturanController::class, 'allDataGedung']);
    Route::post('/storeGedung', [PengaturanController::class, 'storeGedung']);
    Route::post('/dataGedung/{request}', [PengaturanController::class, 'ajax_DTGedung']);
    Route::put('/updateGedung/{gedung}', [PengaturanController::class, 'updateGedung']);
    Route::delete('/deleteGedung/{gedung}', [PengaturanController::class, 'destroyGedung']);
});
