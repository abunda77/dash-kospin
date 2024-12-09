<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\PinjamanController;
use App\Http\Controllers\Api\TabunganController;
use App\Http\Controllers\Api\DepositoController;

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
});
