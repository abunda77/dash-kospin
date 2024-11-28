<?php

namespace App\Filament\Resources\ProdukPinjamanResource\Pages;

use App\Filament\Resources\ProdukPinjamanResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListProdukPinjamen extends ListRecords
{
    protected static string $resource = ProdukPinjamanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
