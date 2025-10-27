<?php

namespace App\Filament\Resources\QrisStaticResource\Pages;

use App\Filament\Resources\QrisStaticResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditQrisStatic extends EditRecord
{
    protected static string $resource = QrisStaticResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
