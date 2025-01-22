<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Support\RawJs;
use App\Models\SettingKomisi;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\SettingKomisiResource\Pages;
use App\Filament\Resources\SettingKomisiResource\RelationManagers;

class SettingKomisiResource extends Resource
{
    protected static ?string $model = SettingKomisi::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Referral';
    protected static ?string $navigationLabel = 'Setting Komisi';
    protected static ?string $pluralModelLabel = 'Setting Komisi';
    protected static ?string $pluralLabel = 'Setting Komisi';
    protected static ?string $modelLabel = 'Setting Komisi';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('kode_komisi')
                    ->label('Kode Komisi')
                    ->default(function () {
                        // Generate kode komisi: KMS-[4 digit]
                        return 'KMS-' . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
                    })
                    ->disabled()
                    ->dehydrated(true),
                Forms\Components\Select::make('jenis_komisi')
                    ->required()
                    ->options([
                        'tabungan' => 'Tabungan',
                        'pinjaman' => 'Pinjaman',
                        'deposito' => 'Deposito'
                    ]),
                Forms\Components\TextInput::make('persen_komisi')
                    ->required()
                    ->numeric()
                    ->default(60)
                    ->suffix('%')
                    ->live()
                    ->afterStateUpdated(function ($state, $set, $get) {
                        $minimalTransaksi = $get('minimal_transaksi') ?? 0;
                        $nominalKomisi = ($state * $minimalTransaksi) / 100;
                        $set('nominal_komisi', $nominalKomisi);
                    }),

                Forms\Components\TextInput::make('minimal_transaksi')
                    ->label('Minimal Transaksi')
                    ->required()
                    ->numeric()
                    ->prefix('Rp')
                    ->live()
                    ->afterStateUpdated(function ($state, $set, $get) {
                        $persenKomisi = $get('persen_komisi') ?? 0;
                        $nominalKomisi = ($persenKomisi * $state) / 100;
                        $set('nominal_komisi', $nominalKomisi);
                    }),

                Forms\Components\TextInput::make('nominal_komisi')
                    ->label('Nominal Komisi')
                    ->prefix('Rp')
                    ->disabled()
                    ->numeric()
                    ->required()
                    ->dehydrated(true),

                Forms\Components\TextInput::make('maksimal_komisi')
                    ->label('Maksimal Komisi')
                    ->required()
                    ->numeric()
                    ->prefix('Rp')
                    ,

                Forms\Components\Toggle::make('status_aktif')
                    ->required()
                    ->default(true),

                Forms\Components\Textarea::make('keterangan')
                    ->maxLength(65535)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('kode_komisi')
                    ->searchable(),
                Tables\Columns\TextColumn::make('jenis_komisi'),
                Tables\Columns\TextColumn::make('persen_komisi')
                    ->suffix('%'),
                Tables\Columns\TextColumn::make('minimal_transaksi')
                    ->money('IDR')
                    ->formatStateUsing(fn (string $state): string => 'Rp ' . number_format((float)$state, 0, ',', '.')),
                Tables\Columns\TextColumn::make('nominal_komisi')
                    ->money('IDR')
                    ->formatStateUsing(fn (string $state): string => 'Rp ' . number_format((float)$state, 0, ',', '.')),
                Tables\Columns\TextColumn::make('maksimal_komisi')
                    ->money('IDR')
                    ->formatStateUsing(fn (string $state): string => 'Rp ' . number_format((float)$state, 0, ',', '.')),
                Tables\Columns\IconColumn::make('status_aktif')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
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
            'index' => Pages\ListSettingKomisis::route('/'),
            'create' => Pages\CreateSettingKomisi::route('/create'),
            'edit' => Pages\EditSettingKomisi::route('/{record}/edit'),
        ];
    }
}
