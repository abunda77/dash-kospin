<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\PinjamanController;
use App\Http\Controllers\Api\TabunganController;
use App\Http\Controllers\Api\DepositoController;
use App\Http\Controllers\Api\BannerMobileController;
use App\Http\Controllers\Api\RegionController;
use App\Http\Controllers\Api\AngsuranController;
use App\Http\Controllers\Api\MutasiTabunganController;
use App\Http\Controllers\Api\ConfigController;
use App\Http\Controllers\Api\MakanBergizisGratisController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('profiles', ProfileController::class);
    Route::post('/tabungan/mutasi', [TabunganController::class, 'getMutasi']);
    Route::post('/tabungan/saldo-berjalan', [TabunganController::class, 'getSaldoBerjalan']);
    Route::post('/tabungan/by-profile', [TabunganController::class, 'getTabunganByProfile']);
    Route::get('pinjaman/by-profile', [PinjamanController::class, 'getPinjamanByProfile']);
    Route::post('/pinjaman/history-pembayaran', [PinjamanController::class, 'getHistoryPembayaran']);
    Route::get('/deposito/by-profile', [DepositoController::class, 'getDepositoByProfile']);
    Route::get('/deposito/detail', [DepositoController::class, 'getDetailByNoRekening']);
    Route::patch('/update-password', [AuthController::class, 'updatePassword']);

    // Tambahkan route baru untuk Angsuran
    Route::get('/angsuran/details', [AngsuranController::class, 'getAngsuranDetails']);
    Route::post('/angsuran/create', [AngsuranController::class, 'createTransaksiAngsuran']);
    Route::patch('/angsuran/{id}/update-status', [AngsuranController::class, 'updateStatusPembayaran']);
});

Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);

Route::get('banner-mobile/type/{type?}', [BannerMobileController::class, 'getByType']);

Route::apiResource('regions', RegionController::class);


Route::get('/mutasi/{no_tabungan}/{periode}', [MutasiTabunganController::class, 'getMutasi']);

Route::get('/config/api-base-url', [ConfigController::class, 'getApiBaseUrl']);

// Barcode Scan Statistics API (public)
Route::prefix('barcode')->group(function () {
    Route::get('/stats', [App\Http\Controllers\Api\BarcodeScanController::class, 'stats'])
        ->middleware('throttle:30,1');

    Route::get('/recent-scans', [App\Http\Controllers\Api\BarcodeScanController::class, 'recentScans'])
        ->middleware('throttle:30,1');

    // Protected routes (require authentication)
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/my-scans', [App\Http\Controllers\Api\BarcodeScanController::class, 'myScans']);
        Route::get('/tabungan/{id}/scan-history', [App\Http\Controllers\Api\BarcodeScanController::class, 'scanHistory']);
    });
});

// Makan Bergizi Gratis API
Route::prefix('makan-bergizi-gratis')->group(function () {
    Route::get('/', [MakanBergizisGratisController::class, 'index'])
        ->middleware('throttle:60,1');
    
    Route::get('/{id}', [MakanBergizisGratisController::class, 'show'])
        ->middleware('throttle:60,1');
    
    Route::post('/check-today', [MakanBergizisGratisController::class, 'checkToday'])
        ->middleware('throttle:60,1');
    
    Route::post('/', [MakanBergizisGratisController::class, 'store'])
        ->middleware('throttle:60,1');
});
