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

class BiayaBungaPinjamanResource extends Resource
{
    protected static ?string $model = BiayaBungaPinjaman::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Pinjaman';

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
            'index' => Pages\ListBiayaBungaPinjamen::route('/'),
            'create' => Pages\CreateBiayaBungaPinjaman::route('/create'),
            'edit' => Pages\EditBiayaBungaPinjaman::route('/{record}/edit'),
        ];
    }
}
