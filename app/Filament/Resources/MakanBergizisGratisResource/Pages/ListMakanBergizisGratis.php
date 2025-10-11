<?php

namespace App\Filament\Resources\MakanBergizisGratisResource\Pages;

use App\Filament\Resources\MakanBergizisGratisResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMakanBergizisGratis extends ListRecords
{
    protected static string $resource = MakanBergizisGratisResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Removed CreateAction - data should only be created via API
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            MakanBergizisGratisResource\Widgets\MakanBergizisGratisStatsWidget::class,
        ];
    }
}
