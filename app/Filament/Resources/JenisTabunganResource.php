<?php

namespace App\Filament\Resources;

use App\Filament\Resources\JenisTabunganResource\Pages;
use App\Filament\Resources\JenisTabunganResource\RelationManagers;
use App\Models\JenisTabungan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class JenisTabunganResource extends Resource
{
    protected static ?string $model = JenisTabungan::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = 'Jenis Tabungan';
    protected static ?string $navigationGroup = 'Tabungan';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('name')
                    ->label('Jenis')
                    ->options([
                        'reguler' => 'Reguler',
                        'berjangka' => 'Berjangka',
                        'deposito' => 'Deposito'
                    ])
                    ->required()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Jenis')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Diperbarui')
                    ->dateTime()
                    ->sortable(),
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
            'index' => Pages\ListJenisTabungans::route('/'),
            'create' => Pages\CreateJenisTabungan::route('/create'),
            'edit' => Pages\EditJenisTabungan::route('/{record}/edit'),
        ];
    }
}
