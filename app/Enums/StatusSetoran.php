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
}
