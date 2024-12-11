<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DendaResource\Pages;
use App\Filament\Resources\DendaResource\RelationManagers;
use App\Models\Denda;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class DendaResource extends Resource
{
    protected static ?string $model = Denda::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    public static function getNavigationGroup(): ?string
            {
                return 'Pinjaman';
            }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('penalty_code')
                    ->label('Kode Denda')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('rate_denda')
                    ->label('Rate Denda')
                    ->required()
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(100)
                    ->suffix('%'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('penalty_code')
                    ->label('Kode Denda')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('rate_denda')
                    ->label('Rate Denda')
                    ->suffix('%')
                    ->numeric(
                        decimalPlaces: 2,
                    )
                    ->sortable()
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
            'index' => Pages\ListDendas::route('/'),
            'create' => Pages\CreateDenda::route('/create'),
            'edit' => Pages\EditDenda::route('/{record}/edit'),
        ];
    }
}
