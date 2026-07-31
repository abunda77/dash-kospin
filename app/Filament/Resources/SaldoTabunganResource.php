<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SaldoTabunganResource\Pages;
use App\Models\Tabungan;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SaldoTabunganResource extends Resource
{
    protected static ?string $model = Tabungan::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $title = 'Saldo Tabungan';

    protected static ?string $navigationLabel = 'Saldo Tabungan';

    public static function getNavigationGroup(): ?string
    {
        return 'Tabungan';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['profile', 'produkTabungan']))
            ->columns([
                Tables\Columns\TextColumn::make('profile.first_name')
                    ->label('Nama Lengkap')
                    ->formatStateUsing(fn ($record) => $record->profile
                        ? "{$record->profile->first_name} {$record->profile->last_name}"
                        : '-')
                    ->searchable(['profile.first_name', 'profile.last_name'])
                    ->sortable(),
                Tables\Columns\TextColumn::make('profile.first_name')
                    ->label('Nama Depan')
                    ->searchable()
                    ->hidden(false),
                Tables\Columns\TextColumn::make('profile.last_name')
                    ->label('Nama Belakang')
                    ->searchable()
                    ->hidden(false),
                Tables\Columns\TextColumn::make('no_tabungan')
                    ->label('No Rekening')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('saldo_akhir')
                    ->label('Saldo')
                    ->formatStateUsing(fn ($state) => 'Rp '.number_format($state, 2, ',', '.'))
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query->orderByRaw(
                            '(saldo + COALESCE((SELECT SUM(jumlah) FROM transaksi_tabungans WHERE id_tabungan = tabungans.id AND jenis_transaksi = ?), 0) - COALESCE((SELECT SUM(jumlah) FROM transaksi_tabungans WHERE id_tabungan = tabungans.id AND jenis_transaksi = ?), 0)) '.$direction,
                            ['debit', 'kredit']
                        );
                    }),
            ])
            ->filters([
                //
            ])
            ->actions([
                //
            ])
            ->bulkActions([
                //
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSaldoTabungans::route('/'),
        ];
    }
}
