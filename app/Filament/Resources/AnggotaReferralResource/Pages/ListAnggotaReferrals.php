<?php

namespace App\Filament\Resources\AnggotaReferralResource\Pages;

use App\Filament\Resources\AnggotaReferralResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAnggotaReferrals extends ListRecords
{
    protected static string $resource = AnggotaReferralResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
