<?php

namespace App\Filament\Resources\MakanBergizisGratisResource\Pages;

use App\Filament\Resources\MakanBergizisGratisResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMakanBergizisGratis extends EditRecord
{
    protected static string $resource = MakanBergizisGratisResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
