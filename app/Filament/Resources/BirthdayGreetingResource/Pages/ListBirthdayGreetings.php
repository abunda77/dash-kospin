<?php

namespace App\Filament\Resources\BirthdayGreetingResource\Pages;

use App\Filament\Resources\BirthdayGreetingResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBirthdayGreetings extends ListRecords
{
    protected static string $resource = BirthdayGreetingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
