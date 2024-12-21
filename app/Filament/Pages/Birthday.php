<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Tables\Contracts\HasTable;
use App\Models\Profile;
use Carbon\Carbon;
use Filament\Navigation\NavigationItem;

class Birthday extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'Ulang Tahun';
    protected static string $view = 'filament.pages.birthday';

    public static function getNavigationGroup(): ?string
    {
        return 'Promotion';
    }

    public static function getNavigationItems(): array
    {
        $today = Carbon::now()->timezone('Asia/Jakarta');

        $birthdayCount = Profile::query()
            ->whereMonth('birthday', $today->month)
            ->whereDay('birthday', $today->day)
            ->count();

        return [
            NavigationItem::make(static::getNavigationLabel())
                ->icon(static::getNavigationIcon())
                ->group(static::getNavigationGroup())
                ->badge($birthdayCount)
                ->sort(static::getNavigationSort())
                ->url(static::getNavigationUrl()),
        ];
    }
}
