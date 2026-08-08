<?php

namespace App\Services;

use App\Enums\StatusSetoran;
use App\Jobs\SendQRISClaimNotificationJob;
use App\Models\Admin;
use App\Models\BuktiSetoran;
use App\Models\SetoranTabungan;
use App\Models\User;
use Filament\Notifications\Notification;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class KirimKlaimPembayaranService
{
    public function execute(
        User $user,
        SetoranTabungan $setoran,
        Carbon $waktuKlaimBayar,
        string $namaPembayar,
        ?string $referensiPembayaran,
        ?string $catatanPengguna,
        ?UploadedFile $buktiPembayaran
    ): SetoranTabungan {
        if ($setoran->user_id !== $user->id) {
            throw new AuthorizationException('Anda tidak berhak mengakses transaksi ini.');
        }

        if (! in_array($setoran->status, [StatusSetoran::MENUNGGU_PEMBAYARAN, StatusSetoran::PERLU_REVISI])) {
            throw new \RuntimeException('Status transaksi saat ini tidak mengizinkan konfirmasi pembayaran.');
        }

        return DB::transaction(function () use ($user, $setoran, $waktuKlaimBayar, $namaPembayar, $referensiPembayaran, $catatanPengguna, $buktiPembayaran) {
            $isTerlambat = $waktuKlaimBayar->greaterThan($setoran->kedaluwarsa_at);
            $statusLama = $setoran->status;

            $updateData = [
                'status' => StatusSetoran::MENUNGGU_VERIFIKASI,
                'waktu_klaim_bayar' => $waktuKlaimBayar,
                'nama_pembayar' => $namaPembayar,
                'referensi_pembayaran' => $referensiPembayaran,
                'catatan_pengguna' => $catatanPengguna,
                'dikirim_at' => Carbon::now(),
                'is_terlambat' => $isTerlambat,
            ];

            if ($buktiPembayaran) {
                $path = $buktiPembayaran->store(
                    "setoran-tabungan/{$setoran->nomor_setoran}",
                    'private'
                );

                $updateData['bukti_pembayaran_path'] = $path;

                BuktiSetoran::where('setoran_id', $setoran->id)
                    ->where('is_terkini', true)
                    ->update(['is_terkini' => false]);

                BuktiSetoran::create([
                    'setoran_id' => $setoran->id,
                    'file_path' => $path,
                    'nama_asli' => $buktiPembayaran->getClientOriginalName(),
                    'mime_type' => $buktiPembayaran->getClientMimeType(),
                    'ukuran_file' => $buktiPembayaran->getSize(),
                    'diunggah_oleh_type' => get_class($user),
                    'diunggah_oleh_id' => $user->id,
                    'is_terkini' => true,
                ]);
            }

            $setoran->update($updateData);

            CatatRiwayatStatusSetoranService::catat(
                $setoran,
                $statusLama,
                StatusSetoran::MENUNGGU_VERIFIKASI,
                get_class($user),
                $user->id,
                'Anggota mengirim klaim pembayaran'
            );

            DB::afterCommit(function () use ($setoran) {
                SendQRISClaimNotificationJob::dispatch($setoran);

                try {
                    $notification = Notification::make()
                        ->title('Klaim Setoran Baru')
                        ->body("Klaim {$setoran->metode_pembayaran->label()} dari anggota {$setoran->user->name} sebesar Rp".number_format($setoran->jumlah, 0, ',', '.')." (#{$setoran->nomor_setoran})")
                        ->info();

                    foreach (Admin::all() as $admin) {
                        $admin->notify($notification->toDatabase());
                    }
                } catch (\Throwable $e) {
                    Log::error('Fails to send Filament database notification to admins: '.$e->getMessage());
                }
            });

            return $setoran;
        });
    }
}
