<?php

namespace App\Filament\Resources\PelunasanResource\Pages;

use App\Filament\Resources\PelunasanResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPelunasans extends ListRecords
{
    protected static string $resource = PelunasanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
