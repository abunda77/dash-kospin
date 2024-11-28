<?php

namespace App\Filament\Resources\ProdukPinjamanResource\Pages;

use App\Filament\Resources\ProdukPinjamanResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProdukPinjaman extends EditRecord
{
    protected static string $resource = ProdukPinjamanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
