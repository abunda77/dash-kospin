<?php

namespace App\Filament\Resources\MakanBergizisGratisResource\Pages;

use App\Filament\Resources\MakanBergizisGratisResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewMakanBergizisGratis extends ViewRecord
{
    protected static string $resource = MakanBergizisGratisResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
