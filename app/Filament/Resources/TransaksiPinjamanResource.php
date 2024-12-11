<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TransaksiPinjamanResource\Pages;
use App\Filament\Resources\TransaksiPinjamanResource\RelationManagers;
use App\Models\TransaksiPinjaman;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TransaksiPinjamanResource extends Resource
{
    protected static ?string $model = TransaksiPinjaman::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    public static function getNavigationGroup(): ?string
            {
                return 'Pinjaman';
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
                //
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
            'index' => Pages\ListTransaksiPinjamen::route('/'),
            'create' => Pages\CreateTransaksiPinjaman::route('/create'),
            'edit' => Pages\EditTransaksiPinjaman::route('/{record}/edit'),
        ];
    }
}
