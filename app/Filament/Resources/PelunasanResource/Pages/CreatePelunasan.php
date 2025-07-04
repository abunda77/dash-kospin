<?php

namespace App\Filament\Resources\PelunasanResource\Pages;

use App\Filament\Resources\PelunasanResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreatePelunasan extends CreateRecord
{
    protected static string $resource = PelunasanResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->record]);
    }
}
