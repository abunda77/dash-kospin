<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BiayaBungaPinjamanResource\Pages;
use App\Filament\Resources\BiayaBungaPinjamanResource\RelationManagers;
use App\Models\BiayaBungaPinjaman;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Support\RawJs;

class BiayaBungaPinjamanResource extends Resource
{
    protected static ?string $model = BiayaBungaPinjaman::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationLabel = 'Biaya Bunga Kredit';
    protected static ?string $title = 'Biaya Bunga Kredit';
    public static function getNavigationGroup(): ?string
            {
                return 'Pinjaman';
            }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nama')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('persentase_bunga')
                    ->label('Persentase Bunga Per Tahun p.a')
                    ->required()
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(100)
                    ->suffix('%'),
                Forms\Components\TextInput::make('biaya_administrasi')
                    ->label('Biaya Administrasi')
                    ->mask(RawJs::make('$money($input)'))
                    ->stripCharacters(',')
                    ->numeric()
                    ->prefix('Rp')
                    ->placeholder('1,000,000')
                    ->afterStateHydrated(function (Forms\Components\TextInput $component, $state) {
                        if ($state) {
                            $component->state(number_format($state, 0, '.', ','));
                        }
                    })
                    ->dehydrateStateUsing(fn ($state) => (int) str_replace(',', '', $state))
                    ->required(),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama')
                    ->searchable(),
                Tables\Columns\TextColumn::make('persentase_bunga')
                    ->label('Persentase Bunga')
                    ->suffix('%')
                    ->numeric(
                        decimalPlaces: 2,
                    ),
                Tables\Columns\TextColumn::make('biaya_administrasi')
                    ->label('Biaya Administrasi')
                    ->money('idr'),

            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
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
            'index' => Pages\ListBiayaBungaPinjamen::route('/'),
            'create' => Pages\CreateBiayaBungaPinjaman::route('/create'),
            'edit' => Pages\EditBiayaBungaPinjaman::route('/{record}/edit'),
        ];
    }
}
