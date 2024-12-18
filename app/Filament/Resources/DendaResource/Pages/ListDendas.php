<?php

namespace App\Filament\Resources\DendaResource\Pages;

use App\Filament\Resources\DendaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDendas extends ListRecords
{
    protected static string $resource = DendaResource::class;
    protected static ?string $title = 'Denda Kredit';
    protected static ?string $navigationLabel = 'Denda Kredit';

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
