<?php

namespace App\Services;

use App\Enums\MetodePembayaranSetoran;
use App\Enums\StatusSetoran;
use App\Models\QrisStatic;
use App\Models\SetoranTabungan;
use App\Models\Tabungan;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class BuatSetoranTabunganService
{
    private QrisGeneratorService $qrisGenerator;

    public function __construct(QrisGeneratorService $qrisGenerator)
    {
        $this->qrisGenerator = $qrisGenerator;
    }

    public function execute(
        User $user,
        Tabungan $tabungan,
        int $jumlah,
        MetodePembayaranSetoran $metodePembayaran = MetodePembayaranSetoran::Qris
    ): SetoranTabungan {
        $min = (int) config('setoran.minimal_jumlah', 10000);
        $max = (int) config('setoran.maksimal_jumlah', 100000000);

        if ($jumlah < $min || $jumlah > $max) {
            throw new \InvalidArgumentException('Nominal setoran minimal Rp'.number_format($min).' dan maksimal Rp'.number_format($max));
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

        $activeLimit = (int) config('setoran.batas_transaksi_aktif', 1);
        $activeCount = SetoranTabungan::where('id_tabungan', $tabungan->id)
            ->whereIn('status', [
                StatusSetoran::MENUNGGU_PEMBAYARAN,
                StatusSetoran::MENUNGGU_VERIFIKASI,
                StatusSetoran::SEDANG_DIPERIKSA,
                StatusSetoran::PERLU_REVISI,
                StatusSetoran::DISETUJUI,
            ])
            ->count();

        if ($activeCount >= $activeLimit) {
            throw new \RuntimeException('Masih memiliki transaksi setoran aktif untuk rekening ini.');
        }

        $staticQris = $metodePembayaran === MetodePembayaranSetoran::Qris
            ? QrisStatic::where('is_active', true)->first()
            : null;

        if ($metodePembayaran === MetodePembayaranSetoran::Qris && ! $staticQris) {
            throw new \RuntimeException('Layanan QRIS statis tidak tersedia saat ini.');
        }

        return DB::transaction(function () use ($user, $tabungan, $jumlah, $metodePembayaran, $staticQris) {
            $nomorSetoran = $this->generateNomorSetoran();
            $kodeUnik = $this->generateKodeUnik($jumlah);
            $jumlahBayar = $jumlah + $kodeUnik;

            $durationMinutes = (int) config('setoran.durasi_qris', 30);
            $qrisDibuatAt = Carbon::now();
            $kedaluwarsaAt = $qrisDibuatAt->copy()->addMinutes($durationMinutes);

            $qrisData = $staticQris
                ? $this->qrisGenerator->generate($staticQris, $jumlahBayar)
                : ['payload' => null, 'image_path' => null];

            $setoran = SetoranTabungan::create([
                'nomor_setoran' => $nomorSetoran,
                'user_id' => $user->id,
                'id_tabungan' => $tabungan->id,
                'jenis_simpanan' => $tabungan->produkTabungan?->nama_produk ?? 'Tabungan',
                'jumlah' => $jumlah,
                'kode_unik' => $kodeUnik,
                'jumlah_bayar' => $jumlahBayar,
                'metode_pembayaran' => $metodePembayaran,
                'qris_payload' => $qrisData['payload'],
                'qris_image_path' => $qrisData['image_path'],
                'qris_dibuat_at' => $staticQris ? $qrisDibuatAt : null,
                'kedaluwarsa_at' => $kedaluwarsaAt,
                'status' => StatusSetoran::MENUNGGU_PEMBAYARAN,
            ]);

            CatatRiwayatStatusSetoranService::catat(
                $setoran,
                null,
                StatusSetoran::MENUNGGU_PEMBAYARAN,
                get_class($user),
                $user->id,
                'Setoran '.$metodePembayaran->label().' berhasil dibuat'
            );

            return $setoran;
        });
    }

    private function generateNomorSetoran(): string
    {
        $dateStr = Carbon::now()->format('Ymd');
        do {
            $randHex = str_pad(random_int(1, 999999), 6, '0', STR_PAD_LEFT);
            $number = "STR-{$dateStr}-{$randHex}";
            $exists = SetoranTabungan::where('nomor_setoran', $number)->exists();
        } while ($exists);

        return $number;
    }

    private function generateKodeUnik(int $jumlah): int
    {
        do {
            $kodeUnik = random_int(1, 999);
            $jumlahBayar = $jumlah + $kodeUnik;

            $exists = SetoranTabungan::where('jumlah_bayar', $jumlahBayar)
                ->whereIn('status', [
                    StatusSetoran::MENUNGGU_PEMBAYARAN,
                    StatusSetoran::MENUNGGU_VERIFIKASI,
                    StatusSetoran::SEDANG_DIPERIKSA,
                    StatusSetoran::PERLU_REVISI,
                    StatusSetoran::DISETUJUI,
                ])
                ->where('kedaluwarsa_at', '>', Carbon::now())
                ->exists();
        } while ($exists);

        return $kodeUnik;
    }
}
