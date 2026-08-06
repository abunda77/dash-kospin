<?php

namespace App\Jobs;

use App\Models\PenarikanTabungan;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendPenarikanNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected PenarikanTabungan $penarikan;

    public function __construct(PenarikanTabungan $penarikan)
    {
        $this->penarikan = $penarikan;
    }

    public function handle(): void
    {
        $user = $this->penarikan->user;
        $tabungan = $this->penarikan->tabungan;

        $userName = $user?->name ?? 'Anggota';
        $noTabungan = $tabungan?->no_tabungan ?? '-';
        $nominal = 'Rp'.number_format($this->penarikan->jumlah, 0, ',', '.');
        $dikirimAt = $this->penarikan->dikirim_at ? $this->penarikan->dikirim_at->format('d/m/Y H:i') : '-';
        $dikirimAt .= ' WIB';

        $pesan = "[PERMOHONAN PENARIKAN SIMPANAN]\n\n"
            ."Nomor Penarikan : {$this->penarikan->nomor_penarikan}\n"
            ."Nama Anggota    : {$userName}\n"
            ."No. Rekening    : {$noTabungan}\n"
            ."Nominal         : {$nominal}\n"
            ."Bank Tujuan     : {$this->penarikan->bank} - {$this->penarikan->nama_bank}\n"
            ."Nama Nasabah    : {$this->penarikan->nama_nasabah}\n"
            ."Waktu Pengajuan : {$dikirimAt}\n\n"
            .'Silakan periksa dashboard admin untuk memverifikasi penarikan.';

        $adminNumbers = array_filter(array_map('trim', explode(',', config('whatsapp_gateway.admin_wa', ''))));

        foreach ($adminNumbers as $adminNumber) {
            $whatsappNumber = preg_replace('/@s\.whatsapp\.net$/', '', $adminNumber);
            $whatsappNumber = preg_replace('/[^0-9]/', '', $whatsappNumber);
            $whatsappNumber = preg_replace('/^(62|0)/', '', $whatsappNumber);
            $whatsappNumber = '62'.$whatsappNumber;

            try {
                $response = send_whatsapp_api($whatsappNumber, $pesan);

                if (! $response->successful()) {
                    Log::warning('Gagal mengirim notifikasi penarikan melalui WhatsApp.', [
                        'penarikan_id' => $this->penarikan->id,
                        'whatsapp' => $whatsappNumber,
                        'status_code' => $response->status(),
                        'response_body' => $response->body(),
                    ]);
                }
            } catch (\Throwable $e) {
                Log::error('Error sending penarikan WhatsApp notification: '.$e->getMessage(), [
                    'penarikan_id' => $this->penarikan->id,
                    'whatsapp' => $whatsappNumber,
                ]);
            }
        }
    }
}
