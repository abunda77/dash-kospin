<?php

namespace App\Events;

use App\Models\Deposito;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DepositoCreated
{
    use Dispatchable, SerializesModels;

    public $deposito;

    public function __construct(Deposito $deposito)
    {
        $this->deposito = $deposito;
    }
}
