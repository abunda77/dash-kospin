<?php

namespace App\Services;

use App\Enums\StatusPenarikan;
use App\Models\Admin;
use App\Models\PenarikanTabungan;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class TolakPenarikanService
{
    public function execute(Admin $admin, PenarikanTabungan $penarikan, string $alasanPenolakan): PenarikanTabungan
    {
        if (! in_array($penarikan->status, [StatusPenarikan::MENUNGGU_VERIFIKASI, StatusPenarikan::SEDANG_DIPERIKSA, StatusPenarikan::PERLU_REVISI])) {
            throw new \RuntimeException('Transaksi dengan status ini tidak dapat ditolak.');
        }

        if (empty(trim($alasanPenolakan))) {
            throw new \InvalidArgumentException('Alasan penolakan wajib diisi.');
        }

        return DB::transaction(function () use ($admin, $penarikan, $alasanPenolakan) {
            $statusLama = $penarikan->status;

            $penarikan->update([
                'status' => StatusPenarikan::DITOLAK,
                'ditolak_at' => Carbon::now(),
                'ditolak_oleh' => $admin->id,
                'alasan_penolakan' => $alasanPenolakan,
            ]);

            CatatRiwayatStatusPenarikanService::catat(
                $penarikan,
                $statusLama,
                StatusPenarikan::DITOLAK,
                get_class($admin),
                $admin->id,
                'Admin menolak penarikan dengan alasan: '.$alasanPenolakan
            );

            return $penarikan;
        });
    }
}
