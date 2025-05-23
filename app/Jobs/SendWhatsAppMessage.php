<?php

namespace App\Jobs;

use App\Models\Karyawan;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

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
            Http::withHeaders([
                'Authorization' => 'Bearer ' . env('WHATSAPP_AUTH_BEARER')
            ])->post(env('WHATSAPP_API_URL'), [
                'recipient_type' => 'individual',
                'to' => $whatsappNumber,
                'type' => 'text',
                'text' => [
                    'body' => $messageText
                ]
            ]);
        } catch (\Exception $e) {
            // Log error jika diperlukan
            throw $e;
        }
    }
}
