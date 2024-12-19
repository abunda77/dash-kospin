<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProfileResource\Pages;
use App\Filament\Resources\ProfileResource\RelationManagers;
use App\Models\Profile;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\DB;
use Filament\Support\RawJs;
use Ysfkaya\FilamentPhoneInput\Forms\PhoneInput;
use Ysfkaya\FilamentPhoneInput\Tables\PhoneColumn;
use Ysfkaya\FilamentPhoneInput\Infolists\PhoneEntry;
use Ysfkaya\FilamentPhoneInput\PhoneInputNumberType;

class ProfileResource extends Resource
{
    protected static ?string $model = Profile::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';
    public static function getNavigationGroup(): ?string
            {
                return 'Data Nasabah';
            }
    protected static ?string $navigationLabel = 'Profile Nasabah';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('id_user')
                    ->relationship('user', 'name')
                    ->required(),
                Forms\Components\TextInput::make('first_name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('last_name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('address')
                    ->required()
                    ->maxLength(65535),
                Forms\Components\Select::make('sign_identity')
                    ->label('Tanda Pengenal')
                    ->options([
                        'ktp' => 'KTP',
                        'paspor' => 'Paspor',
                        'sim' => 'SIM',
                        'kartu_pelajar' => 'Kartu Pelajar/Mahasiswa',
                        'kartu_keluarga' => 'Kartu Keluarga',
                        'lainnya' => 'Lainnya'
                    ])
                    ->required(),
                Forms\Components\TextInput::make('no_identity')
                    ->label('No. Identitas')
                    ->maxLength(255),
                Forms\Components\FileUpload::make('image_identity')
                ->label('Foto / Scan Tanda Pengenal')
                    ->multiple()
                    ->image(),
                PhoneInput::make('phone')
                    ->defaultCountry('ID')
                    ,
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->maxLength(255),
                PhoneInput::make('whatsapp')
                    ->defaultCountry('ID'),
                Forms\Components\Select::make('gender')
                    ->options([
                        'L' => 'Laki-laki',
                        'P' => 'Perempuan'
                    ])
                    ->required(),
                Forms\Components\DatePicker::make('birthday')
                    ->label('Tanggal Lahir')
                    ->required(),
                Forms\Components\Select::make('mariage')
                ->label('Status Pernikahan')
                    ->options([
                        'single' => 'Single',
                        'married' => 'Menikah',
                        'divorced' => 'Cerai'
                    ])
                    ->required(),
                Forms\Components\TextInput::make('job')
                    ->maxLength(50),
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
                Forms\Components\TextInput::make('monthly_income')
                    ->label('Monthly Income (IDR)')
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
                Forms\Components\Select::make('is_active')
                    ->options([
                        1 => 'Active',
                        0 => 'Inactive'
                    ])
                    ->required(),
                Forms\Components\Select::make('type_member')
                    ->options([
                        'regular' => 'Regular',
                        'premium' => 'Premium'
                    ])
                    ->required(),
                Forms\Components\FileUpload::make('avatar')
                    ->image(),
                Forms\Components\TextInput::make('remote_url')
                    ->url()
                    ->maxLength(255),
                Forms\Components\Textarea::make('notes')
                    ->maxLength(65535)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\ImageColumn::make('image_identity')
                    ->circular(),
                Tables\Columns\TextColumn::make('first_name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('last_name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('phone')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('gender')
                    ->badge()
                    ->formatStateUsing(fn ($state): string => match ($state) {
                        'L' => 'Laki-laki',
                        'P' => 'Perempuan',
                        default => $state,
                    })
                    ->color(fn ($state): string => match ($state) {
                        'L' => 'success',
                        'P' => 'warning',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('mariage')
                    ->badge()
                    ->formatStateUsing(fn ($state): string => match ($state) {
                        'single' => 'Single',
                        'married' => 'Menikah',
                        'divorced' => 'Cerai',
                    })
                    ->color(fn ($state): string => match ($state) {
                        'single' => 'info',
                        'married' => 'success',
                        'divorced' => 'danger',
                    }),

                Tables\Columns\TextColumn::make('monthly_income')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('is_active')
                    ->badge()
                    ->formatStateUsing(fn ($state): string => match ($state) {
                        1 => 'Aktif',
                        0 => 'Pasif',
                    })
                    ->color(fn ($state): string => match ($state) {
                        1 => 'success',
                        0 => 'danger',
                    }),
                Tables\Columns\TextColumn::make('type_member')
                    ->badge()
                    ->formatStateUsing(fn ($state): string => match ($state) {
                        'regular' => 'Regular',
                        'premium' => 'Premium',
                    })
                    ->color(fn ($state): string => match ($state) {
                        'regular' => 'info',
                        'premium' => 'success',
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
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
            'index' => Pages\ListProfiles::route('/'),
            'create' => Pages\CreateProfile::route('/create'),
            'edit' => Pages\EditProfile::route('/{record}/edit'),
        ];
    }
}
