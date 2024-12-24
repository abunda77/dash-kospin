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
use Filament\Support\RawJs;
use Filament\Forms\Components\Select;

class TransaksiTabunganResource extends Resource
{
    protected static ?string $model = TransaksiTabungan::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrows-right-left';

    protected static ?string $navigationLabel = 'Transaksi Tabungan';
    public static function getNavigationGroup(): ?string
            {
                return 'Tabungan';
            }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('id_tabungan')
    ->label('No Rekening')
    ->options(fn () => Tabungan::pluck('no_tabungan', 'id'))
    ->searchable()
    ->required(),
                Forms\Components\Select::make('jenis_transaksi')
                    ->label('Jenis Transaksi')
                    ->options([
                        'kredit' => 'Kredit',
                        'debit' => 'Debit'
                    ])
                    ->required(),
                Forms\Components\TextInput::make('jumlah')
                    ->label('Jumlah')
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
                Forms\Components\DateTimePicker::make('tanggal_transaksi')
                    ->label('Tanggal Transaksi')
                    ->required(),
                Forms\Components\TextInput::make('keterangan')
                    ->label('Keterangan')
                    ->maxLength(255),
                Forms\Components\Select::make('kode_transaksi')
                    ->label('Kode Transaksi')
                    ->options([
                        '0' => '000 DB Bunga Deposito',
                        '1' => '001 Penyetoran',
                        '2' => '002 Penarikan di Teller',
                        '3' => '003 Pengambilan di ATM',
                        '4' => '004 Pemindahbukuan (DK), Biaya Adm',
                        '5' => '005 Setoran/Tolakan Kliring',
                        '6' => '006 Bunga',
                        'K' => '00K Koreksi',
                        'S' => '00S Saldo Penutupan',
                        'P' => '00P Pajak',
                        'G' => '00G Gabungan Transaksi'
                    ])
                    ->required(),

                    Forms\Components\Fieldset::make('Kode Teller')
                        ->schema([
                            Forms\Components\TextInput::make('kode_teller')
                                ->label('Kode Teller')
                                ->default(auth('admin')->user()->id)
                                ->disabled()
                                ->dehydrated(fn ($state) => filled($state))
                                ->required(),
                        ]),
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
                    ->badge()
                    ->searchable()
                    ->sortable()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'kredit' => 'Kredit',
                        'debit' => 'Debit',
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'kredit' => 'success',
                        'debit' => 'danger',
                    }),
                Tables\Columns\TextColumn::make('jumlah')
                    ->label('Nominal')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('keterangan')
                    ->label('Keterangan')
                    ->searchable()
                    ->limit(50),

                Tables\Columns\TextColumn::make('kode_teller')
                    ->label('Kode Teller')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('tanggal_transaksi')
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
