<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TransaksiReferralResource\Pages;
use App\Filament\Resources\TransaksiReferralResource\RelationManagers;
use App\Models\TransaksiReferral;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TransaksiReferralResource extends Resource
{
    protected static ?string $model = TransaksiReferral::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Referral';
    protected static ?string $navigationLabel = 'Transaksi Referral';
    protected static ?string $pluralModelLabel = 'Transaksi Referral';
    protected static ?string $pluralLabel = 'Transaksi Referral';
    protected static ?string $modelLabel = 'Transaksi Referral';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('id_referral')
                    ->label('Anggota Referral')
                    ->relationship('anggotaReferral', 'kode_referral', fn (Builder $query) => $query->select(['id_referral', 'kode_referral', 'nama']))
                    ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->kode_referral} - {$record->nama}")
                    ->searchable(['kode_referral', 'nama'])
                    ->required(),
                Forms\Components\Select::make('id_nasabah')
                    ->label('Nasabah')
                    ->relationship('profile', 'first_name', fn (Builder $query) => $query->select(['id_user', 'first_name', 'last_name']))
                    ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->first_name} {$record->last_name}")
                    ->searchable(['first_name', 'last_name'])
                    ->required(),
                Forms\Components\Select::make('kode_komisi')
                    ->label('Setting Komisi')
                    ->relationship('settingKomisi', 'kode_komisi', fn (Builder $query) => $query->select(['kode_komisi', 'minimal_transaksi', 'nominal_komisi','persen_komisi']))
                    ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->kode_komisi} - {$record->jenis_komisi} (Rp {$record->minimal_transaksi} - {$record->persen_komisi} %)")
                    ->searchable(['kode_komisi', 'jenis_komisi'])
                    ->required()
                    ->live()
                    ->afterStateUpdated(function ($state, Forms\Set $set) {
                        $settingKomisi = \App\Models\SettingKomisi::where('kode_komisi', $state)->first();
                        if ($settingKomisi) {
                            $set('nominal_transaksi', $settingKomisi->minimal_transaksi);
                            $nilaiKomisi = ($settingKomisi->persen_komisi * $settingKomisi->minimal_transaksi) / 100;
                            $set('nilai_komisi', $nilaiKomisi);
                        }
                    }),
                Forms\Components\TextInput::make('nominal_transaksi')
                    ->required()
                    ->disabled()
                    ->dehydrated(true)
                    ->numeric(),
                Forms\Components\TextInput::make('nilai_komisi')
                    ->required()
                    ->disabled()
                    ->dehydrated(true)
                    ->numeric(),
                Forms\Components\DateTimePicker::make('tanggal_transaksi')
                    ->required(),
                Forms\Components\Select::make('status_komisi')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected'
                    ])
                    ->required(),
                Forms\Components\Select::make('jenis_transaksi')
                    ->options([
                        'deposit' => 'Deposit',
                        'withdrawal' => 'Withdrawal'
                    ])
                    ->default('deposit')
                    ->disabled()
                    ->dehydrated(true)
                    ->required(),
                Forms\Components\Textarea::make('keterangan')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('tanggal_transaksi')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('anggotaReferral.kode_referral')
                    ->label('Kode Referral')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('profile.first_name')
                    ->label('Nama Nasabah')
                    ->formatStateUsing(fn ($record) => "{$record->profile->first_name} {$record->profile->last_name}")
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('settingKomisi.kode_komisi')
                    ->label('Setting Komisi')
                    ->searchable(),
                Tables\Columns\TextColumn::make('nominal_transaksi')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('nilai_komisi')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\SelectColumn::make('status_komisi')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected'
                    ])
                    ->afterStateUpdated(function($record, $state) {
                        $record->status_komisi = $state;
                        $record->save();
                    }),
                Tables\Columns\TextColumn::make('jenis_transaksi')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'deposit' => 'success',
                        'withdrawal' => 'danger',
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('tanggal_transaksi', 'desc')
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
            'index' => Pages\ListTransaksiReferrals::route('/'),
            'create' => Pages\CreateTransaksiReferral::route('/create'),
            'edit' => Pages\EditTransaksiReferral::route('/{record}/edit'),
        ];
    }
}
