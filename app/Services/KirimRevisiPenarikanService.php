<?php

namespace App\Services;

use App\Enums\StatusPenarikan;
use App\Models\BuktiPenarikan;
use App\Models\PenarikanTabungan;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class KirimRevisiPenarikanService
{
    public function execute(
        User $user,
        PenarikanTabungan $penarikan,
        ?string $referensiPenarikan,
        ?string $catatanPengguna,
        ?UploadedFile $buktiPenarikan
    ): PenarikanTabungan {
        if ($penarikan->user_id !== $user->id) {
            throw new \Illuminate\Auth\Access\AuthorizationException('Anda tidak berhak mengakses transaksi ini.');
        }

        if ($penarikan->status !== StatusPenarikan::PERLU_REVISI) {
            throw new \RuntimeException('Status transaksi saat ini tidak mengizinkan pengiriman revisi.');
        }

        return DB::transaction(function () use ($user, $penarikan, $referensiPenarikan, $catatanPengguna, $buktiPenarikan) {
            $statusLama = $penarikan->status;

            $updateData = [
                'status' => StatusPenarikan::MENUNGGU_VERIFIKASI,
                'referensi_penarikan' => $referensiPenarikan ?? $penarikan->referensi_penarikan,
                'catatan_pengguna' => $catatanPengguna ?? $penarikan->catatan_pengguna,
                'dikirim_at' => Carbon::now(),
            ];

            if ($buktiPenarikan) {
                $path = $buktiPenarikan->store(
                    "penarikan-tabungan/{$penarikan->nomor_penarikan}",
                    'private'
                );

                $updateData['bukti_penarikan_path'] = $path;

                BuktiPenarikan::where('penarikan_id', $penarikan->id)
                    ->where('is_terkini', true)
                    ->update(['is_terkini' => false]);

                BuktiPenarikan::create([
                    'penarikan_id' => $penarikan->id,
                    'file_path' => $path,
                    'nama_asli' => $buktiPenarikan->getClientOriginalName(),
                    'mime_type' => $buktiPenarikan->getClientMimeType(),
                    'ukuran_file' => $buktiPenarikan->getSize(),
                    'diunggah_oleh_type' => get_class($user),
                    'diunggah_oleh_id' => $user->id,
                    'is_terkini' => true,
                ]);
            }

            $penarikan->update($updateData);

            CatatRiwayatStatusPenarikanService::catat(
                $penarikan,
                $statusLama,
                StatusPenarikan::MENUNGGU_VERIFIKASI,
                get_class($user),
                $user->id,
                'Anggota mengirim revisi penarikan'
            );

            return $penarikan;
        });
    }
}
