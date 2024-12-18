<?php

namespace App\Filament\Resources\BiayaBungaPinjamanResource\Pages;

use App\Filament\Resources\BiayaBungaPinjamanResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBiayaBungaPinjamen extends ListRecords
{
    protected static string $resource = BiayaBungaPinjamanResource::class;
    protected static ?string $title = 'Biaya Bunga Kredit';
    protected static ?string $navigationLabel = 'Biaya Bunga Kredit';
    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
