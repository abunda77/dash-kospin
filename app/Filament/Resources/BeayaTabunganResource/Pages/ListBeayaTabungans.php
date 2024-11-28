<?php

namespace App\Filament\Resources\BeayaTabunganResource\Pages;

use App\Filament\Resources\BeayaTabunganResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBeayaTabungans extends ListRecords
{
    protected static string $resource = BeayaTabunganResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
