<?php

namespace App\Filament\Resources\BannerMobileResource\Pages;

use App\Filament\Resources\BannerMobileResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBannerMobiles extends ListRecords
{
    protected static string $resource = BannerMobileResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
