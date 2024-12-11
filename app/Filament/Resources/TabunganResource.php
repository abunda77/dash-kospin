<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TabunganResource\Pages;
use App\Filament\Resources\TabunganResource\RelationManagers;
use App\Models\Tabungan;
use App\Models\Profile;
use App\Models\ProdukTabungan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Support\RawJs;
use Illuminate\Support\Facades\Log;

class TabunganResource extends Resource
{
    protected static ?string $model = Tabungan::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationLabel = 'Rekening Tabungan';
    public static function getNavigationGroup(): ?string
            {
                return 'Tabungan';
            }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('no_tabungan')
                    ->label('No Rekening')
                    ->required()
                    ->maxLength(255)
                    ->default(function() {
                        do {
                            $number = '8888-' . str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
                        } while (Tabungan::where('no_tabungan', $number)->exists());
                        return $number;
                    })
                    ->disabled(),
                Forms\Components\Select::make('id_profile')
                    ->label('Nasabah')
                    ->relationship('profile', 'first_name')
                    ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->first_name} {$record->last_name}")
                    ->required(),
                Forms\Components\Select::make('produk_tabungan')
                    ->label('Produk Tabungan')
                    ->relationship('produkTabungan', 'nama_produk')
                    ->required(),
                Forms\Components\TextInput::make('saldo')
                    ->label('Saldo')
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
                Forms\Components\DatePicker::make('tanggal_buka_rekening')
                    ->label('Tanggal Buka')
                    ->required(),
                Forms\Components\Select::make('status_rekening')
                    ->label('Status')
                    ->options([
                        'aktif' => 'Aktif',
                        'tidak_aktif' => 'Tidak Aktif',
                        'blokir' => 'Blokir'
                    ])
                    ->required()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('no_tabungan')
                    ->label('No Rekening')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('profile.first_name')
                    ->label('Nasabah')
                    ->formatStateUsing(function ($record) {
                        Log::info('Debug Tabungan-Profile:', [
                            'id_profile' => $record->id_profile,
                            'profile' => $record->profile
                        ]);

                        return $record->profile
                            ? "{$record->profile->first_name} {$record->profile->last_name}"
                            : '-';
                    })
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('produkTabungan.nama_produk')
                    ->label('Produk')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('saldo')
                    ->label('Saldo')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('tanggal_buka_rekening')
                    ->label('Tanggal Buka')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status_rekening')
                    ->label('Status')
                    ->badge()
                    ->searchable()
                    ->sortable()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'aktif' => 'Aktif',
                        'tidak_aktif' => 'Tidak Aktif',
                        'blokir' => 'Blokir',
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'aktif' => 'success',
                        'tidak_aktif' => 'danger',
                        'blokir' => 'warning',
                    }),
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
                Tables\Columns\TextColumn::make('id_profile')
                    ->label('ID Profile')
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
            'index' => Pages\ListTabungans::route('/'),
            'create' => Pages\CreateTabungan::route('/create'),
            'edit' => Pages\EditTabungan::route('/{record}/edit'),
        ];
    }
}
