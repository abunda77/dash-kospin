<?php

namespace App\Services;

use App\Enums\StatusPenarikan;
use App\Models\Admin;
use App\Models\PenarikanTabungan;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class MulaiReviewPenarikanService
{
    public function execute(Admin $admin, PenarikanTabungan $penarikan): PenarikanTabungan
    {
        if ($penarikan->status !== StatusPenarikan::MENUNGGU_VERIFIKASI) {
            throw new \RuntimeException('Transaksi tidak dalam status menunggu verifikasi.');
        }

        return DB::transaction(function () use ($admin, $penarikan) {
            $statusLama = $penarikan->status;

            $penarikan->update([
                'status' => StatusPenarikan::SEDANG_DIPERIKSA,
                'mulai_review_at' => Carbon::now(),
                'diperiksa_oleh' => $admin->id,
            ]);

            CatatRiwayatStatusPenarikanService::catat(
                $penarikan,
                $statusLama,
                StatusPenarikan::SEDANG_DIPERIKSA,
                get_class($admin),
                $admin->id,
                'Admin mulai melakukan review pemeriksaan'
            );

            return $penarikan;
        });
    }
}
