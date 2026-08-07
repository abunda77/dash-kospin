<?php

namespace App\Services;

use App\Enums\StatusSetoran;
use App\Models\SetoranTabungan;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class BatalkanSetoranService
{
    public function execute(User $user, SetoranTabungan $setoran): SetoranTabungan
    {
        if ($setoran->user_id !== $user->id) {
            throw new AuthorizationException('Anda tidak berhak mengakses transaksi ini.');
        }

        return DB::transaction(function () use ($user, $setoran): SetoranTabungan {
            $setoranTerkunci = SetoranTabungan::query()
                ->lockForUpdate()
                ->findOrFail($setoran->id);

            if ($setoranTerkunci->user_id !== $user->id) {
                throw new AuthorizationException('Anda tidak berhak mengakses transaksi ini.');
            }

            if ($setoranTerkunci->status !== StatusSetoran::MENUNGGU_PEMBAYARAN) {
                throw new RuntimeException('Setoran hanya dapat dibatalkan saat menunggu pembayaran.');
            }

            $statusLama = $setoranTerkunci->status;
            $setoranTerkunci->update([
                'status' => StatusSetoran::DIBATALKAN,
            ]);

            CatatRiwayatStatusSetoranService::catat(
                $setoranTerkunci,
                $statusLama,
                StatusSetoran::DIBATALKAN,
                $user::class,
                $user->id,
                'Setoran dibatalkan oleh anggota'
            );

            return $setoranTerkunci;
        });
    }
}
