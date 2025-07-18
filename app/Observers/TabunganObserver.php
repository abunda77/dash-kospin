<?php

namespace App\Observers;

use App\Models\Tabungan;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TabunganObserver
{
    /**
     * Handle the Tabungan "created" event.
     */
    public function created(Tabungan $tabungan): void
    {
        $this->sendWebhook($tabungan, 'created');
    }

    /**
     * Send webhook notification
     */
    private function sendWebhook(Tabungan $tabungan, string $event): void
    {
        $webhookUrl = env('WEBHOOK_URL');
        
        if (empty($webhookUrl)) {
            Log::warning('WEBHOOK_URL not configured');
            return;
        }

        try {
            $payload = [
                'event' => $event,
                'model' => 'Tabungan',
                'data' => [
                    'id' => $tabungan->id,
                    'no_tabungan' => $tabungan->no_tabungan,
                    'id_profile' => $tabungan->id_profile,
                    'first_name' => $tabungan->profile->first_name ?? null,
                    'last_name' => $tabungan->profile->last_name ?? null,
                    'produk_tabungan' => $tabungan->produk_tabungan,
                    'saldo' => $tabungan->saldo,
                    'tanggal_buka_rekening' => $tabungan->tanggal_buka_rekening,
                    'status_rekening' => $tabungan->status_rekening,
                ],
                'timestamp' => now()->toISOString()
            ];

            Http::timeout(10)
                ->post($webhookUrl, $payload);

            Log::info('Webhook sent successfully for Tabungan', [
                'id' => $tabungan->id,
                'event' => $event
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to send webhook for Tabungan', [
                'id' => $tabungan->id,
                'event' => $event,
                'error' => $e->getMessage()
            ]);
        }
    }
}