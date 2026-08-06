<?php

namespace App\Services;

use App\Enums\StatusPenarikan;
use App\Models\Admin;
use App\Models\PenarikanTabungan;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class SetujuiPenarikanService
{
    public function execute(
        Admin $admin,
        PenarikanTabungan $penarikan,
        ?string $referensiTransfer = null,
        ?Carbon $waktuTransfer = null,
        ?string $catatanVerifikasi = null
    ): PenarikanTabungan {
        if ($penarikan->status !== StatusPenarikan::SEDANG_DIPERIKSA) {
            throw new \RuntimeException('Transaksi tidak dalam status sedang diperiksa.');
        }

        if ($referensiTransfer) {
            $exists = PenarikanTabungan::where('referensi_transfer', $referensiTransfer)
                ->whereIn('status', [StatusPenarikan::DISETUJUI, StatusPenarikan::SELESAI])
                ->where('id', '!=', $penarikan->id)
                ->exists();
            if ($exists) {
                throw new \RuntimeException('Referensi transfer sudah digunakan.');
            }
        }

        $penarikanResponse = DB::transaction(function () use ($admin, $penarikan, $referensiTransfer, $waktuTransfer, $catatanVerifikasi) {
            $statusLama = $penarikan->status;

            $penarikan->update([
                'status' => StatusPenarikan::DISETUJUI,
                'disetujui_at' => Carbon::now(),
                'direview_at' => Carbon::now(),
                'referensi_transfer' => $referensiTransfer,
                'waktu_transfer' => $waktuTransfer,
                'catatan_verifikasi' => $catatanVerifikasi,
            ]);

            CatatRiwayatStatusPenarikanService::catat(
                $penarikan,
                $statusLama,
                StatusPenarikan::DISETUJUI,
                get_class($admin),
                $admin->id,
                'Admin menyetujui penarikan'
            );

            return $penarikan;
        });

        try {
            app(PostingPenarikanKeTabunganService::class)->execute($penarikanResponse->id, $admin->id);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Posting penarikan ke tabungan gagal setelah persetujuan: '.$e->getMessage());
        }

        return $penarikanResponse->fresh();
    }
}
