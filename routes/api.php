<?php

use App\Http\Controllers\Api\BssnApiController;
use App\Http\Controllers\Api\PenghuniApiController;
use Illuminate\Support\Facades\Route;

use App\Http\Middleware\SimpleBasicAuth;

Route::middleware(SimpleBasicAuth::class)->group(function () {
    Route::get('/penghuni', [PenghuniApiController::class, 'index']);
});

// BSSN Cryptographic endpoints (Public)
Route::prefix('bssn')->group(function () {
    Route::post('/seal', [BssnApiController::class, 'seal']);
    Route::post('/unseal', [BssnApiController::class, 'unseal']);
    Route::post('/seal-names', [BssnApiController::class, 'sealNames']);
    Route::post('/unseal-names', [BssnApiController::class, 'unsealNames']);
    Route::post('/hmac', [BssnApiController::class, 'hmac']);
    Route::post('/verify-hmac', [BssnApiController::class, 'verifyHmac']);
});
