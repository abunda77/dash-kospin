<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PelunasanResource\Pages;
use App\Filament\Resources\PelunasanResource\RelationManagers;
use App\Models\Pelunasan;
use App\Models\Pinjaman;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Support\RawJs;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Closure;

class PelunasanResource extends Resource
{
    protected static ?string $model = Pelunasan::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Pinjaman';
    protected static ?string $navigationLabel = 'Pelunasan';
    protected static ?string $title = 'Pelunasan';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('no_pinjaman')
                    ->label('Nomor Pinjaman')
                    ->required()
                    ->searchable()
                    ->options(function () {
                        return Pinjaman::all()->pluck('no_pinjaman', 'no_pinjaman');
                    })
                    ->afterStateUpdated(function (Forms\Set $set, ?string $state) {
                        if ($state) {
                            $pinjaman = Pinjaman::where('no_pinjaman', $state)->first();
                            if ($pinjaman) {
                                $set('pinjaman_id', $pinjaman->id_pinjaman);
                                $set('profile_id', $pinjaman->profile_id);
                                $set('nama_lengkap', $pinjaman->profile->first_name . ' ' . $pinjaman->profile->last_name);
                                $set('jumlah_pelunasan', $pinjaman->jumlah_pinjaman);
                                $set('jumlah_pinjaman', $pinjaman->jumlah_pinjaman);
                            }
                        } else {
                            $set('pinjaman_id', null);
                            $set('profile_id', null);
                            $set('nama_lengkap', null);
                            $set('jumlah_pelunasan', null);
                            $set('jumlah_pinjaman', null);
                        }
                    })
                    ->live()
                    ->dehydrated(true),

                Forms\Components\TextInput::make('nama_lengkap')
                    ->label('Nama Lengkap')
                    ->disabled()
                    ->dehydrated(false),

                Forms\Components\Hidden::make('profile_id')
                    ->required(),

                Forms\Components\Hidden::make('pinjaman_id')
                    ->required(),

                Forms\Components\TextInput::make('jumlah_pinjaman')
                    ->label('Nominal Pinjaman')
                    ->disabled()
                    ->prefix('Rp')
                    ->mask(RawJs::make('$money($input)'))
                    ->stripCharacters(',')
                    ->numeric()
                    ->placeholder('1.000.000')
                    ->afterStateHydrated(function (Forms\Components\TextInput $component, $state) {
                        if ($state) {
                            $component->state(number_format($state, 0, ',', '.'));
                        }
                    })
                    ->dehydrated(false),

                Forms\Components\DatePicker::make('tanggal_pelunasan')
                    ->required()
                    ->default(now()),

                Forms\Components\TextInput::make('jumlah_pelunasan')
                    ->label('Jumlah Pelunasan')
                    ->required()
                    ->mask(RawJs::make('$money($input)'))
                    ->stripCharacters(',')
                    ->numeric()
                    ->prefix('Rp')
                    ->placeholder('1.000.000')
                    ->afterStateHydrated(function (Forms\Components\TextInput $component, $state) {
                        if ($state) {
                            $component->state(number_format($state, 0, ',', '.'));
                        }
                    })
                    ->dehydrateStateUsing(fn ($state) => (int) str_replace(',', '', $state)),

                Forms\Components\Select::make('status_pelunasan')
                    ->required()
                    ->options([
                        'normal' => 'Normal',
                        'dipercepat' => 'Dipercepat',
                        'tunggakan' => 'Pelunasan Tunggakan'
                    ])
                    ->default('normal'),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Informasi Pelunasan')
                    ->schema([
                        Infolists\Components\TextEntry::make('no_pinjaman')
                            ->label('Nomor Pinjaman'),

                        Infolists\Components\TextEntry::make('profile.first_name')
                            ->label('Nama Depan'),

                        Infolists\Components\TextEntry::make('profile.last_name')
                            ->label('Nama Belakang'),
                    ])->columns(3),

                Infolists\Components\Section::make('Detail Pinjaman dan Pelunasan')
                    ->schema([
                        Infolists\Components\TextEntry::make('pinjaman.jumlah_pinjaman')
                            ->label('Nominal Pinjaman')
                            ->formatStateUsing(fn ($state) => 'Rp ' . number_format($state, 0, ',', '.')),

                        Infolists\Components\TextEntry::make('jumlah_pelunasan')
                            ->label('Jumlah Pelunasan')
                            ->formatStateUsing(fn ($state) => 'Rp ' . number_format($state, 0, ',', '.')),

                        Infolists\Components\TextEntry::make('tanggal_pelunasan')
                            ->label('Tanggal Pelunasan')
                            ->date(),

                        Infolists\Components\TextEntry::make('status_pelunasan')
                            ->label('Status Pelunasan')
                            ->badge()
                            ->formatStateUsing(function ($state) {
                                return match ($state) {
                                    'normal' => 'Normal',
                                    'dipercepat' => 'Dipercepat',
                                    'tunggakan' => 'Pelunasan Tunggakan',
                                    default => $state,
                                };
                            })
                            ->color(function ($state) {
                                return match ($state) {
                                    'normal' => 'success',
                                    'dipercepat' => 'info',
                                    'tunggakan' => 'warning',
                                    default => 'gray',
                                };
                            }),
                    ])->columns(2),

                Infolists\Components\Section::make('Waktu Transaksi')
                    ->schema([
                        Infolists\Components\TextEntry::make('created_at')
                            ->label('Dibuat Pada')
                            ->dateTime(),

                        Infolists\Components\TextEntry::make('updated_at')
                            ->label('Terakhir Diperbarui')
                            ->dateTime(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('profile.first_name')
                    ->label('Nama Depan')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('profile.last_name')
                    ->label('Nama Belakang')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('no_pinjaman')
                    ->searchable(),
                Tables\Columns\TextColumn::make('tanggal_pelunasan')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('jumlah_pelunasan')
                    ->prefix('Rp ')
                    ->formatStateUsing(function ($state) {
                        return number_format($state, 0, ',', '.');
                    })
                    ->sortable(),
                Tables\Columns\SelectColumn::make('status_pelunasan')
                    ->options([
                        'normal' => 'Normal',
                        'dipercepat' => 'Dipercepat',
                        'tunggakan' => 'Pelunasan Tunggakan'
                    ]),
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
            'index' => Pages\ListPelunasans::route('/'),
            'create' => Pages\CreatePelunasan::route('/create'),
            'view' => Pages\ViewPelunasan::route('/{record}'),
            'edit' => Pages\EditPelunasan::route('/{record}/edit'),
        ];
    }
}
