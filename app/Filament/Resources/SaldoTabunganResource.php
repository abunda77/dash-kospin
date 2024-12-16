<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SaldoTabunganResource\Pages;
use App\Filament\Resources\SaldoTabunganResource\RelationManagers;
use App\Models\SaldoTabungan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SaldoTabunganResource extends Resource
{
    protected static ?string $model = SaldoTabungan::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

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
            ->columns([
                Tables\Columns\TextColumn::make('tabungan.profile.full_name')
                    ->label('Nama Lengkap')
                    ->searchable(false)
                    ->getStateUsing(fn ($record) => "{$record->tabungan->profile->first_name} {$record->tabungan->profile->last_name}")
                    ->sortable(),
                Tables\Columns\TextColumn::make('tabungan.profile.first_name')
                    ->label('Nama Depan')
                    ->searchable()
                    ->hidden(false),
                Tables\Columns\TextColumn::make('tabungan.profile.last_name')
                    ->label('Nama Belakang')
                    ->searchable()
                    ->hidden(false),
                Tables\Columns\TextColumn::make('tabungan.no_tabungan')
                    ->label('No Rekening')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('saldo_akhir')
                    ->label('Saldo')
                    ->formatStateUsing(fn ($state) => 'Rp ' . number_format($state, 2, ',', '.'))
                    ->sortable(),
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
