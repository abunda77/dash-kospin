<?php

namespace App\Listeners;

use App\Events\DepositoCreated;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class HandleDepositoCreated
{
    public function handle(DepositoCreated $event): void
    {
        $deposito = $event->deposito;

        $data = [
            'status_code' => 200,
            'mode' => 'create',
            'data' => [
                'id' => $deposito->id,
                'id_user' => $deposito->id_user,
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
                'created_at' => $deposito->created_at,
                'updated_at' => $deposito->updated_at
            ]
        ];

        try {
            $webhookUrl = config('services.webhook.deposito_url');

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
                'deposito_id' => $deposito->id,
                'response_status' => $response->status(),
                'response_body' => $response->body()
            ]);

            if (!$response->successful()) {
                Log::error('Webhook gagal terkirim', [
                    'deposito_id' => $deposito->id,
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
                'deposito_id' => $deposito->id ?? null
            ]);
        }
    }
}
