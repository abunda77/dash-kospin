<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Pinjaman;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Support\RawJs;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\PinjamanResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\PinjamanResource\RelationManagers;

class PinjamanResource extends Resource
{
    protected static ?string $model = Pinjaman::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationLabel = 'Rekening Kredit';
    protected static ?string $title = 'Rekening Kredit';

    public static function getNavigationGroup(): ?string
            {
                return 'Pinjaman';
            }
    protected static ?string $label = 'Rekening Pinjaman';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Pinjaman')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Select::make('profile_id')
                                    ->label('Nasabah')
                                    ->relationship(
                                        name: 'profile.user',
                                        titleAttribute: 'name'
                                    )
                                    ->searchable()
                                    ->required(),
                                Forms\Components\Select::make('produk_pinjaman_id')
                                    ->label('Produk Pinjaman')
                                    ->relationship('produkPinjaman', 'nama_produk')
                                    ->searchable()
                                    ->required(),
                            ]),
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('no_pinjaman')
                                    ->label('Nomor Pinjaman')
                                    ->required()
                                    ->maxLength(255)
                                    ->default(function() {
                                        do {
                                            $number = '7777-' . str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
                                        } while (Pinjaman::where('no_pinjaman', $number)->exists());
                                        return $number;
                                    })
                                    ->disabled(false)
                                    ->dehydrated(true),
                                Forms\Components\TextInput::make('jumlah_pinjaman')
                                    ->label('Jumlah Pinjaman')
                                    ->mask(RawJs::make('$money($input)'))
                                    ->stripCharacters(',')
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->placeholder('1,000,000')
                                    ->afterStateHydrated(function (Forms\Components\TextInput $component, $state) {
                                        try {
                                            if ($state) {
                                                $component->state(number_format($state, 0, '.', ','));
                                            }
                                        } catch (\Exception $e) {
                                            Log::error('Error in jumlah_pinjaman hydration: ' . $e->getMessage());
                                        }
                                    })
                                    ->dehydrateStateUsing(fn ($state) => (int) str_replace(',', '', $state))
                                    ->required(),
                                Forms\Components\TextInput::make('jangka_waktu')
                                    ->label('Jangka Waktu (Bulan)')
                                    ->numeric()
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function($state, $get, $set) {
                                        if (!$state || !$get('tanggal_pinjaman')) {
                                            return;
                                        }

                                        $tanggalPinjaman = \Carbon\Carbon::parse($get('tanggal_pinjaman'));
                                        $jangkaWaktu = (int)$state;
                                        $satuan = $get('jangka_waktu_satuan') ?? 'bulan';

                                        if ($satuan === 'tahun') {
                                            $jangkaWaktu *= 12;
                                        }

                                        $tanggalJatuhTempo = $tanggalPinjaman->copy()->addMonths($jangkaWaktu);
                                        $set('tanggal_jatuh_tempo', $tanggalJatuhTempo->format('Y-m-d'));
                                    }),
                            ]),

                            Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Select::make('beaya_bunga_pinjaman_id')
                                    ->label('Biaya Bunga')
                                    ->relationship('biayaBungaPinjaman', 'name')
                                    ->searchable()
                                    ->required(),
                                Forms\Components\Select::make('denda_id')
                                    ->label('Denda')
                                    ->relationship('denda', 'rate_denda')
                                    ->searchable()
                                    ->nullable(),
                            ]),

                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\DatePicker::make('tanggal_pinjaman')
                                    ->label('Tanggal Pinjaman')
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function($state, $get, $set) {
                                        try {
                                            if ($state && $get('jangka_waktu')) {
                                                $tanggalPinjaman = \Carbon\Carbon::parse($state);
                                                $jangkaWaktu = (int)$get('jangka_waktu');
                                                $satuan = $get('jangka_waktu_satuan') ?? 'bulan';

                                                if ($satuan === 'tahun') {
                                                    $jangkaWaktu *= 12;
                                                }

                                                $tanggalJatuhTempo = $tanggalPinjaman->copy()->addMonths($jangkaWaktu);
                                                $set('tanggal_jatuh_tempo', $tanggalJatuhTempo);
                                            }
                                        } catch (\Exception $e) {
                                            Log::error('Error in tanggal_pinjaman calculation: ' . $e->getMessage());
                                        }
                                    }),
                                    Forms\Components\Select::make('jangka_waktu_satuan')
                                    ->label('Satuan Waktu')
                                    ->options([
                                        'bulan' => 'Bulan',
                                        'tahun' => 'Tahun'
                                    ])
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function($state, $get, $set) {
                                        if (!$state || !$get('tanggal_pinjaman') || !$get('jangka_waktu')) {
                                            return;
                                        }

                                        $tanggalPinjaman = \Carbon\Carbon::parse($get('tanggal_pinjaman'));
                                        $jangkaWaktu = (int)$get('jangka_waktu');

                                        if ($state === 'tahun') {
                                            $jangkaWaktu *= 12;
                                        }

                                        $tanggalJatuhTempo = $tanggalPinjaman->copy()->addMonths($jangkaWaktu);
                                        $set('tanggal_jatuh_tempo', $tanggalJatuhTempo->format('Y-m-d'));
                                    }),

                                Forms\Components\DatePicker::make('tanggal_jatuh_tempo')
                                    ->label('Tanggal Jatuh Tempo')
                                    ->required()
                                    ->dehydrated(true)
                                    ->disabled(false)
                                    ->afterStateHydrated(function($state, $get, $set) {
                                        if ($get('tanggal_pinjaman') && $get('jangka_waktu')) {
                                            $tanggalPinjaman = \Carbon\Carbon::parse($get('tanggal_pinjaman'));
                                            $jangkaWaktu = (int)$get('jangka_waktu');
                                            $satuan = $get('jangka_waktu_satuan') ?? 'bulan';

                                            if ($satuan === 'tahun') {
                                                $jangkaWaktu *= 12;
                                            }

                                            $tanggalJatuhTempo = $tanggalPinjaman->copy()->addMonths($jangkaWaktu);
                                            $set('tanggal_jatuh_tempo', $tanggalJatuhTempo->format('Y-m-d'));
                                        }
                                    }),
                            ]),

                        Forms\Components\Select::make('status_pinjaman')
                            ->label('Status')
                            ->options([
                                'pending' => 'Pending',
                                'approved' => 'Approved',
                                'rejected' => 'Rejected',
                                'completed' => 'Completed'
                            ])
                            ->required(),
                        Forms\Components\Textarea::make('keterangan')
                            ->label('Keterangan')
                            ->rows(3),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('no_pinjaman')
                    ->label('Nomor Pinjaman')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('profile.user.name')
                    ->label('Nasabah')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('produkPinjaman.nama_produk')
                    ->label('Produk Pinjaman')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('jumlah_pinjaman')
                    ->label('Jumlah Pinjaman')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('jangka_waktu')
                    ->label('Jangka Waktu')
                    ->suffix(' Bulan')
                    ->sortable(),
                Tables\Columns\TextColumn::make('tanggal_pinjaman')
                    ->label('Tanggal Pinjaman')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('tanggal_jatuh_tempo')
                    ->label('Jatuh Tempo')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('denda.rate_denda')
                    ->label('Rate Denda')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\SelectColumn::make('status_pinjaman')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                        'completed' => 'Completed'
                    ])
                    ->rules(['required']),
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
            'index' => Pages\ListPinjamen::route('/'),
            'create' => Pages\CreatePinjaman::route('/create'),
            'view' => Pages\ViewPinjaman::route('/{record}'),
            'edit' => Pages\EditPinjaman::route('/{record}/edit'),
        ];
    }
}
