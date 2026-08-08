<?php

use App\Http\Controllers\Api\AngsuranController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BannerMobileController;
use App\Http\Controllers\Api\BarcodeScanController;
use App\Http\Controllers\Api\ConfigController;
use App\Http\Controllers\Api\DepositoController;
use App\Http\Controllers\Api\MakanBergizisGratisController;
use App\Http\Controllers\Api\MutasiTabunganController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\PenarikanSimpananController;
use App\Http\Controllers\Api\PinjamanController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\RegionController;
use App\Http\Controllers\Api\SetoranSimpananController;
use App\Http\Controllers\Api\TabunganController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

    // Penarikan Simpanan
    Route::prefix('penarikan')->group(function () {
        Route::get('/rekening-options', [PenarikanSimpananController::class, 'rekeningOptions']);
        Route::get('/aktif', [PenarikanSimpananController::class, 'aktif']);
        Route::get('/history', [PenarikanSimpananController::class, 'history']);
        Route::get('/{id}', [PenarikanSimpananController::class, 'show']);
        Route::post('/', [PenarikanSimpananController::class, 'store']);
        Route::post('/{id}/revisi', [PenarikanSimpananController::class, 'kirimRevisi']);
        Route::post('/{id}/batalkan', [PenarikanSimpananController::class, 'batalkan']);
    });

    // Setoran Simpanan (QRIS / Transfer Rekening)
    Route::prefix('setoran')->group(function () {
        Route::get('/rekening-options', [SetoranSimpananController::class, 'rekeningOptions']);
        Route::get('/aktif', [SetoranSimpananController::class, 'aktif']);
        Route::get('/history', [SetoranSimpananController::class, 'history']);
        Route::get('/{id}', [SetoranSimpananController::class, 'show']);
        Route::post('/', [SetoranSimpananController::class, 'store']);
        Route::post('/{id}/klaim', [SetoranSimpananController::class, 'klaimPembayaran']);
        Route::post('/{id}/batalkan', [SetoranSimpananController::class, 'batalkan']);
    });
});

Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);

Route::get('banner-mobile/type/{type?}', [BannerMobileController::class, 'getByType']);

Route::apiResource('regions', RegionController::class);

Route::get('/mutasi/{no_tabungan}/{periode}', [MutasiTabunganController::class, 'getMutasi']);

Route::get('/config/api-base-url', [ConfigController::class, 'getApiBaseUrl']);

// Barcode Scan Statistics API (public)
Route::prefix('barcode')->group(function () {
    Route::get('/stats', [BarcodeScanController::class, 'stats'])
        ->middleware('throttle:30,1');

    Route::get('/recent-scans', [BarcodeScanController::class, 'recentScans'])
        ->middleware('throttle:30,1');

    // Protected routes (require authentication)
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/my-scans', [BarcodeScanController::class, 'myScans']);
        Route::get('/tabungan/{id}/scan-history', [BarcodeScanController::class, 'scanHistory']);
    });
});

// Payment / QRIS API
Route::prefix('payment')->group(function () {
    Route::get('/qris', [PaymentController::class, 'listQris'])
        ->middleware('throttle:60,1');

    Route::get('/qris/{id}', [PaymentController::class, 'showQris'])
        ->middleware('throttle:60,1');

    Route::post('/qris/validate', [PaymentController::class, 'validateQris'])
        ->middleware('throttle:30,1');

    Route::post('/qris/generate-dynamic', [PaymentController::class, 'generateDynamic'])
        ->middleware('throttle:30,1');
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
