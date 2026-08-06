<?php

namespace App\Services;

use App\Enums\StatusPenarikan;
use App\Models\Admin;
use App\Models\PenarikanTabungan;
use Illuminate\Support\Facades\DB;

class MintaRevisiPenarikanService
{
    public function execute(Admin $admin, PenarikanTabungan $penarikan, string $catatanVerifikasi): PenarikanTabungan
    {
        if ($penarikan->status !== StatusPenarikan::SEDANG_DIPERIKSA) {
            throw new \RuntimeException('Transaksi tidak dalam status sedang diperiksa.');
        }

        if (empty(trim($catatanVerifikasi))) {
            throw new \InvalidArgumentException('Instruksi atau catatan revisi wajib diisi.');
        }

        return DB::transaction(function () use ($admin, $penarikan, $catatanVerifikasi) {
            $statusLama = $penarikan->status;

            $penarikan->update([
                'status' => StatusPenarikan::PERLU_REVISI,
                'catatan_verifikasi' => $catatanVerifikasi,
            ]);

            CatatRiwayatStatusPenarikanService::catat(
                $penarikan,
                $statusLama,
                StatusPenarikan::PERLU_REVISI,
                get_class($admin),
                $admin->id,
                'Admin meminta revisi penarikan: '.$catatanVerifikasi
            );

            return $penarikan;
        });
    }
}
