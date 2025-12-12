<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\KontrakController;
use App\Http\Controllers\PendaftaranController;
use App\Http\Controllers\PengaturanController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PenghuniController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::post('/authenticate', [AuthController::class, 'authenticate']);
Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);

Route::post('/pendaftaran', [PendaftaranController::class, 'store']);
Route::middleware(['auth:sanctum', 'token.expiry', 'CekUser:Super Admin,Admin'])->group(function () {

    // Dashboard
    Route::get('/dashboard-data', [DashboardController::class, 'index']);

    // Pendaftar
    Route::get('/dataPendaftar', [PendaftaranController::class, 'index']);
    Route::put('/updateTanggalWawancara/{id}', [PendaftaranController::class, 'updateTanggalWawancara']);
    Route::put('/updateTanggalSelesai/{id}', [PendaftaranController::class, 'updateTanggalSelesai']);
    Route::delete('/deletePendaftar/{id}', [PendaftaranController::class, 'destroy']);

    // Penghuni
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

    // Kontrak Non Aktif
    Route::get('/dataKontrakNonAktif', [KontrakController::class, 'kontrakNonAktif']);
    Route::post('/dataKontrakNonAktif/{data}', [KontrakController::class, 'ajax_DTKontrakNonAktif']);
    Route::post('/storeKontrak', [KontrakController::class, 'store']);

    // Pengaturan Lokasi
    Route::get('/dataLokasiAll', [PengaturanController::class, 'allDataLokasi']);
    Route::post('/storeLokasi', [PengaturanController::class, 'storeLokasi']);
    Route::post('/dataLokasi/{request}', [PengaturanController::class, 'ajax_DTLokasi']);
    Route::put('/updateLokasi/{lokasi}', [PengaturanController::class, 'updateLokasi']);
    Route::delete('/deleteLokasi/{lokasi}', [PengaturanController::class, 'destroyLokasi']);
    Route::get('/getDataLokasi', [PengaturanController::class, 'getLokasiOptions']);

    // Pengaturan Gedung
    Route::get('/dataGedungAll', [PengaturanController::class, 'allDataGedung']);
    Route::post('/storeGedung', [PengaturanController::class, 'storeGedung']);
    Route::post('/dataGedung/{request}', [PengaturanController::class, 'ajax_DTGedung']);
    Route::put('/updateGedung/{gedung}', [PengaturanController::class, 'updateGedung']);
    Route::delete('/deleteGedung/{gedung}', [PengaturanController::class, 'destroyGedung']);

    // Pengaturan Unit
    Route::get('/dataUnit', [PengaturanController::class, 'ajax_DTUnit']);
    Route::post('/storeUnit', [PengaturanController::class, 'storeUnit']);
    Route::put('/updateUnit/{unit}', [PengaturanController::class, 'updateUnit']);
    Route::delete('/deleteUnit/{unit}', [PengaturanController::class, 'destroyUnit']);
});

Route::middleware(['auth:sanctum', 'token.expiry', 'CekUser:Super Admin'])->group(function () {
    Route::get('/dataUser', [UserController::class, 'index']);
    Route::post('/storeUser', [UserController::class, 'store']);
    Route::put('/updateUser/{user}', [UserController::class, 'update']);
    Route::delete('/deleteUser/{user}', [UserController::class, 'destroy']);
});
