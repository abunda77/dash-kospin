<?php

namespace App\Filament\Resources\KreditElektronikResource\Pages;

use App\Filament\Resources\KreditElektronikResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditKreditElektronik extends EditRecord
{
    protected static string $resource = KreditElektronikResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
