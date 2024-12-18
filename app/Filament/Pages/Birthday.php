<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Tables\Contracts\HasTable;

class Birthday extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'Ulang Tahun';
    protected static string $view = 'filament.pages.birthday';

    public static function getNavigationGroup(): ?string
    {
        return 'Data Nasabah';
    }
}
