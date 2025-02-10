<?php

namespace App\Filament\Resources\BannerMobileResource\Pages;

use App\Filament\Resources\BannerMobileResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBannerMobile extends EditRecord
{
    protected static string $resource = BannerMobileResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
