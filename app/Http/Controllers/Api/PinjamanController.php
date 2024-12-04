<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Pinjaman;
use Illuminate\Support\Facades\Log;
use App\Models\TransaksiPinjaman;

class PinjamanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function getPinjamanByProfile(Request $request)
    {
        try {
            // Validasi input
            $validatedData = $request->validate([
                'id_profile' => 'required|numeric'
            ]);

            // Cari semua pinjaman berdasarkan id_profile
            $pinjaman = Pinjaman::with(['profile', 'produkPinjaman'])
                ->where('profile_id', $validatedData['id_profile'])
                ->get();

            // Simpan first() result ke variable terpisah
            $firstPinjaman = $pinjaman->first();

            if (!$firstPinjaman) {
                return response()->json([
                    'status' => false,
                    'message' => 'Data pinjaman tidak ditemukan'
                ], 404);
            }

            return response()->json([
                'status' => true,
                'message' => 'Data pinjaman berhasil diambil',
                'data' => [
                    'info_profile' => [
                        'id_profile' => $validatedData['id_profile'],
                        'nama_lengkap' => $firstPinjaman->profile->first_name . ' ' . $firstPinjaman->profile->last_name,
                        'no_identity' => $firstPinjaman->profile->no_identity,
                        'phone' => $firstPinjaman->profile->phone
                    ],
                    'pinjaman' => $pinjaman->map(function($item) {
                        return [
                            'no_pinjaman' => $item->no_pinjaman,
                            'jumlah_pinjaman' => $item->jumlah_pinjaman,
                            'tanggal_pinjaman' => $item->tanggal_pinjaman,
                            'jangka_waktu' => $item->jangka_waktu . ' ' . $item->jangka_waktu_satuan,
                            'tanggal_jatuh_tempo' => $item->tanggal_jatuh_tempo,
                            'status_pinjaman' => $item->status_pinjaman,
                            'produk_pinjaman' => $item->produkPinjaman->nama_produk ?? null
                        ];
                    })
                ]
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error in getPinjamanByProfile: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan saat mengambil data pinjaman',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getHistoryPembayaran(Request $request)
    {
        try {
            // Validasi input
            $validatedData = $request->validate([
                'no_pinjaman' => 'required|string'
            ]);

            // Cari pinjaman berdasarkan nomor pinjaman
            $pinjaman = Pinjaman::with(['profile', 'produkPinjaman'])
                ->where('no_pinjaman', $validatedData['no_pinjaman'])
                ->first();

            if (!$pinjaman) {
                return response()->json([
                    'status' => false,
                    'message' => 'Data pinjaman tidak ditemukan'
                ], 404);
            }

            // Ambil history pembayaran
            $transaksi = TransaksiPinjaman::where('pinjaman_id', $pinjaman->id_pinjaman)
                ->orderBy('angsuran_ke', 'desc')
                ->get()
                ->map(function ($item) {
                    return [
                        'angsuran_ke' => $item->angsuran_ke,
                        'tanggal_pembayaran' => $item->tanggal_pembayaran->format('d/m/Y'),
                        'angsuran_pokok' => $item->angsuran_pokok,
                        'angsuran_bunga' => $item->angsuran_bunga,
                        'denda' => $item->denda,
                        'total_pembayaran' => $item->total_pembayaran,
                        'status_pembayaran' => $item->status_pembayaran,
                        'hari_terlambat' => $item->hari_terlambat
                    ];
                });

            // Hitung total
            $totalPokok = $transaksi->sum('angsuran_pokok');
            $totalBunga = $transaksi->sum('angsuran_bunga');
            $totalDenda = $transaksi->sum('denda');
            $totalPembayaran = $transaksi->sum('total_pembayaran');

            return response()->json([
                'status' => true,
                'message' => 'Data history pembayaran berhasil diambil',
                'data' => [
                    'info_pinjaman' => [
                        'no_pinjaman' => $pinjaman->no_pinjaman,
                        'nama_nasabah' => $pinjaman->profile->first_name . ' ' . $pinjaman->profile->last_name,
                        'produk_pinjaman' => $pinjaman->produkPinjaman->nama_produk,
                        'jumlah_pinjaman' => $pinjaman->jumlah_pinjaman,
                        'tanggal_pinjaman' => $pinjaman->tanggal_pinjaman->format('d/m/Y'),
                        'status_pinjaman' => $pinjaman->status_pinjaman
                    ],
                    'pembayaran' => [
                        'transaksi' => $transaksi,
                        'summary' => [
                            'total_pokok' => $totalPokok,
                            'total_bunga' => $totalBunga,
                            'total_denda' => $totalDenda,
                            'total_pembayaran' => $totalPembayaran
                        ]
                    ]
                ]
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error in getHistoryPembayaran: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan saat mengambil data history pembayaran',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
