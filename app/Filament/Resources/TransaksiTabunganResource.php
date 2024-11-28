<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TransaksiTabunganResource\Pages;
use App\Filament\Resources\TransaksiTabunganResource\RelationManagers;
use App\Models\TransaksiTabungan;
use App\Models\Tabungan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TransaksiTabunganResource extends Resource
{
    protected static ?string $model = TransaksiTabungan::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrows-right-left';

    protected static ?string $navigationLabel = 'Transaksi Tabungan';
    protected static ?string $navigationGroup = 'Tabungan';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('id_tabungan')
                    ->label('No Rekening')
                    ->relationship('tabungan', 'no_tabungan')
                    ->required(),
                Forms\Components\Select::make('jenis_transaksi')
                    ->label('Jenis Transaksi')
                    ->options([
                        'kredit' => 'Kredit',
                        'debit' => 'Debit'
                    ])
                    ->required(),
                Forms\Components\TextInput::make('nominal')
                    ->label('Nominal')
                    ->required()
                    ->numeric()
                    ->prefix('Rp'),
                Forms\Components\Textarea::make('keterangan')
                    ->label('Keterangan')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('tabungan.no_tabungan')
                    ->label('No Rekening')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('jenis_transaksi')
                    ->label('Jenis Transaksi')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('nominal')
                    ->label('Nominal')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('keterangan')
                    ->label('Keterangan')
                    ->searchable()
                    ->limit(50),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
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
            'index' => Pages\ListTransaksiTabungans::route('/'),
            'create' => Pages\CreateTransaksiTabungan::route('/create'),
            'edit' => Pages\EditTransaksiTabungan::route('/{record}/edit'),
        ];
    }
}
