<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Profile;
use App\Models\Tabungan;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Support\RawJs;
use App\Models\ProdukTabungan;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\TabunganResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\TabunganResource\RelationManagers;

class TabunganResource extends Resource
{
    protected static ?string $model = Tabungan::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationLabel = 'Rekening Tabungan';
    protected static ?string $title = 'Rekening Tabungan';
    public static function getNavigationGroup(): ?string
    {
        return 'Tabungan';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Section informasi rekening
                Forms\Components\Section::make('Informasi Rekening')
                    ->description('Nomor rekening akan dibuat otomatis dengan format 8888-XXXX')
                    ->icon('heroicon-o-information-circle')
                    ->schema([
                        Forms\Components\Placeholder::make('info')
                            ->label('')
                            ->content(new \Illuminate\Support\HtmlString('
                                <div style="background: linear-gradient(135deg, #fff7ed 0%, #fed7aa 100%); border: 2px solid #ea580c; border-radius: 12px; padding: 16px; box-shadow: 0 4px 12px rgba(234, 88, 12, 0.15);" class="bg-blue-50 border-blue-200 dark:shadow-xl">
                                    <div class="flex items-start">
                                        <div class="flex-shrink-0">
                                            <svg style="color: #ea580c !important;" class="h-6 w-6" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                            </svg>
                                        </div>
                                        <div class="ml-3">
                                            <h4 style="color: #9a3412 !important; font-weight: 600 !important; font-size: 14px;">
                                                🔥 Informasi Penting
                                            </h4>
                                            <div style="margin-top: 8px; color: #c2410c !important; font-size: 13px; line-height: 1.5;">
                                                <p style="margin-bottom: 6px;">• Untuk produk <strong style="color: #9a3412 !important;">Tabungan Mitra Sinara</strong> pastikan diawali dengan kode <code style="background: #fb923c !important; color: #9a3412 !important; padding: 4px 8px; border-radius: 6px; font-family: ui-monospace, monospace; font-weight: 500;">5555-xxxx</code></p>
                                                <p>• Untuk produk tabungan lainnya menggunakan format <code style="background: #fb923c !important; color: #9a3412 !important; padding: 4px 8px; border-radius: 6px; font-family: ui-monospace, monospace; font-weight: 500;">8888-xxxx</code></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            '))
                            ->columnSpanFull(),
                    ])
                    ->collapsible()
                    ->columnSpanFull(),

                Forms\Components\TextInput::make('no_tabungan')
                    ->label('No Rekening')
                    ->required()
                    ->maxLength(255)
                    ->default(function () {
                        do {
                            $number = '8888-' . str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
                        } while (Tabungan::where('no_tabungan', $number)->exists());
                        return $number;
                    })
                    ->disabled(false)
                    ->helperText('Format: 8888-XXXX (dibuat otomatis)'),
                Forms\Components\Select::make('id_profile')
                    ->label('Nasabah')
                    ->relationship('profile', 'first_name')
                    ->getOptionLabelFromRecordUsing(fn($record) => "{$record->first_name} {$record->last_name}")
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
                    ->dehydrateStateUsing(fn($state) => (int) str_replace(',', '', $state))
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
                    ->required(),
                Forms\Components\Fieldset::make('Kode Teller')
                    ->schema([
                        Forms\Components\TextInput::make('kode_teller')
                            ->label('Kode Teller')
                            ->default(auth('admin')->user()->id)
                            ->disabled()
                            ->dehydrated(fn($state) => filled($state))
                            ->required(),
                    ]),
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
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'aktif' => 'Aktif',
                        'tidak_aktif' => 'Tidak Aktif',
                        'blokir' => 'Blokir',
                    })
                    ->color(fn(string $state): string => match ($state) {
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
            'index' => Pages\ListTabungans::route('/'),
            'create' => Pages\CreateTabungan::route('/create'),
            'view' => Pages\ViewTabungan::route('/{record}'),
            'edit' => Pages\EditTabungan::route('/{record}/edit'),
        ];
    }
}
