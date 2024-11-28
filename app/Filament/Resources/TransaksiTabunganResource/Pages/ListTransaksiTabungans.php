<?php

namespace App\Filament\Resources\TransaksiTabunganResource\Pages;

use App\Filament\Resources\TransaksiTabunganResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTransaksiTabungans extends ListRecords
{
    protected static string $resource = TransaksiTabunganResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
