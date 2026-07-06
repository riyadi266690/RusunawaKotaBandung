<?php

use App\Http\Controllers\Api\PenghuniApiController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth.basic')->group(function () {
    Route::get('/penghuni', [PenghuniApiController::class, 'index']);
});
