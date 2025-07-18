<?php

namespace App\Observers;

use App\Models\Deposito;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DepositoObserver
{
    /**
     * Handle the Deposito "created" event.
     */
    public function created(Deposito $deposito): void
    {
        $this->sendWebhook($deposito, 'created');
    }

    /**
     * Send webhook notification
     */
    private function sendWebhook(Deposito $deposito, string $event): void
    {
        $webhookUrl = env('WEBHOOK_URL');
        
        if (empty($webhookUrl)) {
            Log::warning('WEBHOOK_URL not configured');
            return;
        }

        try {
            $payload = [
                'event' => $event,
                'model' => 'Deposito',
                'data' => [
                    'id' => $deposito->id,
                    'id_user' => $deposito->id_user,
                    'first_name' => $deposito->profile->first_name ?? null,
                    'last_name' => $deposito->profile->last_name ?? null,
                    'nomor_rekening' => $deposito->nomor_rekening,
                    'nominal_penempatan' => $deposito->nominal_penempatan,
                    'jangka_waktu' => $deposito->jangka_waktu,
                    'tanggal_pembukaan' => $deposito->tanggal_pembukaan,
                    'tanggal_jatuh_tempo' => $deposito->tanggal_jatuh_tempo,
                    'rate_bunga' => $deposito->rate_bunga,
                    'nominal_bunga' => $deposito->nominal_bunga,
                    'status' => $deposito->status,
                    'perpanjangan_otomatis' => $deposito->perpanjangan_otomatis,
                    'notes' => $deposito->notes,
                ],
                'timestamp' => now()->toISOString()
            ];

            Http::timeout(10)
                ->post($webhookUrl, $payload);

            Log::info('Webhook sent successfully for Deposito', [
                'id' => $deposito->id,
                'event' => $event
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to send webhook for Deposito', [
                'id' => $deposito->id,
                'event' => $event,
                'error' => $e->getMessage()
            ]);
        }
    }
}