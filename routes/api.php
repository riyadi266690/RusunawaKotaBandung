<?php

use App\Http\Controllers\Api\PenghuniApiController;
use Illuminate\Support\Facades\Route;

use App\Http\Middleware\SimpleBasicAuth;

Route::middleware(SimpleBasicAuth::class)->group(function () {
    Route::get('/penghuni', [PenghuniApiController::class, 'index']);
});
