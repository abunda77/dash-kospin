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

// Temporary file download route
Route::get('/download-temp/{filename}', function (string $filename) {
    $filepath = storage_path('app/temp/' . basename($filename));
    
    if (!file_exists($filepath)) {
        abort(404, 'File not found');
    }
    
    // Clean the filename to ensure it's UTF-8 safe
    $safeFilename = preg_replace('/[^\x20-\x7E]/', '', $filename);
    $safeFilename = preg_replace('/[^a-zA-Z0-9\-_\.]/', '-', $safeFilename);
    
    return response()->download($filepath, $safeFilename, [
        'Content-Type' => 'application/pdf',
        'Cache-Control' => 'no-cache',
    ])->deleteFileAfterSend(true);
})->name('download-temp-file');


