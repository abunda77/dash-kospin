<?php

namespace App\Listeners;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use App\Events\TransaksiTabunganUpdated;

class SendTransaksiTabunganUpdateWebhook
{
    public function handle(TransaksiTabunganUpdated $event): void
    {
        try {
            $transaksi = $event->transaksi;
            $webhookUrl = config('services.webhook.transaksi_tabungan_url');

            if (!$webhookUrl) {
                Log::warning('URL webhook tidak dikonfigurasi');
                return;
            }

            $data = [
                'status_code' => 200,
                'mode' => 'update',
                'data' => [
                    'id' => $transaksi->id,
                    'id_tabungan' => $transaksi->id_tabungan,
                    'jenis_transaksi' => $transaksi->jenis_transaksi,
                    'jumlah' => $transaksi->jumlah,
                    'tanggal_transaksi' => $transaksi->tanggal_transaksi,
                    'keterangan' => $transaksi->keterangan,
                    'kode_transaksi' => $transaksi->kode_transaksi,
                    'kode_teller' => $transaksi->kode_teller,
                    'created_at' => $transaksi->created_at,
                    'updated_at' => $transaksi->updated_at
                ]
            ];

            // Tambahkan timeout dan retry
            $response = Http::timeout(15)
                ->retry(3, 100)
                ->post($webhookUrl, $data);

            // Log response
            Log::info('Mencoba mengirim webhook update', [
                'url' => $webhookUrl,
                'transaksi_id' => $transaksi->id,
                'response_status' => $response->status(),
                'response_body' => $response->body()
            ]);

            if (!$response->successful()) {
                Log::error('Webhook update gagal terkirim', [
                    'transaksi_id' => $transaksi->id,
                    'response_status' => $response->status(),
                    'response_body' => $response->body()
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Error saat mengirim webhook update', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'transaksi_id' => $transaksi->id ?? null
            ]);
        }
    }
}
