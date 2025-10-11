<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MakanBergizisGratisResource\Pages;
use App\Models\MakanBergizisGratis;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class MakanBergizisGratisResource extends Resource
{
    protected static ?string $model = MakanBergizisGratis::class;

    protected static ?string $navigationIcon = 'heroicon-o-gift';

    protected static ?string $navigationLabel = 'Makan Bergizi Sinara';

    protected static ?string $modelLabel = 'Makan Bergizi Sinara';

    protected static ?string $pluralModelLabel = 'Makan Bergizi Sinara';

    protected static ?string $navigationGroup = 'Program Kerja';

    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Rekening')
                    ->schema([
                        Forms\Components\Select::make('tabungan_id')
                            ->label('Tabungan')
                            ->relationship('tabungan', 'no_tabungan')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->disabled(fn($record) => $record !== null),

                        Forms\Components\TextInput::make('no_tabungan')
                            ->label('No. Tabungan')
                            ->disabled()
                            ->dehydrated(false),

                        Forms\Components\DatePicker::make('tanggal_pemberian')
                            ->label('Tanggal Pemberian')
                            ->required()
                            ->default(today())
                            ->disabled(fn($record) => $record !== null),
                    ])->columns(3),

                Forms\Components\Section::make('Data Nasabah')
                    ->schema([
                        Forms\Components\KeyValue::make('data_nasabah')
                            ->label('Informasi Nasabah')
                            ->disabled()
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Data Rekening')
                    ->schema([
                        Forms\Components\KeyValue::make('data_rekening')
                            ->label('Informasi Rekening')
                            ->disabled()
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Data Produk')
                    ->schema([
                        Forms\Components\KeyValue::make('data_produk')
                            ->label('Informasi Produk')
                            ->disabled()
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Transaksi Terakhir')
                    ->schema([
                        Forms\Components\KeyValue::make('data_transaksi_terakhir')
                            ->label('Informasi Transaksi')
                            ->disabled()
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('no_tabungan')
                    ->label('No. Tabungan')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('profile.first_name')
                    ->label('Nama Nasabah')
                    ->formatStateUsing(
                        fn($record) =>
                        $record->profile->first_name . ' ' . $record->profile->last_name
                    )
                    ->searchable(['first_name', 'last_name'])
                    ->sortable(),

                Tables\Columns\TextColumn::make('tanggal_pemberian')
                    ->label('Tanggal Pemberian')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('data_rekening')
                    ->label('Saldo')
                    ->formatStateUsing(function ($record) {
                        $data = is_string($record->data_rekening)
                            ? json_decode($record->data_rekening, true)
                            : $record->data_rekening;
                        return $data['saldo_formatted'] ?? '-';
                    })
                    ->sortable(false),

                Tables\Columns\TextColumn::make('data_produk')
                    ->label('Produk')
                    ->formatStateUsing(function ($record) {
                        $data = is_string($record->data_produk)
                            ? json_decode($record->data_produk, true)
                            : $record->data_produk;
                        return $data['nama'] ?? '-';
                    })
                    ->sortable(false),

                Tables\Columns\TextColumn::make('scanned_at')
                    ->label('Waktu Scan')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('tanggal_pemberian', 'desc')
            ->filters([
                Tables\Filters\Filter::make('tanggal_pemberian')
                    ->form([
                        Forms\Components\DatePicker::make('dari')
                            ->label('Dari Tanggal'),
                        Forms\Components\DatePicker::make('sampai')
                            ->label('Sampai Tanggal'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['dari'],
                                fn(Builder $query, $date): Builder => $query->whereDate('tanggal_pemberian', '>=', $date),
                            )
                            ->when(
                                $data['sampai'],
                                fn(Builder $query, $date): Builder => $query->whereDate('tanggal_pemberian', '<=', $date),
                            );
                    }),

                Tables\Filters\Filter::make('hari_ini')
                    ->label('Hari Ini')
                    ->query(fn(Builder $query): Builder => $query->whereDate('tanggal_pemberian', today())),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
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

    public static function getWidgets(): array
    {
        return [
            \App\Filament\Resources\MakanBergizisGratisResource\Widgets\MakanBergizisGratisStatsWidget::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMakanBergizisGratis::route('/'),
            'view' => Pages\ViewMakanBergizisGratis::route('/{record}'),
        ];
    }
}
