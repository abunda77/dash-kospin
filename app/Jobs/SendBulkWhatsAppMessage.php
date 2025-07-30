<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SendBulkWhatsAppMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        protected string $message,
        protected Collection $karyawan
    ) {}

    public function handle(): void
    {
        foreach ($this->karyawan as $karyawan) {
            $messageText = $this->message;

            // Ganti variabel placeholder
            $messageText = str_replace(
                ['{first_name}', '{last_name}'],
                [$karyawan->first_name, $karyawan->last_name],
                $messageText
            );

            // Format nomor telepon (pastikan menggunakan kode negara 62)
            $whatsappNumber = preg_replace('/^(\+62|62|0)/', '', $karyawan->no_telepon);
            $whatsappNumber = '62' . $whatsappNumber;

            try {
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . env('WHATSAPP_AUTH_BEARER')
                ])->post(env('WHATSAPP_API_URL'), [
                    'recipient_type' => 'individual',
                    'to' => $whatsappNumber,
                    'type' => 'text',
                    'text' => [
                        'body' => $messageText
                    ]
                ]);

                // Kirim data ke webhook N8N apapun status kode pengiriman WhatsApp
                $this->sendToWebhook($whatsappNumber, $messageText, $karyawan, $response->status());

                // Tambahkan delay 2 detik untuk menghindari rate limiting
                sleep(2);
            } catch (\Exception $e) {
                // Kirim data ke webhook N8N meskipun ada error
                $this->sendToWebhook($whatsappNumber, $messageText, $karyawan, null);
                
                // Log error jika diperlukan
                Log::error('Error sending bulk WhatsApp message: ' . $e->getMessage(), [
                    'karyawan_id' => $karyawan->id,
                    'whatsapp' => $whatsappNumber
                ]);
                continue;
            }
        }
    }

    private function sendToWebhook($whatsapp, $message, $karyawan, $whatsappStatus = null)
    {
        try {
            $webhookUrl = env('WEBHOOK_WA_N8N');
            
            if (empty($webhookUrl)) {
                Log::warning('WEBHOOK_WA_N8N tidak dikonfigurasi di .env');
                return;
            }

            $payload = [
                'whatsapp' => $whatsapp,
                'message' => $message,
                'karyawan_id' => $karyawan->id,
                'karyawan_name' => $karyawan->first_name . ' ' . $karyawan->last_name,
                'source' => 'send_bulk_whatsapp_message_job',
                'whatsapp_status_code' => $whatsappStatus,
                'whatsapp_sent_successfully' => $whatsappStatus === 200,
                'timestamp' => now()->toISOString()
            ];

            $response = Http::timeout(30)->post($webhookUrl, $payload);

            if ($response->successful()) {
                Log::info('Data berhasil dikirim ke webhook N8N dari SendBulkWhatsAppMessage Job', [
                    'karyawan_id' => $karyawan->id,
                    'webhook_url' => $webhookUrl,
                    'status_code' => $response->status()
                ]);
            } else {
                Log::warning('Gagal mengirim data ke webhook N8N dari SendBulkWhatsAppMessage Job', [
                    'karyawan_id' => $karyawan->id,
                    'webhook_url' => $webhookUrl,
                    'status_code' => $response->status(),
                    'response_body' => $response->body()
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Error mengirim data ke webhook N8N dari SendBulkWhatsAppMessage Job: ' . $e->getMessage(), [
                'karyawan_id' => $karyawan->id,
                'webhook_url' => $webhookUrl ?? 'tidak tersedia'
            ]);
        }
    }
}
