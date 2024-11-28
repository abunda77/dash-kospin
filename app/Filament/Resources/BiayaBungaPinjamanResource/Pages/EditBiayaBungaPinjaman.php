<?php

namespace App\Filament\Resources\BiayaBungaPinjamanResource\Pages;

use App\Filament\Resources\BiayaBungaPinjamanResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBiayaBungaPinjaman extends EditRecord
{
    protected static string $resource = BiayaBungaPinjamanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
