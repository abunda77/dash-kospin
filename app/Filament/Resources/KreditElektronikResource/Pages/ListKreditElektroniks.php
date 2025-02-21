<?php

namespace App\Filament\Resources\KreditElektronikResource\Pages;

use App\Filament\Resources\KreditElektronikResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListKreditElektroniks extends ListRecords
{
    protected static string $resource = KreditElektronikResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
