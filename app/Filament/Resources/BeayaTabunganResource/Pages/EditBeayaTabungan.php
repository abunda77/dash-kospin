<?php

namespace App\Filament\Resources\BeayaTabunganResource\Pages;

use App\Filament\Resources\BeayaTabunganResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBeayaTabungan extends EditRecord
{
    protected static string $resource = BeayaTabunganResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
