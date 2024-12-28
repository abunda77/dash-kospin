<?php

namespace App\Listeners;

use App\Events\TransaksiPinjamanCreated;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SendTransaksiPinjamanWebhook
{
    public function handle(TransaksiPinjamanCreated $event): void
    {
        try {
            $transaksi = $event->transaksi;

            // Siapkan data yang akan dikirim ke webhook
            $data = [
                'id' => $transaksi->id,
                'tanggal_pembayaran' => $transaksi->tanggal_pembayaran,
                'pinjaman_id' => $transaksi->pinjaman_id,
                'angsuran_pokok' => $transaksi->angsuran_pokok,
                'angsuran_bunga' => $transaksi->angsuran_bunga,
                'denda' => $transaksi->denda,
                'total_pembayaran' => $transaksi->total_pembayaran,
                'sisa_pinjaman' => $transaksi->sisa_pinjaman,
                'status_pembayaran' => $transaksi->status_pembayaran,
                'angsuran_ke' => $transaksi->angsuran_ke,
                'hari_terlambat' => $transaksi->hari_terlambat,
                'created_at' => $transaksi->created_at,
                'updated_at' => $transaksi->updated_at
            ];

            // Kirim data ke webhook (URL webhook harus dikonfigurasi di .env)
            $response = Http::post(env('WEBHOOK_URL'), $data);

            if (!$response->successful()) {
                Log::error('Webhook gagal terkirim', [
                    'transaksi_id' => $transaksi->id,
                    'response' => $response->body()
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Error saat mengirim webhook', [
                'message' => $e->getMessage(),
                'transaksi_id' => $transaksi->id ?? null
            ]);
        }
    }
}
