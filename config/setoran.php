<?php

return [
    'durasi_qris' => env('SETORAN_DURASI_QRIS_MENIT', 30),
    'minimal_jumlah' => env('SETORAN_MINIMAL', 10000),
    'maksimal_jumlah' => env('SETORAN_MAKSIMAL', 100000000),
    'batas_transaksi_aktif' => env('SETORAN_BATAS_AKTIF', 1),
    'nominal_dual_approval' => env('SETORAN_DUAL_APPROVAL_NOMINAL', 1000000),
    'rekening_transfer' => [
        'bank' => env('SETORAN_TRANSFER_BANK', 'BCA'),
        'nomor_rekening' => env('SETORAN_TRANSFER_NOMOR_REKENING', '0889333288'),
        'atas_nama' => env('SETORAN_TRANSFER_ATAS_NAMA', 'KOPERASI SINARA ARTHA'),
    ],
];
