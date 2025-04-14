<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CicilanEmasResource\Pages;
use App\Models\CicilanEmas;
use App\Filament\Resources\CicilanEmasResource\Pages\ViewCicilanEmas;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;

class CicilanEmasResource extends Resource
{
    protected static ?string $model = CicilanEmas::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = 'Cicilan Emas';


    protected static ?string $modelLabel = 'Cicilan Emas';
    protected static ?string $navigationGroup = 'Gadai & Kredit Elektronik';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([


                Select::make('pinjaman_id')
                    ->relationship('pinjaman', 'no_pinjaman')
                    ->required()
                    ->searchable(),

                TextInput::make('no_transaksi')
                    ->default(fn () => 'CMS' . str_pad(mt_rand(0, 999999), 6, '0', STR_PAD_LEFT))
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->disabled()
                    ->dehydrated(true)
                    ->dehydrateStateUsing(fn ($state) => $state ?: 'CMS' . str_pad(mt_rand(0, 999999), 6, '0', STR_PAD_LEFT)),

                Select::make('berat_emas')
                    ->options([
                        '0.5' => '0.5 gram',
                        '1' => '1 gram',
                        '2' => '2 gram',
                        '3' => '3 gram',
                        '5' => '5 gram',
                        '10' => '10 gram',
                        '25' => '25 gram',
                        '50' => '50 gram',
                        '100' => '100 gram',
                    ])
                    ->required()
                    ->searchable(),

                TextInput::make('total_harga')
                    ->required()
                    ->numeric()
                    ->prefix('Rp')
                    ->reactive()
                    ->afterStateUpdated(function ($set, $state) {
                        $set('setoran_awal', $state * 0.05);
                        $set('biaya_admin', $state * 0.005);
                    }),

                TextInput::make('setoran_awal')
                    ->required()
                    ->numeric()
                    ->prefix('Rp')
                    ->disabled()
                    ->dehydrated(true)
                    ->helperText('5% dari harga emas'),

                TextInput::make('biaya_admin')
                    ->required()
                    ->numeric()
                    ->prefix('Rp')
                    ->disabled()
                    ->dehydrated(true)
                    ->helperText('0.5% dari total harga'),



                Select::make('tenor')
                    ->options([
                        1 => '1 Bulan',
                        2 => '2 Bulan',
                        3 => '3 Bulan',
                        4 => '4 Bulan',
                        5 => '5 Bulan',
                        6 => '6 Bulan',
                        7 => '7 Bulan',
                        8 => '8 Bulan',
                        9 => '9 Bulan',
                        10 => '10 Bulan',
                        11 => '11 Bulan',
                        12 => '12 Bulan',
                        24 => '24 Bulan',
                        36 => '36 Bulan',
                        48 => '48 Bulan',
                        60 => '60 Bulan',
                    ])
                    ->required(),



                Select::make('status')
                    ->options([
                        'aktif' => 'Aktif',
                        'lunas' => 'Lunas',
                        'gagal_bayar' => 'Gagal Bayar'
                    ])
                    ->default('aktif')
                    ->required(),

                Forms\Components\Textarea::make('keterangan')
                    ->rows(3)
                    ->maxLength(65535),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('no_transaksi')
                    ->searchable()
                    ->sortable(),


                TextColumn::make('pinjaman.no_pinjaman')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('berat_emas')
                    ->suffix(' gram')
                    ->numeric(
                        decimalPlaces: 2,
                        decimalSeparator: ',',
                        thousandsSeparator: '.'
                    )
                    ->sortable(),



                TextColumn::make('total_harga')
                    ->money('IDR')
                    ->sortable(),



                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'aktif' => 'success',
                        'lunas' => 'info',
                        'gagal_bayar' => 'danger',
                    })
                    ->sortable(),

            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'aktif' => 'Aktif',
                        'lunas' => 'Lunas',
                        'gagal_bayar' => 'Gagal Bayar'
                    ]),

            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ListCicilanEmas::route('/'),
            'create' => Pages\CreateCicilanEmas::route('/create'),
            'edit' => Pages\EditCicilanEmas::route('/{record}/edit'),
            'view' => Pages\ViewCicilanEmas::route('/{record}'),
        ];
    }
}
