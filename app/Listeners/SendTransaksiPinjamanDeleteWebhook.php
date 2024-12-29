<?php

namespace App\Listeners;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use App\Events\TransaksiPinjamanDeleted;

class SendTransaksiPinjamanDeleteWebhook
{
    public function handle(TransaksiPinjamanDeleted $event): void
    {
        try {
            $transaksi = $event->transaksi;
            $webhookUrl = config('services.webhook.transaksi_pinjaman_url');

            if (!$webhookUrl) {
                Log::warning('URL webhook tidak dikonfigurasi');
                return;
            }

            $data = [
                'status_code' => 200,
                'mode' => 'delete',
                'data' => [
                    'id' => $transaksi->id
                ]
            ];

            // Tambahkan timeout dan retry
            $response = Http::timeout(15)
                ->retry(3, 100)
                ->post($webhookUrl, $data);

            // Log response
            Log::info('Mencoba mengirim webhook delete', [
                'url' => $webhookUrl,
                'transaksi_id' => $transaksi->id,
                'response_status' => $response->status(),
                'response_body' => $response->body()
            ]);

            if (!$response->successful()) {
                Log::error('Webhook delete gagal terkirim', [
                    'transaksi_id' => $transaksi->id,
                    'response_status' => $response->status(),
                    'response_body' => $response->body()
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Error saat mengirim webhook delete', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'transaksi_id' => $transaksi->id ?? null
            ]);
        }
    }
}
