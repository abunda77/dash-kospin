<?php

namespace App\Filament\Resources\BirthdayGreetingResource\Pages;

use App\Filament\Resources\BirthdayGreetingResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateBirthdayGreeting extends CreateRecord
{
    protected static string $resource = BirthdayGreetingResource::class;
}
