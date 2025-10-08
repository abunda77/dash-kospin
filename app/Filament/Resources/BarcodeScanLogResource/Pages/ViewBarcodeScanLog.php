<?php

namespace App\Filament\Resources\BarcodeScanLogResource\Pages;

use App\Filament\Resources\BarcodeScanLogResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewBarcodeScanLog extends ViewRecord
{
    protected static string $resource = BarcodeScanLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
