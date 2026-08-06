<?php

namespace App\Enums;

enum StatusPenarikan: string
{
    case MENUNGGU_VERIFIKASI = 'menunggu_verifikasi';
    case SEDANG_DIPERIKSA = 'sedang_diperiksa';
    case PERLU_REVISI = 'perlu_revisi';
    case DISETUJUI = 'disetujui';
    case SELESAI = 'selesai';
    case DITOLAK = 'ditolak';
    case DIBATALKAN = 'dibatalkan';
}
