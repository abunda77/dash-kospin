<?php

namespace App\Filament\Resources\JaminanResource\Pages;

use App\Filament\Resources\JaminanResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListJaminans extends ListRecords
{
    protected static string $resource = JaminanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
