<?php

namespace App\Services;

use App\Enums\StatusSetoran;
use App\Models\SetoranTabungan;
use Illuminate\Support\Carbon;

class KadaluarsaSetoranTidakDibayarService
{
    public function execute(): int
    {
        $count = 0;
        SetoranTabungan::query()
            ->where('status', StatusSetoran::MENUNGGU_PEMBAYARAN)
            ->where('kedaluwarsa_at', '<', Carbon::now())
            ->chunkById(100, function ($setorans) use (&$count) {
                foreach ($setorans as $setoran) {
                    $statusLama = $setoran->status;
                    $setoran->update([
                        'status' => StatusSetoran::KEDALUWARSA,
                    ]);
                    CatatRiwayatStatusSetoranService::catat(
                        $setoran,
                        $statusLama,
                        StatusSetoran::KEDALUWARSA,
                        null,
                        null,
                        'Kedaluwarsa otomatis oleh scheduler'
                    );
                    $count++;
                }
            });

        return $count;
    }
}
