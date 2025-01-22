<?php

namespace App\Filament\Resources\SettingKomisiResource\Pages;

use App\Filament\Resources\SettingKomisiResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSettingKomisi extends EditRecord
{
    protected static string $resource = SettingKomisiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
