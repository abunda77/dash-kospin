<?php

namespace App\Services;

use App\Enums\StatusSetoran;
use App\Models\Admin;
use App\Models\SetoranTabungan;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class SetujuiSetoranService
{
    public function execute(
        Admin $admin,
        SetoranTabungan $setoran,
        ?string $referensiTransaksiProvider = null,
        ?Carbon $waktuBayarProvider = null,
        ?string $namaPembayarProvider = null,
        ?string $catatanVerifikasi = null
    ): SetoranTabungan {
        if ($setoran->status !== StatusSetoran::SEDANG_DIPERIKSA) {
            throw new \RuntimeException('Transaksi tidak dalam status sedang diperiksa.');
        }

        if ($referensiTransaksiProvider) {
            $exists = SetoranTabungan::where('referensi_transaksi_provider', $referensiTransaksiProvider)
                ->whereIn('status', [StatusSetoran::DISETUJUI, StatusSetoran::SELESAI])
                ->where('id', '!=', $setoran->id)
                ->exists();
            if ($exists) {
                throw new \RuntimeException('Referensi transaksi provider sudah digunakan.');
            }
        }

        $setoranResponse = DB::transaction(function () use ($admin, $setoran, $referensiTransaksiProvider, $waktuBayarProvider, $namaPembayarProvider, $catatanVerifikasi) {
            $statusLama = $setoran->status;

            $setoran->update([
                'status' => StatusSetoran::DISETUJUI,
                'disetujui_at' => Carbon::now(),
                'direview_at' => Carbon::now(),
                'referensi_transaksi_provider' => $referensiTransaksiProvider,
                'waktu_bayar_provider' => $waktuBayarProvider,
                'nama_pembayar_provider' => $namaPembayarProvider,
                'catatan_verifikasi' => $catatanVerifikasi,
            ]);

            CatatRiwayatStatusSetoranService::catat(
                $setoran,
                $statusLama,
                StatusSetoran::DISETUJUI,
                get_class($admin),
                $admin->id,
                'Admin menyetujui setoran'
            );

            return $setoran;
        });

        try {
            app(PostingSetoranKeTabunganService::class)->execute($setoranResponse->id, $admin->id);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Posting setoran ke tabungan gagal setelah persetujuan: '.$e->getMessage());
        }

        return $setoranResponse->fresh();
    }
}
