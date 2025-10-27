<?php

namespace App\Filament\Resources\QrisStaticResource\Pages;

use App\Filament\Resources\QrisStaticResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListQrisStatics extends ListRecords
{
    protected static string $resource = QrisStaticResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
