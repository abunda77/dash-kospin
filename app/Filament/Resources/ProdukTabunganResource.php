<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProdukTabunganResource\Pages;
use App\Filament\Resources\ProdukTabunganResource\RelationManagers;
use App\Models\ProdukTabungan;
use App\Models\JenisTabungan;
use App\Models\BeayaTabungan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProdukTabunganResource extends Resource
{
    protected static ?string $model = ProdukTabungan::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = 'Produk Tabungan';
    protected static ?string $navigationGroup = 'Tabungan';

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
                Forms\Components\Select::make('jenis_tabungan_id')
                    ->label('Jenis Tabungan')
                    ->relationship('jenisTabungan', 'name')
                    ->required(),
                Forms\Components\Select::make('bunga_beaya_id')
                    ->label('Bunga & Beaya')
                    ->relationship('beayaTabungan', 'name')
                    ->required(),
                Forms\Components\Textarea::make('keterangan')
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
                Tables\Columns\TextColumn::make('jenisTabungan.name')
                    ->label('Jenis Tabungan')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('BeayaTabungan.name')
                    ->label('Bunga & Beaya')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('keterangan')
                    ->label('Keterangan')
                    ->limit(50)
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Diperbarui')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
            'index' => Pages\ListProdukTabungans::route('/'),
            'create' => Pages\CreateProdukTabungan::route('/create'),
            'edit' => Pages\EditProdukTabungan::route('/{record}/edit'),
        ];
    }
}
