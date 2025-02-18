<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Deposito;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Support\RawJs;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\DepositoResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\DepositoResource\RelationManagers;

class DepositoResource extends Resource
{
    protected static ?string $model = Deposito::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationLabel = 'Rekening Deposito';
    protected static ?string $title = 'Rekening Deposito';

    public static function getNavigationGroup(): ?string
            {
                return 'Deposito';
            }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('id_user')
                    ->relationship('profile', 'first_name')
                    ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->first_name} {$record->last_name}")
                    ->required()
                    ->searchable()
                    ->preload(),

                Forms\Components\TextInput::make('nomor_rekening')
                    ->label('No Rekening')
                    ->required()
                    ->maxLength(255)
                    ->default(function() {
                        do {
                            $number = '9999-' . str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
                        } while (Deposito::where('nomor_rekening', $number)->exists());
                        return $number;
                    })
                    ->disabled(false)
                    ->dehydrated(true), // Pastikan nilai field ini disimpan ke database

                Forms\Components\TextInput::make('nominal_penempatan')
                    ->label('Nominal Penempatan')
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
                    ->live()
                    ->afterStateUpdated(function ($state, callable $set, $get) {
                        $nominal = (int) str_replace(',', '', $state) ?: 0;
                        $rate = (float) $get('rate_bunga') ?: 0;
                        $jangka = (int) $get('jangka_waktu') ?: 0;

                        // Perbaikan perhitungan bunga
                        $bunga = ($nominal * $rate/100 * $jangka) / 12;
                        $set('nominal_bunga', round($bunga));
                    })
                    ->required(),

                Forms\Components\Select::make('jangka_waktu')
                    ->options([
                        1 => '1 Bulan',
                        3 => '3 Bulan',
                        6 => '6 Bulan',
                        12 => '12 Bulan',
                        24 => '24 Bulan',
                        36 => '36 Bulan',
                        48 => '48 Bulan',
                        60 => '60 Bulan'
                    ])
                    ->required()
                    ->live()
                    ->afterStateUpdated(function ($state, callable $set, $get) {
                        if ($get('tanggal_pembukaan')) {
                            $tanggalPembukaan = \Carbon\Carbon::parse($get('tanggal_pembukaan'));
                            $jangkaWaktu = is_numeric($state) ? (int) $state : 0;
                            $set('tanggal_jatuh_tempo', $tanggalPembukaan->copy()->addMonths($jangkaWaktu)->format('Y-m-d'));
                        }

                        // Perbaikan perhitungan bunga
                        $nominal = (float) str_replace([',', '.'], '', $get('nominal_penempatan')) ?: 0;
                        $rate = (float) $get('rate_bunga') ?: 0;
                        $jangka = (int) $state ?: 0;

                        $bunga = ($nominal * $rate/100 * $jangka) / 12;
                        $set('nominal_bunga', round($bunga));
                    }),

                Forms\Components\DatePicker::make('tanggal_pembukaan')
                    ->required()
                    ->native(false)
                    ->displayFormat('d/m/Y')
                    ->live()
                    ->afterStateUpdated(function ($state, callable $set, $get) {
                        if ($get('jangka_waktu')) {
                            $tanggalPembukaan = \Carbon\Carbon::parse($state);
                            $jangkaWaktu = is_numeric($get('jangka_waktu')) ? (int) $get('jangka_waktu') : 0;
                            $set('tanggal_jatuh_tempo', $tanggalPembukaan->copy()->addMonths($jangkaWaktu)->format('Y-m-d'));
                        }
                    }),

                Forms\Components\DatePicker::make('tanggal_jatuh_tempo')
                    ->required()
                    ->native(false)
                    ->displayFormat('d/m/Y')
                    ->disabled(),

                Forms\Components\Select::make('rate_bunga')
                    ->options([
                        '9.00' => '9.00%',
                        '9.25' => '9.25%',
                        '9.50' => '9.50%',
                        '9.75' => '9.75%',
                        '10.00' => '10.00%',
                        '10.25' => '10.25%',
                        '10.50' => '10.50%',
                        '10.75' => '10.75%',
                        '11.00' => '11.00%',
                        '11.25' => '11.25%',
                        '11.50' => '11.50%',
                        '11.75' => '11.75%',
                        '12.00' => '12.00%',
                        '12.25' => '12.25%',
                        '12.50' => '12.50%',
                        '12.75' => '12.75%',
                        '13.00' => '13.00%',
                        '13.25' => '13.25%',
                        '13.50' => '13.50%',
                        '13.75' => '13.75%',
                        '14.00' => '14.00%',
                        '14.25' => '14.25%',
                        '14.50' => '14.50%',
                        '14.75' => '14.75%',
                        '15.00' => '15.00%'
                    ])
                    ->required()
                    ->live()
                    ->afterStateUpdated(function ($state, callable $set, $get) {
                        $nominal = (float) str_replace([',', '.'], '', $get('nominal_penempatan')) ?: 0;
                        $rate = (float) $state ?: 0;
                        $jangka = (int) $get('jangka_waktu') ?: 0;

                        // Perbaikan perhitungan bunga
                        $bunga = ($nominal * $rate/100 * $jangka) / 12;
                        $set('nominal_bunga', round($bunga));
                    }),

                Forms\Components\TextInput::make('nominal_bunga')
                    ->label('Nominal Bunga')
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
                    ->disabled(),

                Forms\Components\Toggle::make('perpanjangan_otomatis')
                    ->default(false),

                Forms\Components\Select::make('status')
                    ->options([
                        'active' => 'Active',
                        'ended' => 'Ended'
                    ])
                    ->default('active')
                    ->required(),

                Forms\Components\Textarea::make('notes')
                    ->maxLength(65535)
                    ->columnSpanFull(),

                Forms\Components\Select::make('nama_bank')
                    ->label('Nama Bank')
                    ->options([
                        'bca' => 'Bank Central Asia (BCA)',
                        'bni' => 'Bank Negara Indonesia (BNI)',
                        'bri' => 'Bank Rakyat Indonesia (BRI)',
                        'mandiri' => 'Bank Mandiri',
                        'cimb' => 'CIMB Niaga',
                        'danamon' => 'Bank Danamon',
                        'permata' => 'Bank Permata',
                        'btn' => 'Bank Tabungan Negara (BTN)',
                        'bsi' => 'Bank Syariah Indonesia (BSI)',
                        'mega' => 'Bank Mega',
                        'ocbc' => 'OCBC NISP',
                        'panin' => 'Panin Bank',
                        'uob' => 'UOB Indonesia',
                        'maybank' => 'Maybank Indonesia',
                        'other' => 'Bank Lainnya'
                    ])
                    ->required()
                    ->searchable(),

                Forms\Components\TextInput::make('nomor_rekening_bank')
                    ->label('Nomor Rekening Bank')
                    ->required()
                    ->maxLength(20),

                Forms\Components\TextInput::make('nama_pemilik_rekening_bank')
                    ->label('Nama Pemilik Rekening')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('profile.first_name')
                    ->label('Nama Nasabah')
                    ->formatStateUsing(fn ($record) => "{$record->profile->first_name} {$record->profile->last_name}")
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('nomor_rekening')
                    ->searchable(),

                Tables\Columns\TextColumn::make('nominal_penempatan')
                    ->money('IDR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('jangka_waktu')
                    ->suffix(' bulan')
                    ->sortable(),

                Tables\Columns\TextColumn::make('tanggal_pembukaan')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('tanggal_jatuh_tempo')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('rate_bunga')
                    ->suffix('%')
                    ->sortable(),

                Tables\Columns\TextColumn::make('nominal_bunga')
                    ->money('IDR')
                    ->sortable(),

                Tables\Columns\IconColumn::make('perpanjangan_otomatis')
                    ->boolean(),

                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'success' => 'active',
                        'danger' => 'ended',
                    ]),

                Tables\Columns\TextColumn::make('nama_bank')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('nomor_rekening_bank')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('nama_pemilik_rekening_bank')
                    ->searchable()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'active' => 'Active',
                        'ended' => 'Ended'
                    ]),
                Tables\Filters\Filter::make('jatuh_tempo_bulan_ini')
                    ->query(fn ($query) => $query->whereMonth('tanggal_jatuh_tempo', now()->month)),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
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
            'index' => Pages\ListDepositos::route('/'),
            'create' => Pages\CreateDeposito::route('/create'),
            'view' => Pages\ViewDeposito::route('/{record}'),
            'edit' => Pages\EditDeposito::route('/{record}/edit'),
        ];
    }
}
