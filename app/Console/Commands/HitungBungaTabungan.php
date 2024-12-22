<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Tabungan;
use App\Models\TransaksiTabungan;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use function Laravel\Prompts\select;
use function Laravel\Prompts\text;
use function Laravel\Prompts\confirm;
use function Laravel\Prompts\info;
use function Laravel\Prompts\progress;

class HitungBungaTabungan extends Command
{
    protected $signature = 'tabungan:hitung-bunga
                            {--hapus-duplikat : Hapus duplikat perhitungan bunga}
                            {--all : Hitung bunga untuk semua rekening aktif pada bulan berjalan}';

    protected $description = 'Menghitung bunga tabungan berdasarkan saldo harian.';

    public function handle()
    {
        // Tambahkan pengecekan opsi hapus-duplikat di awal
        if ($this->option('hapus-duplikat')) {
            $this->hapusDuplikatBunga();
            return;
        }

        // Tambahkan pengecekan opsi all
        if ($this->option('all')) {
            $this->hitungBungaSemuaRekening();
            return;
        }

        // Pilihan no_tabungan atau all
        $pilihan = select(
            'Pilih rekening tabungan yang akan dihitung bunganya:',
            [
                'all' => 'Semua rekening aktif',
                'specific' => 'Rekening spesifik'
            ]
        );

        $noTabungan = null;
        if ($pilihan === 'specific') {
            $noTabungan = text(
                'Masukkan nomor rekening:',
                validate: fn (string $value) => match(true) {
                    strlen($value) < 5 => 'Nomor rekening minimal 5 karakter',
                    !Tabungan::where('no_tabungan', $value)->exists() => 'Nomor rekening tidak ditemukan',
                    default => null
                }
            );
        }

        // Pilihan periode perhitungan
        $periode = select(
            'Pilih periode perhitungan bunga:',
            [
                'current' => 'Bulan ini',
                'previous' => 'Bulan sebelumnya'
            ]
        );

        // Konfirmasi sebelum eksekusi
        if (!confirm('Apakah Anda yakin akan melanjutkan perhitungan bunga?')) {
            info('Perhitungan bunga dibatalkan.');
            return;
        }

        // Proses perhitungan
        $this->newLine();
        info('Memulai perhitungan bunga tabungan...');

        if ($pilihan === 'all') {
            $tabungans = Tabungan::where('status_rekening', 'aktif')->get();

            progress(
                label: 'Menghitung bunga tabungan',
                steps: $tabungans,
                callback: function ($tabungan) use ($periode) {
                    if ($periode === 'current') {
                        $this->hitungBungaPerTabungan($tabungan);
                    } else {
                        $this->hitungBungaBulanSebelumnya($tabungan);
                    }
                },
                hint: 'Proses ini mungkin membutuhkan beberapa waktu.'
            );

            $this->newLine(2);
        } else {
            $tabungan = Tabungan::where('no_tabungan', $noTabungan)->first();
            info("Menghitung bunga untuk rekening {$tabungan->no_tabungan}...");

            if ($periode === 'current') {
                $this->hitungBungaPerTabungan($tabungan);
            } else {
                $this->hitungBungaBulanSebelumnya($tabungan);
            }
        }

        info('Perhitungan bunga selesai');
        $this->newLine();
    }

    private function hitungBungaBulanSebelumnya($tabungan)
    {
        // Ambil bulan saat ini
        $bulanSekarang = now();

        // Hitung selisih bulan dari bulan sekarang ke bulan sebelumnya
        $selisihBulan = $bulanSekarang->month == 1 ? 1 : $bulanSekarang->month - 1;

        // Cek bulan-bulan sebelumnya sampai dengan bulan pertama di tahun yang sama
        for ($i = 1; $i <= $selisihBulan; $i++) {
            $bulanCheck = $bulanSekarang->copy()->subMonths($i);

            // Cek apakah sudah ada transaksi bunga untuk bulan tersebut
            $sudahDihitung = TransaksiTabungan::where('id_tabungan', $tabungan->id)
                ->where('kode_transaksi', '6') // Kode untuk bunga
                ->whereYear('tanggal_transaksi', $bulanCheck->year)
                ->whereMonth('tanggal_transaksi', $bulanCheck->month)
                ->exists();

            if (!$sudahDihitung) {
                $this->info("Menghitung bunga untuk rekening {$tabungan->no_tabungan} bulan {$bulanCheck->format('F Y')}...");
                $this->hitungBungaPerTabungan($tabungan, $bulanCheck);
            }
        }
    }

    private function hitungBungaPerTabungan($tabungan, $tanggalHitung = null)
    {
        // Ambil persentase bunga dari produk tabungan
        $persentaseBunga = $tabungan->produkTabungan->beayaTabungan->persentase_bunga;
        $this->info("Persentase bunga: {$persentaseBunga}%");

        // Set tanggal perhitungan
        if ($tanggalHitung) {
            $awalBulan = $tanggalHitung->copy()->startOfMonth();
            $akhirBulan = $tanggalHitung->copy()->endOfMonth();
        } else {
            $awalBulan = now()->startOfMonth();
            $akhirBulan = now()->endOfMonth();
        }

        // Hitung saldo harian
        $totalBunga = 0;
        $currentDate = $awalBulan->copy();

        while ($currentDate <= $akhirBulan) {
            $saldo = $this->hitungSaldoPerTanggal($tabungan->id, $currentDate);
            $bunga = ($saldo * ($persentaseBunga/100) * 1) / 365; // 1 hari
            $totalBunga += $bunga;

            $this->info("Tanggal: {$currentDate->format('Y-m-d')}");
            $this->info("Saldo: Rp " . number_format($saldo, 2));
            $this->info("Bunga harian: Rp " . number_format($bunga, 2));

            $currentDate->addDay();
        }

        $this->info("Total bunga bulan " . $awalBulan->format('F Y') . ": Rp " . number_format($totalBunga, 2));

        // Simpan transaksi bunga
        if ($totalBunga > 0) {
            TransaksiTabungan::create([
                'id_tabungan' => $tabungan->id,
                'jenis_transaksi' => TransaksiTabungan::JENIS_SETORAN,
                'jumlah' => round($totalBunga, 2),
                'tanggal_transaksi' => $akhirBulan,
                'keterangan' => 'Bunga',
                'kode_transaksi' => '6',
                'kode_teller' => '00'
            ]);

            $this->info("Bunga telah dikreditkan ke rekening {$tabungan->no_tabungan}");
        }
    }

    private function hitungSaldoPerTanggal($tabunganId, $tanggal)
    {
        return DB::table('transaksi_tabungans')
            ->where('id_tabungan', $tabunganId)
            ->where('tanggal_transaksi', '<=', $tanggal)
            ->selectRaw('SUM(CASE
                WHEN jenis_transaksi = ? THEN jumlah
                ELSE -jumlah
            END) as saldo', [TransaksiTabungan::JENIS_SETORAN])
            ->value('saldo') ?? 0;
    }

    private function hapusDuplikatBunga()
    {
        $this->info('Mencari duplikat perhitungan bunga...');

        // Ambil data duplikat berdasarkan id_tabungan dan bulan yang sama
        $duplikats = DB::table('transaksi_tabungans')
            ->select('id_tabungan',
                    DB::raw('DATE_FORMAT(tanggal_transaksi, "%Y-%m") as bulan'),
                    DB::raw('COUNT(*) as jumlah'))
            ->where('kode_transaksi', '6')
            ->groupBy('id_tabungan', DB::raw('DATE_FORMAT(tanggal_transaksi, "%Y-%m")'))
            ->having('jumlah', '>', 1)
            ->get();

        if ($duplikats->isEmpty()) {
            $this->info('Tidak ditemukan duplikat perhitungan bunga.');
            return;
        }

        $this->info('Ditemukan ' . $duplikats->count() . ' duplikat perhitungan bunga.');

        foreach ($duplikats as $duplikat) {
            $transaksis = TransaksiTabungan::where('id_tabungan', $duplikat->id_tabungan)
                ->where('kode_transaksi', '6')
                ->whereRaw('DATE_FORMAT(tanggal_transaksi, "%Y-%m") = ?', [$duplikat->bulan])
                ->orderBy('tanggal_transaksi', 'desc')
                ->get();

            // Simpan transaksi pertama (terbaru), hapus sisanya
            $transaksis->skip(1)->each(function ($transaksi) use ($duplikat) {
                $tabungan = Tabungan::find($duplikat->id_tabungan);
                $this->info("Menghapus duplikat bunga untuk rekening {$tabungan->no_tabungan} pada bulan {$duplikat->bulan}");
                $transaksi->delete();
            });
        }

        $this->info('Proses penghapusan duplikat selesai.');
    }

    private function hitungBungaSemuaRekening()
    {
        // Hapus konfirmasi jika dijalankan dengan opsi --all
        if (!$this->option('all')) {
            if (!confirm('Apakah Anda yakin akan menghitung bunga untuk semua rekening aktif?')) {
                info('Perhitungan bunga dibatalkan.');
                return;
            }
        }

        $this->newLine();
        info('Memulai perhitungan bunga untuk semua rekening aktif...');

        $tabungans = Tabungan::where('status_rekening', 'aktif')->get();

        progress(
            label: 'Menghitung bunga tabungan',
            steps: $tabungans,
            callback: function ($tabungan) {
                $this->hitungBungaPerTabungan($tabungan);
            },
            hint: 'Proses ini mungkin membutuhkan beberapa waktu.'
        );

        $this->newLine(2);
        info('Perhitungan bunga selesai');
    }
}
