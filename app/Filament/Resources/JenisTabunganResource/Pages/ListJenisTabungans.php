<?php

namespace App\Filament\Resources\JenisTabunganResource\Pages;

use App\Filament\Resources\JenisTabunganResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListJenisTabungans extends ListRecords
{
    protected static string $resource = JenisTabunganResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
