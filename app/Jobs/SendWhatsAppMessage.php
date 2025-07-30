<?php

namespace App\Jobs;

use App\Models\Karyawan;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SendWhatsAppMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        protected string $message,
        protected Karyawan $karyawan
    ) {}

    public function handle(): void
    {
        $messageText = str_replace(
            ['{first_name}', '{last_name}'],
            [$this->karyawan->first_name, $this->karyawan->last_name],
            $this->message
        );

        // Format nomor telepon
        $whatsappNumber = preg_replace('/^(\+62|62|0)/', '', $this->karyawan->no_telepon);
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
            $this->sendToWebhook($whatsappNumber, $messageText, $response->status());

        } catch (\Exception $e) {
            // Kirim data ke webhook N8N meskipun ada error
            $this->sendToWebhook($whatsappNumber, $messageText, null);
            
            // Log error jika diperlukan
            Log::error('Error sending WhatsApp message: ' . $e->getMessage(), [
                'karyawan_id' => $this->karyawan->id,
                'whatsapp' => $whatsappNumber
            ]);
            throw $e;
        }
    }

    private function sendToWebhook($whatsapp, $message, $whatsappStatus = null)
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
                'karyawan_id' => $this->karyawan->id,
                'karyawan_name' => $this->karyawan->first_name . ' ' . $this->karyawan->last_name,
                'source' => 'send_whatsapp_message_job',
                'whatsapp_status_code' => $whatsappStatus,
                'whatsapp_sent_successfully' => $whatsappStatus === 200,
                'timestamp' => now()->toISOString()
            ];

            $response = Http::timeout(30)->post($webhookUrl, $payload);

            if ($response->successful()) {
                Log::info('Data berhasil dikirim ke webhook N8N dari SendWhatsAppMessage Job', [
                    'karyawan_id' => $this->karyawan->id,
                    'webhook_url' => $webhookUrl,
                    'status_code' => $response->status()
                ]);
            } else {
                Log::warning('Gagal mengirim data ke webhook N8N dari SendWhatsAppMessage Job', [
                    'karyawan_id' => $this->karyawan->id,
                    'webhook_url' => $webhookUrl,
                    'status_code' => $response->status(),
                    'response_body' => $response->body()
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Error mengirim data ke webhook N8N dari SendWhatsAppMessage Job: ' . $e->getMessage(), [
                'karyawan_id' => $this->karyawan->id,
                'webhook_url' => $webhookUrl ?? 'tidak tersedia'
            ]);
        }
    }
}
