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
Route::middleware(['auth:sanctum', 'token.expiry'])->group(function () {

    // pendaftaran
    Route::get('/dataPendaftar', [PendaftaranController::class, 'index']);
    Route::put('/updateTanggalWawancara/{id}', [PendaftaranController::class, 'updateTanggalWawancara']);
    Route::delete('/deletePendaftar/{id}', [PendaftaranController::class, 'destroy']);

    // penghuni
    Route::post('/dataPenghuni/{data}', [PenghuniController::class, 'ajax_DTPenghuni']);
    Route::post('/storePenghuni', [PenghuniController::class, 'store']);
    Route::put('/updatePenghuni/{id}', [PenghuniController::class, 'update']);
    Route::delete('/deletePenghuni/{id}', [PenghuniController::class, 'destroy']);

    // kontrak
    Route::post('/storeKontrak', [KontrakController::class, 'store']);

    // pengaturan
    // lokasi
    Route::post('/storeLokasi', [PengaturanController::class, 'storeLokasi']);
    Route::post('/dataLokasi/{request}', [PengaturanController::class, 'ajax_DTLokasi']);
    Route::put('/updateLokasi/{lokasi}', [PengaturanController::class, 'updateLokasi']);
    Route::delete('/deleteLokasi/{lokasi}', [PengaturanController::class, 'destroyLokasi']);

    // gedung
    Route::post('/storeGedung', [PengaturanController::class, 'storeGedung']);
    Route::post('/dataGedung/{request}', [PengaturanController::class, 'ajax_DTGedung']);
});
