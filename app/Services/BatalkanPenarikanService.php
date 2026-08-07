<?php

namespace App\Services;

use App\Enums\StatusPenarikan;
use App\Models\PenarikanTabungan;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class BatalkanPenarikanService
{
    public function execute(User $user, PenarikanTabungan $penarikan): PenarikanTabungan
    {
        if ($penarikan->user_id !== $user->id) {
            throw new AuthorizationException('Anda tidak berhak mengakses transaksi ini.');
        }

        return DB::transaction(function () use ($user, $penarikan): PenarikanTabungan {
            $penarikanTerkunci = PenarikanTabungan::query()
                ->lockForUpdate()
                ->findOrFail($penarikan->id);

            if ($penarikanTerkunci->user_id !== $user->id) {
                throw new AuthorizationException('Anda tidak berhak mengakses transaksi ini.');
            }

            if ($penarikanTerkunci->status !== StatusPenarikan::MENUNGGU_VERIFIKASI) {
                throw new RuntimeException('Penarikan hanya dapat dibatalkan saat menunggu verifikasi.');
            }

            $statusLama = $penarikanTerkunci->status;
            $penarikanTerkunci->update([
                'status' => StatusPenarikan::DIBATALKAN,
            ]);

            CatatRiwayatStatusPenarikanService::catat(
                $penarikanTerkunci,
                $statusLama,
                StatusPenarikan::DIBATALKAN,
                $user::class,
                $user->id,
                'Penarikan dibatalkan oleh anggota'
            );

            return $penarikanTerkunci;
        });
    }
}
