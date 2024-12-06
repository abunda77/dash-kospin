<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tabungan;
use App\Models\TransaksiTabungan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TabunganController extends Controller
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

    public function getMutasi(Request $request)
    {
        try {
            // Validasi input
            $validatedData = $request->validate([
                'no_tabungan' => 'required|string'
            ]);

            // Cari tabungan berdasarkan nomor rekening
            $tabungan = Tabungan::with(['profile', 'produkTabungan'])
                ->where('no_tabungan', $validatedData['no_tabungan'])
                ->first();

            if (!$tabungan) {
                return response()->json([
                    'status' => false,
                    'message' => 'Rekening tidak ditemukan'
                ], 404);
            }

            // Ambil transaksi dan hitung saldo berjalan
            $transaksi = TransaksiTabungan::where('id_tabungan', $tabungan->id)
                ->orderBy('tanggal_transaksi', 'ASC')
                ->orderBy('id', 'ASC')
                ->get()
                ->map(function ($record) use ($tabungan) {
                    return [
                        'kode_transaksi' => $record->kode_transaksi,
                        'tanggal_transaksi' => $record->tanggal_transaksi,
                        'jenis_transaksi' => $record->jenis_transaksi,
                        'jumlah' => $record->jumlah,
                        'keterangan' => $record->keterangan,
                        'saldo_berjalan' => $this->hitungSaldoBerjalan($record, $tabungan)
                    ];
                });

            return response()->json([
                'status' => true,
                'message' => 'Data saldo berhasil diambil',
                'data' => [
                    'info_rekening' => [
                        'no_tabungan' => $tabungan->no_tabungan,
                        'nama_nasabah' => $tabungan->profile->first_name . ' ' . $tabungan->profile->last_name,
                        'produk_tabungan' => $tabungan->produkTabungan->nama_produk,
                        'saldo_akhir' => $tabungan->saldo,
                        'status_rekening' => $tabungan->status_rekening,
                        'tanggal_buka' => $tabungan->tanggal_buka_rekening
                    ],
                    'mutasi' => $transaksi
                ]
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error in getMutasi: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan saat mengambil data saldo',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    private function hitungSaldoBerjalan($record, $tabungan)
    {
        try {
            $saldo = $tabungan->saldo;

            // Ambil semua transaksi sebelumnya
            $previousTransactions = TransaksiTabungan::where('id_tabungan', $tabungan->id)
                ->where(function($query) use ($record) {
                    $query->where('tanggal_transaksi', '<', $record->tanggal_transaksi)
                        ->orWhere(function($q) use ($record) {
                            $q->where('tanggal_transaksi', '=', $record->tanggal_transaksi)
                              ->where('id', '<=', $record->id);
                        });
                })
                ->orderBy('tanggal_transaksi', 'ASC')
                ->orderBy('id', 'ASC')
                ->get();

            // Hitung saldo berjalan
            foreach ($previousTransactions as $transaction) {
                if ($transaction->jenis_transaksi === 'debit') {
                    $saldo += $transaction->jumlah;
                } else {
                    $saldo -= $transaction->jumlah;
                }
            }

            return $saldo;

        } catch (\Exception $e) {
            Log::error('Error calculating saldo berjalan: ' . $e->getMessage());
            return 0;
        }
    }

    public function getSaldoBerjalan(Request $request)
    {
        try {
            // Validasi input
            $validatedData = $request->validate([
                'no_tabungan' => 'required|string'
            ]);

            // Cari tabungan berdasarkan nomor rekening
            $tabungan = Tabungan::with(['profile', 'produkTabungan'])
                ->where('no_tabungan', $validatedData['no_tabungan'])
                ->first();

            if (!$tabungan) {
                return response()->json([
                    'status' => false,
                    'message' => 'Rekening tidak ditemukan'
                ], 404);
            }

            // Ambil transaksi terakhir dan hitung saldo berjalan
            $lastTransaction = TransaksiTabungan::where('id_tabungan', $tabungan->id)
                ->orderBy('tanggal_transaksi', 'DESC')
                ->orderBy('id', 'DESC')
                ->first();

            if (!$lastTransaction) {
                return response()->json([
                    'status' => true,
                    'message' => 'Data saldo berhasil diambil',
                    'data' => [
                        'info_rekening' => [
                            'no_tabungan' => $tabungan->no_tabungan,
                            'nama_nasabah' => $tabungan->profile->first_name . ' ' . $tabungan->profile->last_name,
                            'produk_tabungan' => $tabungan->produkTabungan->nama_produk,
                            'saldo_berjalan' => 0
                        ]
                    ]
                ], 200);
            }

            // Hitung saldo berjalan dari transaksi terakhir
            $saldoBerjalan = $this->hitungSaldoBerjalan($lastTransaction, $tabungan);

            return response()->json([
                'status' => true,
                'message' => 'Data saldo berhasil diambil',
                'data' => [
                    'info_rekening' => [
                        'no_tabungan' => $tabungan->no_tabungan,
                        'nama_nasabah' => $tabungan->profile->first_name . ' ' . $tabungan->profile->last_name,
                        'produk_tabungan' => $tabungan->produkTabungan->nama_produk,
                        'saldo_berjalan' => $saldoBerjalan
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
            Log::error('Error in getSaldoBerjalan: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan saat mengambil data saldo',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getTabunganByProfile(Request $request)
    {
        try {
            // Validasi input
            $validatedData = $request->validate([
                'id_profile' => 'required|numeric'
            ]);

            // Cari semua tabungan berdasarkan id_profile
            $tabungan = Tabungan::with(['profile', 'produkTabungan'])
                ->where('id_profile', $validatedData['id_profile'])
                ->get();

            // Simpan first() result ke variable terpisah
            $firstTabungan = $tabungan->first();

            if (!$firstTabungan) {
                return response()->json([
                    'status' => false,
                    'message' => 'Data tabungan tidak ditemukan'
                ], 404);
            }

            return response()->json([
                'status' => true,
                'message' => 'Data tabungan berhasil diambil',
                'data' => [
                    'info_profile' => [
                        'id_profile' => $validatedData['id_profile'],
                        'nama_lengkap' => $firstTabungan->profile->first_name . ' ' . $firstTabungan->profile->last_name,
                        'no_identity' => $firstTabungan->profile->no_identity,
                        'phone' => $firstTabungan->profile->phone
                    ],
                    'tabungan' => $tabungan
                ]
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error in getTabunganByProfile: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Terjadi kesalahan saat mengambil data tabungan',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
