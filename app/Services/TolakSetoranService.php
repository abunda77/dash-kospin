<?php

namespace App\Services;

use App\Enums\StatusSetoran;
use App\Models\Admin;
use App\Models\SetoranTabungan;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class TolakSetoranService
{
    public function execute(Admin $admin, SetoranTabungan $setoran, string $alasanPenolakan): SetoranTabungan
    {
        if (! in_array($setoran->status, [StatusSetoran::MENUNGGU_VERIFIKASI, StatusSetoran::SEDANG_DIPERIKSA, StatusSetoran::PERLU_REVISI])) {
            throw new \RuntimeException('Transaksi dengan status ini tidak dapat ditolak.');
        }

        if (empty(trim($alasanPenolakan))) {
            throw new \InvalidArgumentException('Alasan penolakan wajib diisi.');
        }

        return DB::transaction(function () use ($admin, $setoran, $alasanPenolakan) {
            $statusLama = $setoran->status;

            $setoran->update([
                'status' => StatusSetoran::DITOLAK,
                'ditolak_at' => Carbon::now(),
                'ditolak_oleh' => $admin->id,
                'alasan_penolakan' => $alasanPenolakan,
            ]);

            CatatRiwayatStatusSetoranService::catat(
                $setoran,
                $statusLama,
                StatusSetoran::DITOLAK,
                get_class($admin),
                $admin->id,
                'Admin menolak setoran dengan alasan: '.$alasanPenolakan
            );

            return $setoran;
        });
    }
}
