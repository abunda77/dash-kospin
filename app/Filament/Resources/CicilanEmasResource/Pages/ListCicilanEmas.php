<?php

namespace App\Filament\Resources\CicilanEmasResource\Pages;

use App\Filament\Resources\CicilanEmasResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCicilanEmas extends ListRecords
{
    protected static string $resource = CicilanEmasResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
