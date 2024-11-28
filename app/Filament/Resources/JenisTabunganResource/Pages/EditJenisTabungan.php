<?php

namespace App\Filament\Resources\JenisTabunganResource\Pages;

use App\Filament\Resources\JenisTabunganResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditJenisTabungan extends EditRecord
{
    protected static string $resource = JenisTabunganResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
