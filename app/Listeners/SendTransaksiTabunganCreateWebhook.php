<?php

namespace App\Listeners;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use App\Events\TransaksiTabunganCreated;

class SendTransaksiTabunganCreateWebhook
{
    public function handle(TransaksiTabunganCreated $event): void
    {
        $transaksi = $event->transaksi;

        $data = [
            'status_code' => 200,
            'mode' => 'create',
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

        try {
            $webhookUrl = config('services.webhook.transaksi_tabungan_url');

            if (!$webhookUrl) {
                Log::warning('URL webhook tidak dikonfigurasi');
                return;
            }

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
