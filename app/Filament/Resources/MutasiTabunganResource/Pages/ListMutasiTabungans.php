<?php

namespace App\Filament\Resources\MutasiTabunganResource\Pages;

use App\Filament\Resources\MutasiTabunganResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMutasiTabungans extends ListRecords
{
    protected static string $resource = MutasiTabunganResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
