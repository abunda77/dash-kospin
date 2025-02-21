<?php

namespace App\Filament\Resources\GadaiResource\Pages;

use App\Filament\Resources\GadaiResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditGadai extends EditRecord
{
    protected static string $resource = GadaiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
