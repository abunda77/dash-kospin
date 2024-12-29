<?php

namespace App\Events;

use App\Models\TransaksiPinjaman;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;


class TransaksiPinjamanUpdated
{
    use Dispatchable,  SerializesModels;

    public $transaksi;

    public function __construct(TransaksiPinjaman $transaksi)
    {
        $this->transaksi = $transaksi;
    }
}
