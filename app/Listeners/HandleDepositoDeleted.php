<?php

namespace App\Listeners;

use App\Events\DepositoDeleted;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class HandleDepositoDeleted
{
    public function handle(DepositoDeleted $event): void
    {
        try {
            $deposito = $event->deposito;
            $webhookUrl = config('services.webhook.deposito_url');

            if (!$webhookUrl) {
                Log::warning('URL webhook tidak dikonfigurasi');
                return;
            }

            $data = [
                'status_code' => 200,
                'mode' => 'delete',
                'data' => [
                    'id' => $deposito->id
                ]
            ];

            // Tambahkan timeout dan retry
            $response = Http::timeout(15)
                ->retry(3, 100)
                ->post($webhookUrl, $data);

            // Log response
            Log::info('Mencoba mengirim webhook delete', [
                'url' => $webhookUrl,
                'deposito_id' => $deposito->id,
                'response_status' => $response->status(),
                'response_body' => $response->body()
            ]);

            if (!$response->successful()) {
                Log::error('Webhook delete gagal terkirim', [
                    'deposito_id' => $deposito->id,
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
                'deposito_id' => $deposito->id ?? null
            ]);
        }
    }
}
