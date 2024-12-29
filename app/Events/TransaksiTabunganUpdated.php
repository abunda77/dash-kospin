<?php

namespace App\Events;

use App\Models\TransaksiTabungan;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TransaksiTabunganUpdated
{
    use Dispatchable, SerializesModels;


    public $transaksi;

    public function __construct(TransaksiTabungan $transaksi)
    {
        $this->transaksi = $transaksi;
    }


}
