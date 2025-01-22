<?php

namespace App\Filament\Resources\CatatanKreditResource\Pages;

use App\Filament\Resources\CatatanKreditResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCatatanKredit extends EditRecord
{
    protected static string $resource = CatatanKreditResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
