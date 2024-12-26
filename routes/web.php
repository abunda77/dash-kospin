<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

Route::get('/', function () {
    return view('welcome');
});

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
