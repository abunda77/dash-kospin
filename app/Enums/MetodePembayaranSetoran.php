<?php

namespace App\Enums;

enum MetodePembayaranSetoran: string
{
    case Qris = 'qris';
    case TransferRekening = 'transfer_rekening';

    public function label(): string
    {
        return match ($this) {
            self::Qris => 'QRIS',
            self::TransferRekening => 'Transfer Rekening',
        };
    }
}
