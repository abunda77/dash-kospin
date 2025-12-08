<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\WelcomeController;


Route::get('/', [WelcomeController::class, 'index']);

// Route::name('filament.admin.pages.')->group(function () {
//     Route::get('admin/merge-old-transactions/{id_tabungan?}', \App\Filament\Pages\MergeOldTransactions::class)
//         ->name('merge-old-transactions');
// });

Route::get('/reset-password/{token}', function (string $token) {
    return view('auth.reset-password', ['token' => $token]);
})->middleware('guest')->name('password.reset');

Route::post('/reset-password', [App\Http\Controllers\Api\AuthController::class, 'resetPassword'])
    ->middleware('guest')
    ->name('password.update');

// PDF Report download routes
Route::get('/download-report/{filename}', function (string $filename) {
    $filepath = storage_path('app/public/reports/' . basename($filename));
    
    if (!file_exists($filepath)) {
        abort(404, 'File not found');
    }
    
    // Security check - only allow PDF files
    if (!str_ends_with(strtolower($filename), '.pdf')) {
        abort(403, 'Invalid file type');
    }
    
    return response()->download($filepath, $filename, [
        'Content-Type' => 'application/pdf',
    ]);
})->name('report.download');

// Progress monitor page
Route::get('/export-monitor', function () {
    return view('export-progress');
})->name('export.monitor');

// Progress check route for AJAX monitoring
Route::get('/export-progress/{key}', function (string $key) {
    $progress = \Illuminate\Support\Facades\Cache::get($key);
    
    if (!$progress) {
        return response()->json(['error' => 'Progress not found'], 404);
    }
    
    return response()->json($progress);
})->name('export.progress');

// Tabungan Barcode Routes
Route::get('/tabungan/{id}/print-barcode', [App\Http\Controllers\TabunganBarcodeController::class, 'printBarcode'])
    ->name('tabungan.print-barcode');

Route::get('/tabungan/{hash}/scan', [App\Http\Controllers\TabunganBarcodeController::class, 'scan'])
    ->middleware('throttle:60,1') // 60 requests per minute
    ->name('tabungan.scan');

// Debug route untuk test QR code
Route::get('/test-qr/{id}', [App\Http\Controllers\TabunganBarcodeController::class, 'testQrCode'])
    ->name('tabungan.test-qr');

// Makan Bergizi Gratis Public Routes
Route::get('/makan-bergizi-sinara/{hash?}', App\Livewire\MakanBergizisGratisCheckout::class)
    ->name('makan-bergizi-gratis.checkout');

// QRIS Public Generator
Route::get('/qris-generator', App\Livewire\QrisPublicGenerator::class)
    ->name('qris.public-generator');

// Mobile App Request - Public page for closed beta access request
Route::get('/mobile-app', App\Livewire\MobileAppRequest::class)
    ->name('mobile-app.request');

