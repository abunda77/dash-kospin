<?php

namespace App\Filament\Resources\GadaiResource\Pages;

use App\Filament\Resources\GadaiResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListGadais extends ListRecords
{
    protected static string $resource = GadaiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
