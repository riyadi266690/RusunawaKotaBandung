<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\PendaftaranController;
use App\Http\Controllers\PengaturanController;
use App\Http\Controllers\PenghuniController;
use App\Models\Pendaftaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// login
Route::post('/authenticate', [AuthController::class, 'authenticate']);
Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);

// pendaftaran
Route::post('/pendaftaran', [PendaftaranController::class, 'store']);
Route::middleware(['auth:sanctum', 'token.expiry'])->group(function () {

    // pendaftaran
    Route::put('/updateTanggalWawancara/{id}', [PendaftaranController::class, 'updateTanggalWawancara']);

    // penghuni
    Route::post('/dataPenghuni/{data}', [PenghuniController::class, 'ajax_DTPenghuni']);
    Route::post('/storePenghuni', [PenghuniController::class, 'store']);
    Route::put('/updatePenghuni/{id}', [PenghuniController::class, 'update']);
    Route::delete('/deletePenghuni/{id}', [PenghuniController::class, 'destroy']);
});
