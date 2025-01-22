<?php

namespace App\Filament\Resources\SettingKomisiResource\Pages;

use App\Filament\Resources\SettingKomisiResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSettingKomisis extends ListRecords
{
    protected static string $resource = SettingKomisiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
