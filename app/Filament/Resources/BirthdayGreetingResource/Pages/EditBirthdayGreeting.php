<?php

namespace App\Filament\Resources\BirthdayGreetingResource\Pages;

use App\Filament\Resources\BirthdayGreetingResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBirthdayGreeting extends EditRecord
{
    protected static string $resource = BirthdayGreetingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
