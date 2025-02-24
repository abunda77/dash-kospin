<?php

namespace App\Http\Controllers\Api;

use App\Models\Tabungan;
use App\Models\TransaksiTabungan;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller; // Import base Controller class

class MutasiTabunganController extends Controller
{
    public function getMutasi(Request $request, $no_tabungan, $periode)
    {
        try {
            $tabungan = Tabungan::where('no_tabungan', $no_tabungan)->first();

            if (!$tabungan) {
                return response()->json(['message' => 'Tabungan tidak ditemukan'], 404);
            }

            $query = TransaksiTabungan::where('id_tabungan', $tabungan->id)
                ->orderBy('tanggal_transaksi', 'DESC')
                ->orderBy('id', 'DESC');

            switch ($periode) {
                case '10_terakhir':
                    $transaksi = $query->take(10)->get();
                    break;
                case '1_minggu_terakhir':
                    $transaksi = $query->where('tanggal_transaksi', '>=', Carbon::now()->subWeek())->get();
                    break;
                case '1_bulan_terakhir':
                    $transaksi = $query->where('tanggal_transaksi', '>=', Carbon::now()->subMonth())->get();
                    break;
                default:
                    return response()->json(['message' => 'Periode tidak valid'], 400);
            }

            // Format data transaksi (opsional)
            $formattedTransaksi = $transaksi->map(function ($item) {
                return [
                    'id' => $item->id,
                    'tanggal_transaksi' => $item->tanggal_transaksi,
                    'jenis_transaksi' => $item->jenis_transaksi,
                    'jumlah' => $item->jumlah,
                    'keterangan' => $item->keterangan,
                    // tambahkan field lain sesuai kebutuhan
                ];
            });

            return response()->json([
                'no_tabungan' => $tabungan->no_tabungan,
                'periode' => $periode,
                'transaksi' => $formattedTransaksi,
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching mutasi: ' . $e->getMessage());
            return response()->json(['message' => 'Terjadi kesalahan server'], 500);
        }
    }
}
