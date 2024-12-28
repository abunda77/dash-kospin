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
            $webhookUrl = config('services.webhook.transaksi_pinjaman_url');

            if (!$webhookUrl) {
                Log::warning('URL webhook tidak dikonfigurasi');
                return;
            }

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

            // Tambahkan timeout dan retry
            $response = Http::timeout(15) // timeout 15 detik
                ->retry(3, 100) // retry 3x dengan jeda 100ms
                ->post($webhookUrl, $data);

            // Tambahkan logging lebih detail
            Log::info('Mencoba mengirim webhook', [
                'url' => $webhookUrl,
                'transaksi_id' => $transaksi->id,
                'response_status' => $response->status(),
                'response_body' => $response->body()
            ]);

            if (!$response->successful()) {
                Log::error('Webhook gagal terkirim', [
                    'transaksi_id' => $transaksi->id,
                    'response_status' => $response->status(),
                    'response_body' => $response->body()
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Error saat mengirim webhook', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'transaksi_id' => $transaksi->id ?? null
            ]);
        }
    }
}
