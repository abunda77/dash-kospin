<?php

namespace App\Observers;

use App\Models\Pinjaman;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PinjamanObserver
{
    /**
     * Handle the Pinjaman "created" event.
     */
    public function created(Pinjaman $pinjaman): void
    {
        $this->sendWebhook($pinjaman, 'created');
    }

    /**
     * Send webhook notification
     */
    private function sendWebhook(Pinjaman $pinjaman, string $event): void
    {
        $webhookUrl = env('WEBHOOK_URL');
        
        if (empty($webhookUrl)) {
            Log::warning('WEBHOOK_URL not configured');
            return;
        }

        try {
            $payload = [
                'event' => $event,
                'model' => 'Pinjaman',
                'data' => [
                    'id_pinjaman' => $pinjaman->id_pinjaman,
                    'no_pinjaman' => $pinjaman->no_pinjaman,
                    'profile_id' => $pinjaman->profile_id,
                    'first_name' => $pinjaman->profile->first_name ?? null,
                    'last_name' => $pinjaman->profile->last_name ?? null,
                    'produk_pinjaman_id' => $pinjaman->produk_pinjaman_id,
                    'jumlah_pinjaman' => $pinjaman->jumlah_pinjaman,
                    'tanggal_pinjaman' => $pinjaman->tanggal_pinjaman,
                    'jangka_waktu' => $pinjaman->jangka_waktu,
                    'tanggal_jatuh_tempo' => $pinjaman->tanggal_jatuh_tempo,
                    'status_pinjaman' => $pinjaman->status_pinjaman,
                ],
                'timestamp' => now()->toISOString()
            ];

            Http::timeout(10)
                ->post($webhookUrl, $payload);

            Log::info('Webhook sent successfully for Pinjaman', [
                'id_pinjaman' => $pinjaman->id_pinjaman,
                'event' => $event
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to send webhook for Pinjaman', [
                'id_pinjaman' => $pinjaman->id_pinjaman,
                'event' => $event,
                'error' => $e->getMessage()
            ]);
        }
    }
}