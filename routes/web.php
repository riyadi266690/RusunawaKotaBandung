<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KontrakController;
use App\Http\Controllers\PendaftaranController;
use App\Http\Controllers\PengaturanController;
use App\Http\Controllers\PenghuniController;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;

$prefix = env('APP_ROUTE_PREFIX', '');

Route::prefix($prefix)->group(function () {

    Route::get('/', [PendaftaranController::class, 'index']);
    Route::get('/tokensadarka', function () {
        return token_sadarkajabar();
    });
    Route::get('login', [AuthController::class, 'login'])->name('login');
    Route::prefix('pendaftaran')->name('pendaftaran.')->group(function() {
        Route::get('index', [PendaftaranController::class, 'index'])->name('index');
        Route::post('store', [PendaftaranController::class, 'store'])->name('store');    
    });
    Route::prefix('auth')->name('auth.')->group(function() {
        Route::get('login', [AuthController::class, 'login'])->name('login');
        Route::post('login', [AuthController::class, 'authenticate'])->name('authenticate');
        Route::post('logout', [AuthController::class, 'logout'])->name('logout');
        Route::get('password/form', [AuthController::class, 'passwordForm'])->name('password.form');
        Route::post('password/update', [AuthController::class, 'updatePassword'])->name('password.update');
    });
    Route::prefix('dashboard')->name('dashboard.')->middleware(['auth', 'CekUser', 'BackHistory'])->group(function() {
        Route::get('index', [DashboardController::class ,'index'])->name('index');
    });
    Route::prefix('pendaftar')->name('pendaftar.')->middleware(['auth', 'CekUser', 'BackHistory'])->group(function() {
        Route::get('index', [PendaftaranController::class, 'index_pengelola'])->name('index');    
        Route::post('/update-tgl-wawancara/{id}', [PendaftaranController::class, 'updateTanggalWawancara'])->name('updateWawancara'); 
        Route::post('/update-tgl-final/{id}', [PendaftaranController::class, 'updateTanggalSelesai'])->name('updateSelesai');   
        Route::get('ajax.pendaftar', [PendaftaranController::class, 'ajax_DTpendaftar'])->name('ajax.DTPendaftar'); 
    });
    Route::prefix('penghuni')->name('penghuni.')->middleware(['auth', 'CekUser', 'BackHistory'])->group(function() {
        Route::get('index', [PenghuniController::class, 'index'])->name('index');    
        Route::post('store', [PenghuniController::class, 'store'])->name('store');    
        Route::get('/{id}/edit', [PenghuniController::class, 'edit'])->name('edit');
        Route::put('/{id}', [PenghuniController::class, 'update'])->name('update');
        Route::delete('/{id}', [PenghuniController::class, 'destroy'])->name('destroy');
        Route::get('ajax.penghuni', [PenghuniController::class, 'ajax_DTPenghuni'])->name('ajax.DTPenghuni');
        // Rute BARU untuk memanggil API eksternal
        Route::post('get-data-individu', [PenghuniController::class, 'getDataIndividuFromAPI'])->name('getDataIndividuFromAPI');
    });

    // Route untuk Pengaturan (Lokasi dan Gedung)
    Route::prefix('pengaturan')->name('pengaturan.')->group(function () {
        Route::get('/', [PengaturanController::class, 'index'])->name('index');
        Route::get('/ajax-dtlokasi', [PengaturanController::class, 'ajax_DTLokasi'])->name('ajax.DTLokasi');
        Route::get('/ajax-dtgedung', [PengaturanController::class, 'ajax_DTGedung'])->name('ajax.DTGedung');
        Route::get('/ajax-dtunit', [PengaturanController::class, 'ajax_DTUnit'])->name('ajax.DTUnit');

        // Routes untuk Lokasi
        Route::prefix('lokasi')->name('lokasi.')->group(function () {
            Route::post('/store', [PengaturanController::class, 'storeLokasi'])->name('store');
            Route::get('/{lokasi}/edit', [PengaturanController::class, 'editLokasi'])->name('edit');
            Route::put('/{lokasi}', [PengaturanController::class, 'updateLokasi'])->name('update');
            Route::delete('/{lokasi}', [PengaturanController::class, 'destroyLokasi'])->name('destroy');
            Route::get('/options', [PengaturanController::class, 'getLokasiOptions'])->name('options'); // Untuk dropdown Gedung
        });

        // Routes untuk Gedung
        Route::prefix('gedung')->name('gedung.')->group(function () {
            Route::post('/store', [PengaturanController::class, 'storeGedung'])->name('store');
            Route::get('/{gedung}/edit', [PengaturanController::class, 'editGedung'])->name('edit');
            Route::put('/{gedung}', [PengaturanController::class, 'updateGedung'])->name('update');
            Route::delete('/{gedung}', [PengaturanController::class, 'destroyGedung'])->name('destroy');
            Route::get('/options', [PengaturanController::class, 'getGedungOptions'])->name('options'); // Untuk dropdown Unit
        });
        // Routes untuk Unit
        Route::prefix('unit')->name('unit.')->group(function () {
            Route::post('/store', [PengaturanController::class, 'storeUnit'])->name('store');
            Route::get('/{unit}/edit', [PengaturanController::class, 'editUnit'])->name('edit');
            Route::put('/{unit}', [PengaturanController::class, 'updateUnit'])->name('update');
            Route::delete('/{unit}', [PengaturanController::class, 'destroyUnit'])->name('destroy');
        });
    });

    // Route untuk Kontrak
    Route::prefix('kontrak')->name('kontrak.')->group(function() {
        Route::get('aktif', [KontrakController::class, 'kontrakAktif'])->name('aktif');
        Route::get('non_aktif', [KontrakController::class, 'kontrakNonAktif'])->name('non_aktif'); // Akan diimplementasikan nanti

        // AJAX DataTables
        Route::get('ajax-kontrak-aktif', [KontrakController::class, 'ajax_DTKontrakAktif'])->name('ajax.DTKontrakAktif');
        Route::get('ajax-kontrak-non-aktif', [KontrakController::class, 'ajax_DTKontrakNonAktif'])->name('ajax.DTKontrakNonAktif');
        
        // Route::get('ajax-kontrak-non-aktif', [KontrakController::class, 'ajax_DTKontrakNonAktif'])->name('ajax.DTKontrakNonAktif'); // Akan diimplementasikan nanti

        // CRUD Kontrak
        Route::post('store', [KontrakController::class, 'store'])->name('store');
        Route::get('{kontrak}/edit', [KontrakController::class, 'edit'])->name('edit');
        Route::put('{kontrak}', [KontrakController::class, 'update'])->name('update');
        Route::delete('{kontrak}', [KontrakController::class, 'destroy'])->name('destroy');

        // Dropdown dan Detail untuk Form Kontrak
        Route::get('unit-options', [KontrakController::class, 'getUnitOptions'])->name('unit.options');
        Route::get('unit-details/{unit}', [KontrakController::class, 'getUnitDetails'])->name('unit.details');
        Route::get('penghuni-options', [KontrakController::class, 'getPenghuniOptions'])->name('penghuni.options');

        Route::post('putus/{kontrak}', [KontrakController::class, 'putusKontrak'])->name('putus');
    });


    //ujicoba
    Route::get('sadarkajabar', function () {

        $nik = "3204282606900008";
        $nik2 = "1102072705850001";
        dd(getDataIndividu($nik));
    
        
    });
});