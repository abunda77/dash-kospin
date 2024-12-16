<?php

namespace App\Filament\Resources\SaldoTabunganResource\Pages;

use App\Filament\Resources\SaldoTabunganResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSaldoTabungan extends EditRecord
{
    protected static string $resource = SaldoTabunganResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
