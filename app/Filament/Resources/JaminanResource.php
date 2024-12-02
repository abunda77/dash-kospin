<?php

namespace App\Filament\Resources;

use App\Filament\Resources\JaminanResource\Pages;
use App\Filament\Resources\JaminanResource\RelationManagers;
use App\Models\Jaminan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Support\RawJs;

class JaminanResource extends Resource
{
    protected static ?string $model = Jaminan::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Pinjaman';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('id_pinjaman')
                    ->label('ID Pinjaman')
                    ->relationship('pinjaman', 'no_pinjaman')
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\Select::make('jenis_jaminan')
                    ->label('Jenis Jaminan')
                    ->options([
                        'BPKB MOBIL' => 'BPKB MOBIL',
                        'BPKB MOTOR' => 'BPKB MOTOR',
                        'SERTIFIKAT RUMAH/TANAH' => 'SERTIFIKAT RUMAH/TANAH',
                        'BARANG ELEKTRONIK' => 'BARANG ELEKTRONIK',
                        'LAINNYA' => 'LAINNYA'
                    ])
                    ->required(),
                Forms\Components\TextInput::make('nilai_jaminan')
                    ->label('Nilai Jaminan')
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
                Forms\Components\TextInput::make('keterangan')
                    ->label('Keterangan')
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('pinjaman.no_pinjaman')
                    ->label('No rek.Pinjaman')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('jenis_jaminan')
                    ->label('Jenis Jaminan')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('nilai_jaminan')
                    ->label('Nilai Jaminan')
                    ->money('idr')
                    ->sortable(),
                Tables\Columns\TextColumn::make('keterangan')
                    ->label('Keterangan')
                    ->searchable()
                    ->limit(50),
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
            'index' => Pages\ListJaminans::route('/'),
            'create' => Pages\CreateJaminan::route('/create'),
            'edit' => Pages\EditJaminan::route('/{record}/edit'),
        ];
    }
}
