<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Route::name('filament.admin.pages.')->group(function () {
//     Route::get('admin/merge-old-transactions/{id_tabungan?}', \App\Filament\Pages\MergeOldTransactions::class)
//         ->name('merge-old-transactions');
// });
