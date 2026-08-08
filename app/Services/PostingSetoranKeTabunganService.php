<?php

namespace App\Services;

use App\Enums\StatusSetoran;
use App\Models\Admin;
use App\Models\SetoranTabungan;
use App\Models\Tabungan;
use App\Models\TransaksiTabungan;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class PostingSetoranKeTabunganService
{
    public function execute(int $setoranId, int $adminId): SetoranTabungan
    {
        return DB::transaction(function () use ($setoranId, $adminId) {
            $setoran = SetoranTabungan::query()
                ->lockForUpdate()
                ->findOrFail($setoranId);

            if ($setoran->status === StatusSetoran::SELESAI) {
                return $setoran;
            }

            if ($setoran->status !== StatusSetoran::DISETUJUI) {
                throw new \RuntimeException('Setoran belum disetujui.');
            }

            $tabungan = Tabungan::query()
                ->lockForUpdate()
                ->findOrFail($setoran->id_tabungan);

            $alreadyPosted = TransaksiTabungan::query()
                ->where('setoran_id', $setoran->id)
                ->exists();

            if ($alreadyPosted) {
                throw new \RuntimeException('Setoran sudah memiliki transaksi tabungan.');
            }

            TransaksiTabungan::create([
                'id_tabungan' => $tabungan->id,
                'setoran_id' => $setoran->id,
                'jenis_transaksi' => TransaksiTabungan::JENIS_SETORAN,
                'jumlah' => $setoran->jumlah_bayar,
                'tanggal_transaksi' => Carbon::now(),
                'keterangan' => 'Setoran via '.$setoran->metode_pembayaran->label().' - '.$setoran->nomor_setoran,
                'kode_transaksi' => $setoran->nomor_setoran,
                'kode_teller' => $adminId,
            ]);

            $statusLama = $setoran->status;
            $setoran->update([
                'status' => StatusSetoran::SELESAI,
                'diposting_at' => Carbon::now(),
                'selesai_at' => Carbon::now(),
            ]);

            CatatRiwayatStatusSetoranService::catat(
                $setoran,
                $statusLama,
                StatusSetoran::SELESAI,
                Admin::class,
                $adminId,
                'Saldo berhasil diposting ke tabungan'
            );

            return $setoran;
        });
    }
}
