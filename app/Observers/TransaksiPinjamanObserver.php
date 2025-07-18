<?php

namespace App\Observers;

use App\Models\TransaksiPinjaman;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TransaksiPinjamanObserver
{
    /**
     * Handle the TransaksiPinjaman "created" event.
     */
    public function created(TransaksiPinjaman $transaksiPinjaman): void
    {
        $this->sendWebhook($transaksiPinjaman, 'created');
    }

    /**
     * Send webhook notification
     */
    private function sendWebhook(TransaksiPinjaman $transaksiPinjaman, string $event): void
    {
        $webhookUrl = env('WEBHOOK_URL');
        
        if (empty($webhookUrl)) {
            Log::warning('WEBHOOK_URL not configured');
            return;
        }

        try {
            $payload = [
                'event' => $event,
                'model' => 'TransaksiPinjaman',
                'data' => [
                    'id' => $transaksiPinjaman->id,
                    'tanggal_pembayaran' => $transaksiPinjaman->tanggal_pembayaran,
                    'pinjaman_id' => $transaksiPinjaman->pinjaman_id,
                    'profile_id' => $transaksiPinjaman->pinjaman->profile_id ?? null,
                    'first_name' => $transaksiPinjaman->pinjaman->profile->first_name ?? null,
                    'last_name' => $transaksiPinjaman->pinjaman->profile->last_name ?? null,
                    'angsuran_pokok' => $transaksiPinjaman->angsuran_pokok,
                    'angsuran_bunga' => $transaksiPinjaman->angsuran_bunga,
                    'denda' => $transaksiPinjaman->denda,
                    'total_pembayaran' => $transaksiPinjaman->total_pembayaran,
                    'sisa_pinjaman' => $transaksiPinjaman->sisa_pinjaman,
                    'status_pembayaran' => $transaksiPinjaman->status_pembayaran,
                    'angsuran_ke' => $transaksiPinjaman->angsuran_ke,
                    'hari_terlambat' => $transaksiPinjaman->hari_terlambat,
                ],
                'timestamp' => now()->toISOString()
            ];

            Http::timeout(10)
                ->post($webhookUrl, $payload);

            Log::info('Webhook sent successfully for TransaksiPinjaman', [
                'id' => $transaksiPinjaman->id,
                'event' => $event
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to send webhook for TransaksiPinjaman', [
                'id' => $transaksiPinjaman->id,
                'event' => $event,
                'error' => $e->getMessage()
            ]);
        }
    }
}