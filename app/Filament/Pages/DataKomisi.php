<?php

namespace App\Filament\Pages;

use App\Models\AnggotaReferral;
use App\Models\TransaksiReferral;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Database\Eloquent\Builder;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;

class DataKomisi extends Page implements HasTable
{
    use InteractsWithTable, HasPageShield;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static string $view = 'filament.pages.data-komisi';
    protected static ?string $navigationGroup = 'Referral';
    protected static ?string $navigationLabel = 'Data Komisi';
    protected static ?string $pluralModelLabel = 'Data Komisi';
    protected static ?string $pluralLabel = 'Data Komisi';
    protected static ?string $modelLabel = 'Data Komisi';

    public function getTableQuery(): Builder
    {
        return AnggotaReferral::query()
            ->withSum(['transaksiReferral as total_komisi' => function ($query) {
                $query->where('status_komisi', 'approved');
            }], 'nilai_komisi')
            ->withSum(['transaksiReferral as total_withdrawal' => function ($query) {
                $query->where('status_komisi', 'approved');
            }], 'nilai_withdrawal');
    }

    protected function getTableColumns(): array
    {
        return [
            TextColumn::make('nama')
                ->label('Nama Anggota Referral')
                ->searchable()
                ->sortable(),

            TextColumn::make('total_komisi')
                ->label('Total Komisi')
                ->money('IDR')
                ->sortable(),

            TextColumn::make('total_withdrawal')
                ->label('Total Withdrawal')
                ->money('IDR')
                ->sortable(),

            TextColumn::make('sisa_komisi')
                ->label('Sisa Komisi')
                ->money('IDR')
                ->state(function ($record): float {
                    return ($record->total_komisi ?? 0) - ($record->total_withdrawal ?? 0);
                })
                ->sortable(),
        ];
    }
}
