<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Pinjaman;
use App\Models\TransaksiPinjaman;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\JsonResponse;
use function response;
use function now;

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
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }

    public function updateStatusPembayaran(Request $request, $id)
    {
        try {
            // Validasi input
            $validatedData = $request->validate([
                'no_pinjaman' => 'required|string'
            ]);

            // Cari transaksi pinjaman
            $transaksiPinjaman = TransaksiPinjaman::where('id', $id)
                ->where('status_pembayaran', 'PENDING')
                ->first();

            if (!$transaksiPinjaman) {
                return response()->json([
                    'status' => false,
                    'message' => 'Data transaksi pinjaman tidak ditemukan atau status bukan PENDING'
                ], 404);
            }

            // Update status pembayaran menjadi LUNAS
            $transaksiPinjaman->status_pembayaran = 'LUNAS';
            $transaksiPinjaman->save();

            return response()->json([
                'status' => true,
                'message' => 'Status pembayaran berhasil diupdate menjadi LUNAS',
                'data' => $transaksiPinjaman
            ]);

        } catch (\Exception $e) {
            Log::error('Error updating payment status: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan saat mengupdate status pembayaran',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }

    public function createTransaksiAngsuran(Request $request)
    {
        try {
            // Validasi input
            $validatedData = $request->validate([
                'no_pinjaman' => 'required|string',
                'periode' => 'required|integer',
                'pokok' => 'required|numeric',
                'bunga' => 'required|numeric',
                'total_angsuran' => 'required|numeric',
                'sisa_pokok' => 'required|numeric',
                'denda' => 'required|numeric',
                'hari_terlambat' => 'required|integer',
                'total_tagihan' => 'required|numeric'
            ]);

            // Cari pinjaman berdasarkan nomor pinjaman
            $pinjaman = Pinjaman::where('no_pinjaman', $validatedData['no_pinjaman'])->first();

            if (!$pinjaman) {
                return response()->json([
                    'status' => false,
                    'message' => 'Data pinjaman tidak ditemukan'
                ], 404);
            }

            // Cek apakah transaksi untuk periode ini sudah ada
            $existingTransaksi = TransaksiPinjaman::where('pinjaman_id', $pinjaman->id_pinjaman)
                ->where('angsuran_ke', $validatedData['periode'])
                ->first();

            if ($existingTransaksi) {
                return response()->json([
                    'status' => false,
                    'message' => 'Transaksi untuk periode ini sudah ada'
                ], 400);
            }

            // Buat transaksi baru
            $transaksi = new TransaksiPinjaman();
            $transaksi->pinjaman_id = $pinjaman->id_pinjaman;
            $transaksi->angsuran_ke = $validatedData['periode'];
            $transaksi->angsuran_pokok = $validatedData['pokok'];
            $transaksi->angsuran_bunga = $validatedData['bunga'];
            $transaksi->total_pembayaran = $validatedData['total_angsuran'];
            $transaksi->sisa_pinjaman = $validatedData['sisa_pokok'];
            $transaksi->denda = $validatedData['denda'];
            $transaksi->hari_terlambat = $validatedData['hari_terlambat'];
            $transaksi->status_pembayaran = 'PENDING';
            $transaksi->tanggal_pembayaran = now();  // Set ke tanggal hari ini
            $transaksi->save();

            return response()->json([
                'status' => true,
                'message' => 'Transaksi angsuran berhasil dibuat',
                'data' => $transaksi
            ]);

        } catch (\Exception $e) {
            Log::error('Error creating payment transaction: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan saat membuat transaksi angsuran',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
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
