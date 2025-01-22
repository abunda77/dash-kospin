<?php

namespace App\Filament\Resources\TransaksiReferralResource\Pages;

use App\Filament\Resources\TransaksiReferralResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTransaksiReferrals extends ListRecords
{
    protected static string $resource = TransaksiReferralResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
