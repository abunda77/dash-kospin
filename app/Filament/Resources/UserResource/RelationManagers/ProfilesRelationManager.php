<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Illuminate\Support\Facades\DB;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProfilesRelationManager extends RelationManager
{
    protected static string $relationship = 'profiles';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Pribadi')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('first_name')
                                    ->label('Nama Depan')
                                    ->required(),
                                Forms\Components\TextInput::make('last_name')
                                    ->label('Nama Belakang')
                                    ->required(),
                            ]),
                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\TextInput::make('phone')
                                    ->label('No. Telepon')
                                    ->tel(),
                                Forms\Components\TextInput::make('whatsapp')
                                    ->label('WhatsApp'),
                                Forms\Components\TextInput::make('email')
                                    ->email()
                                    ->label('Email'),
                            ]),
                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\Select::make('gender')
                                    ->label('Jenis Kelamin')
                                    ->options([
                                        'L' => 'Laki-laki',
                                        'P' => 'Perempuan'
                                    ]),
                                Forms\Components\DatePicker::make('birthday')
                                    ->label('Tanggal Lahir'),
                                Forms\Components\Select::make('mariage')
                                    ->label('Status Pernikahan')
                                    ->options([
                                        'single' => 'Belum Menikah',
                                        'married' => 'Menikah',
                                        'divorced' => 'Cerai'
                                    ]),
                            ]),
                        Forms\Components\Textarea::make('address')
                            ->label('Alamat')
                            ->rows(3),
                    ]),

                Forms\Components\Section::make('Informasi Wilayah')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Select::make('province_id')
                                    ->label('Provinsi')
                                    ->options(function () {
                                        return DB::table('regions')
                                            ->where('level', 'province')
                                            ->pluck('name', 'code');
                                    })
                                    ->searchable()
                                    ->reactive()
                                    ->afterStateUpdated(fn (callable $set) => $set('district_id', null)),

                                Forms\Components\Select::make('district_id')
                                    ->label('Kabupaten/Kota')
                                    ->options(function (callable $get) {
                                        $provinceId = $get('province_id');
                                        if (!$provinceId) {
                                            return [];
                                        }
                                        return DB::table('regions')
                                            ->where('level', 'district')
                                            ->where('code', 'like', $provinceId . '%')
                                            ->pluck('name', 'code');
                                    })
                                    ->searchable()
                                    ->reactive()
                                    ->afterStateUpdated(fn (callable $set) => $set('city_id', null)),

                                Forms\Components\Select::make('city_id')
                                    ->label('Kecamatan')
                                    ->options(function (callable $get) {
                                        $districtId = $get('district_id');
                                        if (!$districtId) {
                                            return [];
                                        }
                                        return DB::table('regions')
                                            ->where('level', 'city')
                                            ->where('code', 'like', $districtId . '%')
                                            ->pluck('name', 'code');
                                    })
                                    ->searchable()
                                    ->reactive()
                                    ->afterStateUpdated(fn (callable $set) => $set('village_id', null)),

                                Forms\Components\Select::make('village_id')
                                    ->label('Desa/Kelurahan')
                                    ->options(function (callable $get) {
                                        $cityId = $get('city_id');
                                        if (!$cityId) {
                                            return [];
                                        }
                                        return DB::table('regions')
                                            ->where('level', 'village')
                                            ->where('code', 'like', $cityId . '%')
                                            ->pluck('name', 'code');
                                    })
                                    ->searchable(),
                            ]),
                    ]),

                Forms\Components\Section::make('Informasi Identitas')
                    ->schema([
                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\Select::make('sign_identity')
                                    ->label('Jenis Identitas')
                                    ->options([
                                        'KTP' => 'KTP',
                                        'SIM' => 'SIM',
                                        'Passport' => 'Passport'
                                    ]),
                                Forms\Components\TextInput::make('no_identity')
                                    ->label('Nomor Identitas'),
                                Forms\Components\FileUpload::make('image_identity')
                                    ->label('Foto Identitas')
                                    ->multiple()
                                    ->image(),
                            ]),
                    ]),

                Forms\Components\Section::make('Informasi Tambahan')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('job')
                                    ->label('Pekerjaan'),
                                Forms\Components\TextInput::make('monthly_income')
                                    ->label('Pendapatan Bulanan')
                                    ->numeric()
                                    ->prefix('Rp'),
                            ]),
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Select::make('type_member')
                                    ->label('Tipe Anggota')
                                    ->options([
                                        'regular' => 'Regular',
                                        'premium' => 'Premium',
                                        'vip' => 'VIP'
                                    ]),
                                Forms\Components\FileUpload::make('avatar')
                                    ->label('Foto Profil')
                                    ->image(),
                            ]),
                        Forms\Components\Toggle::make('is_active')
                            ->label('Status Aktif')
                            ->default(true),
                        Forms\Components\Textarea::make('notes')
                            ->label('Catatan')
                            ->rows(3),
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('Profile')
            ->columns([
                Tables\Columns\TextColumn::make('first_name')
                    ->label('Nama Depan')
                    ->searchable(),
                Tables\Columns\TextColumn::make('last_name')
                    ->label('Nama Belakang')
                    ->searchable(),
                Tables\Columns\TextColumn::make('phone')
                    ->label('No. Telepon')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('type_member')
                    ->label('Tipe Anggota')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'regular' => 'gray',
                        'premium' => 'warning',
                        'vip' => 'success',
                    }),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Status')
                    ->boolean(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                ->closeModalByClickingAway(false)
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
