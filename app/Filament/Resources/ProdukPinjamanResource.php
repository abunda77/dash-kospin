<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProdukPinjamanResource\Pages;
use App\Filament\Resources\ProdukPinjamanResource\RelationManagers;
use App\Models\ProdukPinjaman;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProdukPinjamanResource extends Resource
{
    protected static ?string $model = ProdukPinjaman::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    public static function getNavigationGroup(): ?string
            {
                return 'Pinjaman';
            }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('kode_produk')
                    ->label('Kode Produk')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('nama_produk')
                    ->label('Nama Produk')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('beaya_bunga_id')
                    ->label('Biaya & Bunga')
                    ->relationship('biayaBunga', 'name')
                    ->searchable()
                    ->required(),
                Forms\Components\Select::make('denda_id')
                    ->label('Denda')
                    ->relationship('denda', 'penalty_code')
                    ->searchable()
                    ->required(),
                Forms\Components\TextInput::make('keterangan')
                    ->label('Keterangan')
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('kode_produk')
                    ->label('Kode Produk')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('nama_produk')
                    ->label('Nama Produk')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('biayaBunga.name')
                    ->label('Biaya & Bunga')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('denda.penalty_code')
                    ->label('Denda')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('keterangan')
                    ->label('Keterangan')
                    ->searchable()
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
            'index' => Pages\ListProdukPinjamen::route('/'),
            'create' => Pages\CreateProdukPinjaman::route('/create'),
            'edit' => Pages\EditProdukPinjaman::route('/{record}/edit'),
        ];
    }
}
