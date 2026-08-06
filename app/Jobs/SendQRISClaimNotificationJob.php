<?php

namespace App\Jobs;

use App\Models\SetoranTabungan;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SendQRISClaimNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected SetoranTabungan $setoran;

    public function __construct(SetoranTabungan $setoran)
    {
        $this->setoran = $setoran;
    }

    public function handle(): void
    {
        $user = $this->setoran->user;
        $tabungan = $this->setoran->tabungan;

        $userName = $user?->name ?? 'Anggota';
        $noTabungan = $tabungan?->no_tabungan ?? '-';
        $nominal = 'Rp'.number_format($this->setoran->jumlah, 0, ',', '.');
        $kodeUnik = 'Rp'.number_format($this->setoran->kode_unik, 0, ',', '.');
        $totalBayar = 'Rp'.number_format($this->setoran->jumlah_bayar, 0, ',', '.');
        $waktuKlaim = $this->setoran->waktu_klaim_bayar ? $this->setoran->waktu_klaim_bayar->format('d/m/Y H:i') : '-';
        $waktuKlaim .= ' WIB';
        $namaPembayar = $this->setoran->nama_pembayar ?? '-';

        $pesan = "[KLAIM SETORAN MASUK]\n\n"
            ."Nomor Setoran : {$this->setoran->nomor_setoran}\n"
            ."Nama Anggota  : {$userName}\n"
            ."No. Rekening  : {$noTabungan}\n"
            ."Nominal       : {$nominal}\n"
            ."Kode Unik     : {$kodeUnik}\n"
            ."Total Bayar   : {$totalBayar}\n"
            ."Waktu Klaim   : {$waktuKlaim}\n"
            ."Nama Pembayar : {$namaPembayar}\n\n"
            .'Silakan periksa dashboard admin untuk memverifikasi pembayaran.';

        $adminNumbers = array_filter(array_map('trim', explode(',', config('whatsapp_gateway.admin_wa', ''))));

        foreach ($adminNumbers as $adminNumber) {
            $whatsappNumber = preg_replace('/@s\.whatsapp\.net$/', '', $adminNumber);
            $whatsappNumber = preg_replace('/[^0-9]/', '', $whatsappNumber);
            $whatsappNumber = preg_replace('/^(62|0)/', '', $whatsappNumber);
            $whatsappNumber = '62'.$whatsappNumber;

            try {
                $response = send_whatsapp_api($whatsappNumber, $pesan);
                //                $this->sendToWebhook($whatsappNumber, $pesan, $response->status());

                if (! $response->successful()) {
                    Log::warning('Gagal mengirim notifikasi klaim setoran melalui WhatsApp.', [
                        'setoran_id' => $this->setoran->id,
                        'whatsapp' => $whatsappNumber,
                        'status_code' => $response->status(),
                        'response_body' => $response->body(),
                    ]);
                }
            } catch (\Throwable $e) {
                //              $this->sendToWebhook($whatsappNumber, $pesan);

                Log::error('Error sending setoran WhatsApp notification: '.$e->getMessage(), [
                    'setoran_id' => $this->setoran->id,
                    'whatsapp' => $whatsappNumber,
                ]);
            }
        }
    }

    private function sendToWebhook(string $whatsapp, string $message, ?int $whatsappStatus = null): void
    {
        $webhookUrl = config('whatsapp_gateway.webhook_url');

        if (empty($webhookUrl)) {
            return;
        }

        try {
            Http::timeout(30)->post($webhookUrl, [
                'whatsapp' => $whatsapp,
                'message' => $message,
                'setoran_id' => $this->setoran->id,
                'nomor_setoran' => $this->setoran->nomor_setoran,
                'source' => 'send_qris_claim_notification_job',
                'whatsapp_status_code' => $whatsappStatus,
                'whatsapp_sent_successfully' => $whatsappStatus === 200,
                'timestamp' => now()->toISOString(),
            ]);
        } catch (\Throwable $e) {
            Log::error('Error mengirim webhook notifikasi klaim setoran: '.$e->getMessage(), [
                'setoran_id' => $this->setoran->id,
                'whatsapp' => $whatsapp,
            ]);
        }
    }
}
