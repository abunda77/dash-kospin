<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Pinjaman;
use Carbon\Carbon;

class ReminderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $pinjaman;

    public function __construct(Pinjaman $pinjaman)
    {
        $this->pinjaman = $pinjaman;
    }

    public function handle(): void
    {
        try {
            $nama = trim("{$this->pinjaman->profile->first_name} {$this->pinjaman->profile->last_name}");
            $angsuranPokok = $this->pinjaman->jumlah_pinjaman / $this->pinjaman->jangka_waktu;
            $tanggalJatuhTempo = Carbon::parse($this->pinjaman->tanggal_jatuh_tempo)
                ->setMonth(Carbon::now()->month)
                ->setYear(Carbon::now()->year)
                ->format('d F Y');

            $message = "Halo *{$nama},\n\n*"
                . "Ini adalah pengingat untuk pembayaran angsuran pinjaman Anda:\n"
                . "No Pinjaman: *{$this->pinjaman->no_pinjaman}*\n"
                . "Angsuran: *Rp." . number_format($angsuranPokok, 2, ',', '.') . "*\n"
                . "Jatuh Tempo: *{$tanggalJatuhTempo}*\n\n"
                . "Mohon siapkan pembayaran Anda. Terima kasih.\n\n"
                . "Terima kasih.\n"
                . "Kospin Sinara Artha\n\n"
                . "_NB: Abaikan pesan ini jika sudah melakukan pembayaran_\n";


            $whatsapp = $this->formatWhatsAppNumber($this->pinjaman->profile->whatsapp);

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . env('WHATSAPP_AUTH_BEARER')
            ])->post(env('WHATSAPP_API_URL'), [
                'recipient_type' => 'individual',
                    'to' => $whatsapp,
                    'type' => 'text',
                    'text' => [
                        'body' => $message
                    ]
                ]);

            if ($response->status() !== 200) {
                throw new \Exception('Gagal mengirim pesan WhatsApp: ' . $response->body());
            }

            // Kirim data ke webhook N8N
            $this->sendToWebhook($whatsapp, $message);

            Log::info('Reminder berhasil dikirim', [
                'pinjaman_id' => $this->pinjaman->id,
                'whatsapp' => $whatsapp
            ]);

        } catch (\Exception $e) {
            Log::error('Error mengirim reminder: ' . $e->getMessage(), [
                'pinjaman_id' => $this->pinjaman->id
            ]);
            throw $e;
        }
    }

    private function formatWhatsAppNumber($whatsapp)
    {
        $whatsapp = preg_replace('/[^0-9]/', '', $whatsapp);

        if (substr($whatsapp, 0, 1) === '0') {
            $whatsapp = '62' . substr($whatsapp, 1);
        }

        return $whatsapp;
    }

    private function sendToWebhook($whatsapp, $message)
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
                'pinjaman_id' => $this->pinjaman->id,
                'no_pinjaman' => $this->pinjaman->no_pinjaman,
                'timestamp' => now()->toISOString()
            ];

            $response = Http::timeout(30)->post($webhookUrl, $payload);

            if ($response->successful()) {
                Log::info('Data berhasil dikirim ke webhook N8N', [
                    'pinjaman_id' => $this->pinjaman->id,
                    'webhook_url' => $webhookUrl,
                    'status_code' => $response->status()
                ]);
            } else {
                Log::warning('Gagal mengirim data ke webhook N8N', [
                    'pinjaman_id' => $this->pinjaman->id,
                    'webhook_url' => $webhookUrl,
                    'status_code' => $response->status(),
                    'response_body' => $response->body()
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Error mengirim data ke webhook N8N: ' . $e->getMessage(), [
                'pinjaman_id' => $this->pinjaman->id,
                'webhook_url' => $webhookUrl ?? 'tidak tersedia'
            ]);
        }
    }
}
