<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppGatewayService
{
    public function kirimPesan(string $nomorTujuan, string $pesan): bool
    {
        try {
            $baseUrl = config('whatsapp_gateway.base_url');
            if (empty($baseUrl)) {
                return false;
            }

            $response = Http::withBasicAuth(
                config('whatsapp_gateway.username'),
                config('whatsapp_gateway.password')
            )
                ->withHeaders([
                    'X-Device-Id' => config('whatsapp_gateway.device_id'),
                ])
                ->timeout((int) config('whatsapp_gateway.timeout', 10))
                ->post($baseUrl.'/send/message', [
                    'phone' => $nomorTujuan,
                    'message' => $pesan,
                    'reply_message_id' => '',
                    'is_forwarded' => false,
                ]);

            if (! $response->successful()) {
                Log::warning('WhatsApp Gateway: gagal mengirim pesan.', [
                    'nomor' => $nomorTujuan,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return false;
            }

            return true;
        } catch (\Throwable $e) {
            Log::error('WhatsApp Gateway: exception saat mengirim pesan.', [
                'nomor' => $nomorTujuan,
                'message' => $e->getMessage(),
            ]);

            return false;
        }
    }

    public function kirimKeAdmins(string $pesan): void
    {
        $adminWaList = config('whatsapp_gateway.admin_wa');
        if (empty($adminWaList)) {
            return;
        }

        $numbers = array_filter(array_map('trim', explode(',', $adminWaList)));
        foreach ($numbers as $num) {
            $jid = $this->formatJid($num);
            $this->kirimPesan($jid, $pesan);
        }
    }

    private function formatJid(string $phone): string
    {
        $phone = preg_replace('/@s\.whatsapp\.net$/', '', trim($phone));
        $phone = preg_replace('/[^0-9]/', '', $phone);

        if (str_starts_with($phone, '0')) {
            $phone = '62'.substr($phone, 1);
        }

        return $phone.'@s.whatsapp.net';
    }
}
