<?php

namespace App\Filament\Resources\JaminanResource\Pages;

use App\Filament\Resources\JaminanResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditJaminan extends EditRecord
{
    protected static string $resource = JaminanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
