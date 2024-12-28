<?php

namespace App\Listeners;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use App\Events\TransaksiTabunganCreated;

class SendTransaksiTabunganWebhook
{
    public function handle(TransaksiTabunganCreated $event): void
    {
        $transaksi = $event->transaksi;

        $data = [
            'id' => $transaksi->id,
            'id_tabungan' => $transaksi->id_tabungan,
            'jenis_transaksi' => $transaksi->jenis_transaksi,
            'jumlah' => $transaksi->jumlah,
            'tanggal_transaksi' => $transaksi->tanggal_transaksi,
            'keterangan' => $transaksi->keterangan,
            'kode_transaksi' => $transaksi->kode_transaksi,
            'kode_teller' => $transaksi->kode_teller,
            'created_at' => $transaksi->created_at,
            'updated_at' => $transaksi->updated_at
        ];

        try {
            Http::post(env('WEBHOOK_URL'), $data);
        } catch (\Exception $e) {
            Log::error('Webhook error: ' . $e->getMessage());
        }
    }
}
