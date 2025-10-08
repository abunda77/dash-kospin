<?php

namespace App\Filament\Resources\BarcodeScanLogResource\Pages;

use App\Filament\Resources\BarcodeScanLogResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBarcodeScanLog extends EditRecord
{
    protected static string $resource = BarcodeScanLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
