<?php

namespace App\Filament\Resources\BarcodeScanLogResource\Pages;

use App\Filament\Resources\BarcodeScanLogResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBarcodeScanLogs extends ListRecords
{
    protected static string $resource = BarcodeScanLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // No create action - logs are created automatically
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            BarcodeScanLogResource\Widgets\ScanStatsWidget::class,
        ];
    }
}
