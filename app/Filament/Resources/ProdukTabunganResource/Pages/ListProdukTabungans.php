<?php

namespace App\Filament\Resources\ProdukTabunganResource\Pages;

use App\Filament\Resources\ProdukTabunganResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListProdukTabungans extends ListRecords
{
    protected static string $resource = ProdukTabunganResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
