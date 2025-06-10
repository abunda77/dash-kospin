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


