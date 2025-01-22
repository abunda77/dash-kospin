<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AnggotaReferralResource\Pages;
use App\Filament\Resources\AnggotaReferralResource\RelationManagers;
use App\Models\AnggotaReferral;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AnggotaReferralResource extends Resource
{
    protected static ?string $model = AnggotaReferral::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Referral';
    protected static ?string $navigationLabel = 'Anggota Referral';
    protected static ?string $pluralModelLabel = 'Anggota Referral';
    protected static ?string $pluralLabel = 'Anggota Referral';
    protected static ?string $modelLabel = 'Anggota Referral';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('kode_referral')
                    ->required()
                    ->maxLength(20)
                    ->default(fn () => 'REFF' . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT))
                    ->disabled()
                    ->dehydrated(),
                Forms\Components\TextInput::make('nama')
                    ->required()
                    ->maxLength(100),
                Forms\Components\Select::make('status_referral')
                    ->required()
                    ->options([
                        'Freelance' => 'Freelance',
                        'Staff' => 'Staff',
                        'Marketing' => 'Marketing'
                    ]),

                Forms\Components\TextInput::make('no_rekening')
                    ->required()
                    ->maxLength(30),
                Forms\Components\Select::make('bank')
                    ->required()
                    ->options([
                        'BCA' => 'Bank Central Asia (BCA)',
                        'BNI' => 'Bank Negara Indonesia (BNI)',
                        'BRI' => 'Bank Rakyat Indonesia (BRI)',
                        'Mandiri' => 'Bank Mandiri',
                        'CIMB' => 'CIMB Niaga',
                        'BTN' => 'Bank Tabungan Negara (BTN)',
                        'Permata' => 'Bank Permata',
                        'Danamon' => 'Bank Danamon',
                        'BSI' => 'Bank Syariah Indonesia',
                        'OCBC' => 'OCBC NISP',
                        'Panin' => 'Bank Panin',
                        'Maybank' => 'Maybank Indonesia'
                    ]),
                Forms\Components\TextInput::make('atas_nama_bank')
                    ->required()
                    ->maxLength(100),
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->required()
                    ->maxLength(100),
                Forms\Components\TextInput::make('no_hp')
                    ->required()
                    ->maxLength(20),
                Forms\Components\DateTimePicker::make('tanggal_bergabung')
                    ->required(),
                Forms\Components\Toggle::make('status_aktif')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('kode_referral')
                    ->searchable(),
                Tables\Columns\TextColumn::make('nama')
                    ->searchable(),
                Tables\Columns\TextColumn::make('status_referral'),
                Tables\Columns\TextColumn::make('no_rekening')
                    ->searchable(),
                Tables\Columns\TextColumn::make('bank')
                    ->searchable(),
                Tables\Columns\TextColumn::make('atas_nama_bank')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('no_hp')
                    ->searchable(),
                Tables\Columns\TextColumn::make('tanggal_bergabung')
                    ->dateTime()
                    ->sortable(),
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
            'index' => Pages\ListAnggotaReferrals::route('/'),
            'create' => Pages\CreateAnggotaReferral::route('/create'),
            'edit' => Pages\EditAnggotaReferral::route('/{record}/edit'),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
