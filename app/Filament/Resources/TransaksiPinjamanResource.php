<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Pinjaman;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Support\RawJs;
use Filament\Resources\Resource;
use App\Models\TransaksiPinjaman;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\TransaksiPinjamanResource\Pages;
use App\Filament\Resources\TransaksiPinjamanResource\RelationManagers;

class TransaksiPinjamanResource extends Resource
{
    protected static ?string $model = TransaksiPinjaman::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationLabel = 'Transaksi Kredit';
    protected static ?string $title = 'Transaksi Kredit';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getNavigationGroup(): ?string
            {
                return 'Pinjaman';
            }

    public static function form(Form $form): Form
    {
        return $form
        ->schema([
            Forms\Components\DatePicker::make('tanggal_pembayaran')
                ->label('Tanggal Pembayaran')
                ->required(),
            Forms\Components\Select::make('pinjaman_id')
                ->label('Nomor Pinjaman')
                ->options(fn () => Pinjaman::pluck('no_pinjaman', 'id_pinjaman'))
                ->searchable()
                ->required(),
            Forms\Components\TextInput::make('angsuran_pokok')
                ->label('Angsuran Pokok')
                ->numeric()
                ->prefix('Rp')
                ->required(),
            Forms\Components\TextInput::make('angsuran_bunga')
                ->label('Angsuran Bunga')
                ->numeric()
                ->prefix('Rp')
                ->required(),
            Forms\Components\TextInput::make('denda')
                ->label('Denda')
                ->numeric()
                ->prefix('Rp')
                ->required(),
            Forms\Components\TextInput::make('total_pembayaran')
                ->label('Total Pembayaran')
                ->numeric()
                ->prefix('Rp')
                ->required(),
            Forms\Components\TextInput::make('sisa_pinjaman')
                ->label('Sisa Pinjaman')
                ->numeric()
                ->prefix('Rp')
                ->required(),
            Forms\Components\TextInput::make('status_pembayaran')
                ->label('Status')
                ->required(),
            Forms\Components\TextInput::make('angsuran_ke')
                ->label('Angsuran Ke')
                ->numeric()
                ->required(),
            Forms\Components\TextInput::make('hari_terlambat')
                ->label('Hari Terlambat')
                ->numeric()
                ->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('tanggal_pembayaran')
                    ->label('Tanggal Pembayaran')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('pinjaman.no_pinjaman')
                    ->label('Nomor Pinjaman')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('angsuran_pokok')
                    ->label('Angsuran Pokok')
                    ->money('idr')
                    ->sortable(),
                Tables\Columns\TextColumn::make('angsuran_bunga')
                    ->label('Angsuran Bunga')
                    ->money('idr')
                    ->sortable(),
                Tables\Columns\TextColumn::make('denda')
                    ->label('Denda')
                    ->money('idr')
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_pembayaran')
                    ->label('Total Pembayaran')
                    ->money('idr')
                    ->sortable(),
                Tables\Columns\TextColumn::make('sisa_pinjaman')
                    ->label('Sisa Pinjaman')
                    ->money('idr')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status_pembayaran')
                    ->label('Status')
                    ->sortable(),
                Tables\Columns\TextColumn::make('angsuran_ke')
                    ->label('Angsuran Ke')
                    ->sortable(),
                Tables\Columns\TextColumn::make('hari_terlambat')
                    ->label('Hari Terlambat')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
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
