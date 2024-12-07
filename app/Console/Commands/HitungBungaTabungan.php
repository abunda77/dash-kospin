<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Tabungan;
use App\Models\TransaksiTabungan;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class HitungBungaTabungan extends Command
{
    protected $signature = 'tabungan:hitung-bunga
                          {--check-previous : Cek dan hitung bunga bulan-bulan sebelumnya}
                          {--check-duplikat : Cek dan koreksi bunga duplikat}';

    protected $description = 'Menghitung bunga tabungan berdasarkan saldo harian.
                            Gunakan --check-previous untuk menghitung bunga bulan-bulan sebelumnya.
                            Gunakan --check-duplikat untuk mengecek dan mengoreksi bunga duplikat.';

    public function handle()
    {
        if ($this->option('check-duplikat')) {
            $this->info('Mengecek transaksi bunga duplikat...');
            $this->cekDanKoreksiBungaDuplikat();
            return;
        }

        if ($this->option('check-previous')) {
            $this->info('Mengecek perhitungan bunga bulan-bulan sebelumnya...');
            $this->hitungBungaBulanSebelumnya();
            return;
        }

        // Proses normal untuk bulan ini
        $this->info('Mulai menghitung bunga tabungan bulan ini...');
        $tabungans = Tabungan::where('status_rekening', 'aktif')->get();

        foreach ($tabungans as $tabungan) {
            $this->info("Menghitung bunga untuk rekening {$tabungan->no_tabungan}...");
            $this->hitungBungaPerTabungan($tabungan);
        }

        $this->info('Perhitungan bunga selesai');
    }

    private function cekDanKoreksiBungaDuplikat()
    {
        // Cari transaksi bunga duplikat berdasarkan id_tabungan, jumlah, dan tanggal_transaksi
        $duplikatBunga = DB::table('transaksi_tabungans')
            ->select('id_tabungan', 'jumlah', 'tanggal_transaksi', DB::raw('COUNT(*) as jumlah_duplikat'))
            ->where('kode_transaksi', '6') // Kode untuk bunga
            ->groupBy('id_tabungan', 'jumlah', 'tanggal_transaksi')
            ->having('jumlah_duplikat', '>', 1)
            ->get();

        if ($duplikatBunga->isEmpty()) {
            $this->info("Tidak ditemukan transaksi bunga duplikat");
            return;
        }

        foreach ($duplikatBunga as $duplikat) {
            // Ambil semua transaksi duplikat
            $transaksiDuplikat = DB::table('transaksi_tabungans')
                ->where('id_tabungan', $duplikat->id_tabungan)
                ->where('jumlah', $duplikat->jumlah)
                ->where('tanggal_transaksi', $duplikat->tanggal_transaksi)
                ->where('kode_transaksi', '6')
                ->orderBy('id', 'asc')
                ->get();

            $this->info("Menemukan {$duplikat->jumlah_duplikat} transaksi bunga duplikat:");
            $this->info("Rekening ID: {$duplikat->id_tabungan}");
            $this->info("Tanggal: {$duplikat->tanggal_transaksi}");
            $this->info("Jumlah: Rp " . number_format($duplikat->jumlah, 2));

            // Simpan transaksi pertama, koreksi sisanya
            $firstTransaction = true;
            foreach ($transaksiDuplikat as $transaksi) {
                if ($firstTransaction) {
                    $firstTransaction = false;
                    continue; // Skip transaksi pertama
                }

                // Buat transaksi koreksi untuk duplikat
                TransaksiTabungan::create([
                    'id_tabungan' => $transaksi->id_tabungan,
                    'jenis_transaksi' => TransaksiTabungan::JENIS_PENARIKAN,
                    'jumlah' => $transaksi->jumlah,
                    'tanggal_transaksi' => now(),
                    'keterangan' => 'Koreksi bunga',
                    'kode_transaksi' => 'K',
                    'kode_teller' => '00'
                ]);

                $this->info("Transaksi koreksi telah dibuat untuk ID: {$transaksi->id}");
            }
        }
    }

    private function hitungBungaBulanSebelumnya()
    {
        $tabungans = Tabungan::where('status_rekening', 'aktif')->get();

        // Ambil bulan saat ini
        $bulanSekarang = now();

        // Hitung selisih bulan dari bulan sekarang ke bulan sebelumnya
        $selisihBulan = $bulanSekarang->month == 1 ? 1 : $bulanSekarang->month - 1;

        // Cek bulan-bulan sebelumnya sampai dengan bulan pertama di tahun yang sama
        for ($i = 1; $i <= $selisihBulan; $i++) {
            $bulanCheck = $bulanSekarang->copy()->subMonths($i);

            foreach ($tabungans as $tabungan) {
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
}
