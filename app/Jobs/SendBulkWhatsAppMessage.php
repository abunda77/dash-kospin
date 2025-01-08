<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

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

                // Tambahkan delay 2 detik untuk menghindari rate limiting
                sleep(2);
            } catch (\Exception $e) {
                // Log error jika diperlukan
                continue;
            }
        }
    }
}
