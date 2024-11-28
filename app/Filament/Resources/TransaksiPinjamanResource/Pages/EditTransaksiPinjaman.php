<?php

namespace App\Filament\Resources\TransaksiPinjamanResource\Pages;

use App\Filament\Resources\TransaksiPinjamanResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTransaksiPinjaman extends EditRecord
{
    protected static string $resource = TransaksiPinjamanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
