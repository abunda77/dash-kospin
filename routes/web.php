<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\TabunganBarcodeController;
use App\Http\Controllers\WelcomeController;
use App\Livewire\Auth\ModernForgotPassword;
use App\Livewire\Auth\ModernLogin;
use App\Livewire\MakanBergizisGratisCheckout;
use App\Livewire\MobileAppRequest;
use App\Livewire\QrisPublicGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

Route::get('/', [WelcomeController::class, 'index']);

// Route::name('filament.admin.pages.')->group(function () {
//     Route::get('admin/merge-old-transactions/{id_tabungan?}', \App\Filament\Pages\MergeOldTransactions::class)
//         ->name('merge-old-transactions');
// });

Route::get('/reset-password/{token}', function (string $token) {
    return view('auth.reset-password', ['token' => $token]);
})->middleware('guest')->name('password.reset');

Route::post('/reset-password', [AuthController::class, 'resetPassword'])
    ->middleware('guest')
    ->name('password.update');

// Modern minimalis login page
Route::get('/login', ModernLogin::class)
    ->middleware('guest')
    ->name('login.modern');

// Route register dinonaktifkan
// Route::get('/register', \App\Livewire\Auth\ModernRegister::class)
//     ->middleware('guest')
//     ->name('register');

Route::get('/forgot-password', ModernForgotPassword::class)
    ->middleware('guest')
    ->name('password.request');

// PDF Report download routes
Route::get('/download-report/{filename}', function (string $filename) {
    $filename = basename($filename);

    if (! str_ends_with(strtolower($filename), '.pdf')) {
        abort(403, 'Invalid file type');
    }

    if (! Storage::disk('public')->exists('reports/'.$filename)) {
        abort(404, 'File not found');
    }

    return Storage::disk('public')->download('reports/'.$filename, $filename, [
        'Content-Type' => 'application/pdf',
    ]);
})->name('report.download');

// Progress monitor page
Route::get('/export-monitor', function () {
    return view('export-progress');
})->name('export.monitor');

// Progress check route for AJAX monitoring
Route::get('/export-progress/{key}', function (string $key) {
    $progress = Cache::get($key);

    if (! $progress) {
        return response()->json(['error' => 'Progress not found'], 404);
    }

    return response()->json($progress);
})->name('export.progress');

// Tabungan Barcode Routes
Route::get('/tabungan/{id}/print-barcode', [TabunganBarcodeController::class, 'printBarcode'])
    ->name('tabungan.print-barcode');

Route::get('/tabungan/{hash}/scan', [TabunganBarcodeController::class, 'scan'])
    ->middleware('throttle:60,1') // 60 requests per minute
    ->name('tabungan.scan');

// Debug route untuk test QR code
Route::get('/test-qr/{id}', [TabunganBarcodeController::class, 'testQrCode'])
    ->name('tabungan.test-qr');

// Makan Bergizi Gratis Public Routes
Route::get('/makan-bergizi-sinara/{hash?}', MakanBergizisGratisCheckout::class)
    ->name('makan-bergizi-gratis.checkout');

// QRIS Public Generator
Route::get('/qris-generator', QrisPublicGenerator::class)
    ->name('qris.public-generator');

// Mobile App Request - Public page for closed beta access request
Route::get('/mobile-app', MobileAppRequest::class)
    ->name('mobile-app.request');
