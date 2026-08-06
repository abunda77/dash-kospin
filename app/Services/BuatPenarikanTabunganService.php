<?php

namespace App\Services;

use App\Enums\StatusPenarikan;
use App\Jobs\SendPenarikanNotificationJob;
use App\Models\Admin;
use App\Models\BuktiPenarikan;
use App\Models\PenarikanTabungan;
use App\Models\Tabungan;
use App\Models\User;
use Filament\Notifications\Notification;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class BuatPenarikanTabunganService
{
    public function execute(
        User $user,
        Tabungan $tabungan,
        int $jumlah,
        string $bank,
        string $namaBank,
        string $namaNasabah,
        ?string $referensiPenarikan = null,
        ?string $catatanPengguna = null,
        ?UploadedFile $buktiPenarikan = null
    ): PenarikanTabungan {
        $min = (int) config('penarikan.minimal_jumlah', 10000);
        $max = (int) config('penarikan.maksimal_jumlah', 100000000);

        if ($jumlah < $min || $jumlah > $max) {
            throw new \InvalidArgumentException('Nominal penarikan minimal Rp'.number_format($min).' dan maksimal Rp'.number_format($max));
        }

        if ($tabungan->status_rekening !== 'aktif') {
            throw new \InvalidArgumentException('Rekening tabungan tidak aktif.');
        }

        $dimilikiPengguna = Tabungan::query()
            ->whereKey($tabungan->getKey())
            ->whereHas('profile', fn ($query) => $query
                ->where('id_user', $user->getKey())
                ->where('is_active', true))
            ->exists();

        if (! $dimilikiPengguna) {
            throw new \InvalidArgumentException('Rekening tabungan tidak dimiliki anggota atau anggota tidak aktif.');
        }

        if ($jumlah > (int) floor($tabungan->saldo_akhir)) {
            throw new \InvalidArgumentException('Nominal penarikan melebihi saldo tersedia (Rp'.number_format($tabungan->saldo_akhir, 0, ',', '.').').');
        }

        $activeLimit = (int) config('penarikan.batas_transaksi_aktif', 1);
        $activeCount = PenarikanTabungan::where('id_tabungan', $tabungan->id)
            ->whereIn('status', [
                StatusPenarikan::MENUNGGU_VERIFIKASI,
                StatusPenarikan::SEDANG_DIPERIKSA,
                StatusPenarikan::PERLU_REVISI,
                StatusPenarikan::DISETUJUI,
            ])
            ->count();

        if ($activeCount >= $activeLimit) {
            throw new \RuntimeException('Masih memiliki transaksi penarikan aktif untuk rekening ini.');
        }

        return DB::transaction(function () use ($user, $tabungan, $jumlah, $bank, $namaBank, $namaNasabah, $referensiPenarikan, $catatanPengguna, $buktiPenarikan) {
            $nomorPenarikan = $this->generateNomorPenarikan();

            $data = [
                'nomor_penarikan' => $nomorPenarikan,
                'user_id' => $user->id,
                'id_tabungan' => $tabungan->id,
                'jenis_simpanan' => $tabungan->produkTabungan?->nama_produk ?? 'Tabungan',
                'jumlah' => $jumlah,
                'bank' => $bank,
                'nama_bank' => $namaBank,
                'nama_nasabah' => $namaNasabah,
                'referensi_penarikan' => $referensiPenarikan,
                'catatan_pengguna' => $catatanPengguna,
                'status' => StatusPenarikan::MENUNGGU_VERIFIKASI,
                'dikirim_at' => Carbon::now(),
            ];

            if ($buktiPenarikan) {
                $data['bukti_penarikan_path'] = $buktiPenarikan->store(
                    "penarikan-tabungan/{$nomorPenarikan}",
                    'private'
                );
            }

            $penarikan = PenarikanTabungan::create($data);

            if ($buktiPenarikan) {
                BuktiPenarikan::create([
                    'penarikan_id' => $penarikan->id,
                    'file_path' => $data['bukti_penarikan_path'],
                    'nama_asli' => $buktiPenarikan->getClientOriginalName(),
                    'mime_type' => $buktiPenarikan->getClientMimeType(),
                    'ukuran_file' => $buktiPenarikan->getSize(),
                    'diunggah_oleh_type' => get_class($user),
                    'diunggah_oleh_id' => $user->id,
                    'is_terkini' => true,
                ]);
            }

            CatatRiwayatStatusPenarikanService::catat(
                $penarikan,
                null,
                StatusPenarikan::MENUNGGU_VERIFIKASI,
                get_class($user),
                $user->id,
                'Permohonan penarikan berhasil diajukan'
            );

            DB::afterCommit(function () use ($penarikan) {
                SendPenarikanNotificationJob::dispatch($penarikan);

                try {
                    $notification = Notification::make()
                        ->title('Permohonan Penarikan Simpanan Baru')
                        ->body("Permohonan baru dari anggota {$penarikan->user->name} sebesar Rp".number_format($penarikan->jumlah, 0, ',', '.')." (#{$penarikan->nomor_penarikan})")
                        ->info();

                    foreach (Admin::all() as $admin) {
                        $admin->notify($notification->toDatabase());
                    }
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::error('Fails to send Filament database notification to admins: '.$e->getMessage());
                }
            });

            return $penarikan;
        });
    }

    private function generateNomorPenarikan(): string
    {
        $dateStr = Carbon::now()->format('Ymd');
        do {
            $randHex = str_pad(random_int(1, 999999), 6, '0', STR_PAD_LEFT);
            $number = "PNK-{$dateStr}-{$randHex}";
            $exists = PenarikanTabungan::where('nomor_penarikan', $number)->exists();
        } while ($exists);

        return $number;
    }
}
