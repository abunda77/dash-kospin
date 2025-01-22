<?php

namespace App\Filament\Resources\TransaksiReferralResource\Pages;

use App\Filament\Resources\TransaksiReferralResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTransaksiReferral extends EditRecord
{
    protected static string $resource = TransaksiReferralResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
