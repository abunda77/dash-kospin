<?php

namespace App\Observers;

use App\Models\TransaksiTabungan;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TransaksiTabunganObserver
{
    /**
     * Handle the TransaksiTabungan "created" event.
     */
    public function created(TransaksiTabungan $transaksiTabungan): void
    {
        $this->sendWebhook($transaksiTabungan, 'created');
    }

    /**
     * Send webhook notification
     */
    private function sendWebhook(TransaksiTabungan $transaksiTabungan, string $event): void
    {
        $webhookUrl = env('WEBHOOK_URL');
        
        if (empty($webhookUrl)) {
            Log::warning('WEBHOOK_URL not configured');
            return;
        }

        try {
            $payload = [
                'event' => $event,
                'model' => 'TransaksiTabungan',
                'data' => [
                    'id' => $transaksiTabungan->id,
                    'id_tabungan' => $transaksiTabungan->id_tabungan,
                    'profile_id' => $transaksiTabungan->tabungan->id_profile ?? null,
                    'first_name' => $transaksiTabungan->tabungan->profile->first_name ?? null,
                    'last_name' => $transaksiTabungan->tabungan->profile->last_name ?? null,
                    'jenis_transaksi' => $transaksiTabungan->jenis_transaksi,
                    'jumlah' => $transaksiTabungan->jumlah,
                    'tanggal_transaksi' => $transaksiTabungan->tanggal_transaksi,
                    'keterangan' => $transaksiTabungan->keterangan,
                    'kode_transaksi' => $transaksiTabungan->kode_transaksi,
                    'kode_teller' => $transaksiTabungan->kode_teller,
                ],
                'timestamp' => now()->toISOString()
            ];

            Http::timeout(10)
                ->post($webhookUrl, $payload);

            Log::info('Webhook sent successfully for TransaksiTabungan', [
                'id' => $transaksiTabungan->id,
                'event' => $event
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to send webhook for TransaksiTabungan', [
                'id' => $transaksiTabungan->id,
                'event' => $event,
                'error' => $e->getMessage()
            ]);
        }
    }
}