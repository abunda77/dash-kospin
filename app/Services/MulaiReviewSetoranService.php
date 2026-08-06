<?php

namespace App\Services;

use App\Enums\StatusSetoran;
use App\Models\Admin;
use App\Models\SetoranTabungan;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class MulaiReviewSetoranService
{
    public function execute(Admin $admin, SetoranTabungan $setoran): SetoranTabungan
    {
        if ($setoran->status !== StatusSetoran::MENUNGGU_VERIFIKASI) {
            throw new \RuntimeException('Transaksi tidak dalam status menunggu verifikasi.');
        }

        return DB::transaction(function () use ($admin, $setoran) {
            $statusLama = $setoran->status;

            $setoran->update([
                'status' => StatusSetoran::SEDANG_DIPERIKSA,
                'mulai_review_at' => Carbon::now(),
                'diperiksa_oleh' => $admin->id,
            ]);

            CatatRiwayatStatusSetoranService::catat(
                $setoran,
                $statusLama,
                StatusSetoran::SEDANG_DIPERIKSA,
                get_class($admin),
                $admin->id,
                'Admin mulai melakukan review pemeriksaan'
            );

            return $setoran;
        });
    }
}
