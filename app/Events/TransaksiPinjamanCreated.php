<?php

namespace App\Events;

use App\Models\TransaksiPinjaman;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TransaksiPinjamanCreated
{
    use Dispatchable, SerializesModels;

    public $transaksi;

    public function __construct(TransaksiPinjaman $transaksi)
    {
        $this->transaksi = $transaksi;
    }
}
