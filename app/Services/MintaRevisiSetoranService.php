<?php

namespace App\Services;

use App\Enums\StatusSetoran;
use App\Models\Admin;
use App\Models\SetoranTabungan;
use Illuminate\Support\Facades\DB;

class MintaRevisiSetoranService
{
    public function execute(Admin $admin, SetoranTabungan $setoran, string $catatanVerifikasi): SetoranTabungan
    {
        if ($setoran->status !== StatusSetoran::SEDANG_DIPERIKSA) {
            throw new \RuntimeException('Transaksi tidak dalam status sedang diperiksa.');
        }

        if (empty(trim($catatanVerifikasi))) {
            throw new \InvalidArgumentException('Instruksi atau catatan revisi wajib diisi.');
        }

        return DB::transaction(function () use ($admin, $setoran, $catatanVerifikasi) {
            $statusLama = $setoran->status;

            $setoran->update([
                'status' => StatusSetoran::PERLU_REVISI,
                'catatan_verifikasi' => $catatanVerifikasi,
            ]);

            CatatRiwayatStatusSetoranService::catat(
                $setoran,
                $statusLama,
                StatusSetoran::PERLU_REVISI,
                get_class($admin),
                $admin->id,
                'Admin meminta revisi bukti: '.$catatanVerifikasi
            );

            return $setoran;
        });
    }
}
