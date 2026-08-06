<?php

namespace App\Services;

use App\Enums\StatusPenarikan;
use App\Models\PenarikanTabungan;
use App\Models\Tabungan;
use App\Models\TransaksiTabungan;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class PostingPenarikanKeTabunganService
{
    public function execute(int $penarikanId, int $adminId): PenarikanTabungan
    {
        return DB::transaction(function () use ($penarikanId, $adminId) {
            $penarikan = PenarikanTabungan::query()
                ->lockForUpdate()
                ->findOrFail($penarikanId);

            if ($penarikan->status === StatusPenarikan::SELESAI) {
                return $penarikan;
            }

            if ($penarikan->status !== StatusPenarikan::DISETUJUI) {
                throw new \RuntimeException('Penarikan belum disetujui.');
            }

            $tabungan = Tabungan::query()
                ->lockForUpdate()
                ->findOrFail($penarikan->id_tabungan);

            $alreadyPosted = TransaksiTabungan::query()
                ->where('penarikan_id', $penarikan->id)
                ->exists();

            if ($alreadyPosted) {
                throw new \RuntimeException('Penarikan sudah memiliki transaksi tabungan.');
            }

            if ($penarikan->jumlah > (int) floor($tabungan->saldo_akhir)) {
                throw new \RuntimeException('Saldo tabungan tidak mencukupi untuk penarikan ini.');
            }

            TransaksiTabungan::create([
                'id_tabungan' => $tabungan->id,
                'penarikan_id' => $penarikan->id,
                'jenis_transaksi' => TransaksiTabungan::JENIS_PENARIKAN,
                'jumlah' => $penarikan->jumlah,
                'tanggal_transaksi' => Carbon::now(),
                'keterangan' => 'Penarikan Simpanan - '.$penarikan->nomor_penarikan.' ke '.$penarikan->nama_bank.' a.n. '.$penarikan->nama_nasabah,
                'kode_transaksi' => $penarikan->nomor_penarikan,
                'kode_teller' => $adminId,
            ]);

            $statusLama = $penarikan->status;
            $penarikan->update([
                'status' => StatusPenarikan::SELESAI,
                'diposting_at' => Carbon::now(),
                'selesai_at' => Carbon::now(),
            ]);

            CatatRiwayatStatusPenarikanService::catat(
                $penarikan,
                $statusLama,
                StatusPenarikan::SELESAI,
                \App\Models\Admin::class,
                $adminId,
                'Penarikan berhasil diposting ke tabungan'
            );

            return $penarikan;
        });
    }
}
