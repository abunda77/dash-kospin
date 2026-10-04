<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Security Blacklist File
    |--------------------------------------------------------------------------
    |
    | Lokasi file teks berisi daftar domain email dan alamat IP yang diblokir.
    | Format tiap baris: "<tipe>:<nilai>" dengan tipe "email" atau "ip".
    | Baris yang diawali "#" diperlakukan sebagai komentar.
    |
    */

    'blacklist_path' => env('SECURITY_BLACKLIST_PATH') ?: base_path('blacklist.txt'),

];
