<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

// Jalankan setiap akhir bulan pukul 23:59
Schedule::command('tabungan:hitung-bunga --all')
    ->monthlyOn(date('t'), '23:59')
    ->after(function() {
        Artisan::call('tabungan:hitung-bunga --hapus-duplikat');
    });
