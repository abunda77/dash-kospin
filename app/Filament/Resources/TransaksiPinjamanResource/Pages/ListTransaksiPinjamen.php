<?php

namespace App\Filament\Resources\TransaksiPinjamanResource\Pages;

use App\Filament\Resources\TransaksiPinjamanResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTransaksiPinjamen extends ListRecords
{
    protected static string $resource = TransaksiPinjamanResource::class;
    protected static ?string $title = 'History Pembayaran Kredit';
    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
