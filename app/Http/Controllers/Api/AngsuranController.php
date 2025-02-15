<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Pinjaman;
use App\Models\TransaksiPinjaman;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class AngsuranController extends Controller
{
    public function getAngsuranDetails(Request $request)
    {
        try {
            // Validasi input
            $validatedData = $request->validate([
                'no_pinjaman' => 'required|string'
            ]);

            // Cari pinjaman berdasarkan nomor pinjaman
            $pinjaman = Pinjaman::with([
                'profile', 
                'produkPinjaman', 
                'biayaBungaPinjaman',
                'denda',
                'transaksiPinjaman'
            ])
            ->where('no_pinjaman', $validatedData['no_pinjaman'])
            ->first();

            if (!$pinjaman) {
                return response()->json([
                    'status' => false,
                    'message' => 'Data pinjaman tidak ditemukan'
                ], 404);
            }

            // Hitung detail angsuran
            $angsuranDetails = $this->calculateAngsuran($pinjaman);

            return response()->json([
                'status' => true,
                'message' => 'Data angsuran berhasil diambil',
                'data' => [
                    'info_pinjaman' => [
                        'no_pinjaman' => $pinjaman->no_pinjaman,
                        'nama_nasabah' => $pinjaman->profile->first_name . ' ' . $pinjaman->profile->last_name,
                        'produk_pinjaman' => $pinjaman->produkPinjaman->nama_produk,
                        'jumlah_pinjaman' => $pinjaman->jumlah_pinjaman,
                        'jangka_waktu' => $pinjaman->jangka_waktu,
                        'tanggal_pinjaman' => $pinjaman->tanggal_pinjaman->format('d/m/Y'),
                        'bunga_per_tahun' => $pinjaman->biayaBungaPinjaman->persentase_bunga . '%',
                        'status_pinjaman' => $pinjaman->status_pinjaman,
                        'rate_denda' => $pinjaman->denda->rate_denda . '%'
                    ],
                    'detail_angsuran' => $angsuranDetails
                ]
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error in getAngsuranDetails: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan saat mengambil data angsuran',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    private function calculateAngsuran($pinjaman)
    {
        $pokok = $pinjaman->jumlah_pinjaman;
        $bungaPerTahun = $pinjaman->biayaBungaPinjaman->persentase_bunga;
        $jangkaWaktu = $pinjaman->jangka_waktu;
        $rateDenda = $pinjaman->denda->rate_denda;

        // Hitung bunga per bulan
        $bungaPerBulan = ($pokok * ($bungaPerTahun/100)) / $jangkaWaktu;

        // Hitung angsuran pokok per bulan
        $angsuranPokok = $pokok / $jangkaWaktu;

        // Total angsuran per bulan (tetap)
        $totalAngsuran = $angsuranPokok + $bungaPerBulan;

        $angsuranList = [];
        $sisaPokok = $pokok;
        $today = Carbon::now()->startOfDay();

        // Ambil tanggal awal pinjaman
        $tanggalJatuhTempo = $pinjaman->tanggal_pinjaman->copy();

        for ($i = 1; $i <= $jangkaWaktu; $i++) {
            // Tambah 1 bulan untuk tanggal jatuh tempo berikutnya
            $tanggalJatuhTempo = $tanggalJatuhTempo->addMonth();
            
            // Cek status pembayaran
            $transaksi = $pinjaman->transaksiPinjaman
                ->where('angsuran_ke', $i)
                ->first();

            // Hitung denda jika belum dibayar dan melewati tanggal jatuh tempo
            $denda = 0;
            $hariTerlambat = 0;
            $statusPembayaran = 'BELUM BAYAR';
            $tanggalPembayaran = null;

            if ($transaksi) {
                $denda = $transaksi->denda;
                $hariTerlambat = $transaksi->hari_terlambat;
                $statusPembayaran = $transaksi->status_pembayaran;
                $tanggalPembayaran = $transaksi->tanggal_pembayaran ? 
                    $transaksi->tanggal_pembayaran->format('d/m/Y') : null;
            } else {
                // Hitung denda untuk angsuran yang belum dibayar
                if ($today->gt($tanggalJatuhTempo)) {
                    $hariTerlambat = $tanggalJatuhTempo->diffInDays($today);
                    $denda = ($rateDenda/100 * $angsuranPokok / 30) * $hariTerlambat;
                }
            }

            // Hitung sisa hari
            $sisaHari = $today->diffInDays($tanggalJatuhTempo, false);
            $statusJatuhTempo = $sisaHari < 0 ? 'Telah lewat ' . abs($sisaHari) . ' hari' : 
                               ($sisaHari == 0 ? 'Hari ini' : 'Sisa ' . $sisaHari . ' hari');

            $angsuranList[] = [
                'periode' => $i,
                'pokok' => round($angsuranPokok),
                'bunga' => round($bungaPerBulan),
                'total_angsuran' => round($totalAngsuran),
                'sisa_pokok' => round($sisaPokok - $angsuranPokok),
                'tanggal_jatuh_tempo' => $tanggalJatuhTempo->format('d/m/Y'),
                'countdown' => $statusJatuhTempo,
                'status_pembayaran' => $statusPembayaran,
                'tanggal_pembayaran' => $tanggalPembayaran,
                'denda' => round($denda),
                'hari_terlambat' => $hariTerlambat,
                'total_tagihan' => round($totalAngsuran + $denda)
            ];

            $sisaPokok -= $angsuranPokok;
        }

        return $angsuranList;
    }
}
