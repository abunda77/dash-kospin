<?php

namespace App\Enums;

enum StatusSetoran: string
{
    case MENUNGGU_PEMBAYARAN = 'menunggu_pembayaran';
    case MENUNGGU_VERIFIKASI = 'menunggu_verifikasi';
    case SEDANG_DIPERIKSA = 'sedang_diperiksa';
    case PERLU_REVISI = 'perlu_revisi';
    case DISETUJUI = 'disetujui';
    case SELESAI = 'selesai';
    case DITOLAK = 'ditolak';
    case KEDALUWARSA = 'kedaluwarsa';
    case DIBATALKAN = 'dibatalkan';

    public function label(): string
    {
        return match ($this) {
            self::MENUNGGU_PEMBAYARAN => 'Menunggu Pembayaran',
            self::MENUNGGU_VERIFIKASI => 'Menunggu Verifikasi',
            self::SEDANG_DIPERIKSA => 'Sedang Diperiksa',
            self::PERLU_REVISI => 'Perlu Revisi',
            self::DISETUJUI => 'Disetujui',
            self::SELESAI => 'Selesai',
            self::DITOLAK => 'Ditolak',
            self::KEDALUWARSA => 'Kedaluwarsa',
            self::DIBATALKAN => 'Dibatalkan',
        };
    }
}
