<?php

namespace App\Filament\User\Pages;

use Filament\Pages\Page;
use Filament\Infolists\Concerns\InteractsWithInfolists;
use Filament\Infolists\Contracts\HasInfolists;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\Grid;
use App\Models\Profile as ProfileModel;
use Illuminate\Support\Facades\Auth;

class Profile extends Page implements HasInfolists
{
    use InteractsWithInfolists;

    protected static ?string $navigationIcon = 'heroicon-o-user-circle';
    
    protected static ?string $navigationLabel = 'Profile Saya';
    
    protected static ?string $title = 'Profile Saya';

    protected static string $view = 'filament.user.pages.profile';
    
    protected static ?int $navigationSort = 100;

    public function profileInfolist(Infolist $infolist): Infolist
    {
        $profile = Auth::user()->profile;

        return $infolist
            ->record($profile)
            ->schema([
                Section::make('Informasi Pribadi')
                    ->icon('heroicon-o-user')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('first_name')
                                    ->label('Nama Depan')
                                    ->placeholder('-'),
                                
                                TextEntry::make('last_name')
                                    ->label('Nama Belakang')
                                    ->placeholder('-'),
                                
                                TextEntry::make('gender')
                                    ->label('Jenis Kelamin')
                                    ->placeholder('-')
                                    ->badge()
                                    ->color(fn (string $state): string => match ($state) {
                                        'Laki-laki' => 'info',
                                        'Perempuan' => 'danger',
                                        default => 'gray',
                                    }),
                                
                                TextEntry::make('birthday')
                                    ->label('Tanggal Lahir')
                                    ->date('d F Y')
                                    ->placeholder('-'),
                                
                                TextEntry::make('ibu_kandung')
                                    ->label('Nama Ibu Kandung')
                                    ->placeholder('-'),
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Identitas')
                    ->icon('heroicon-o-identification')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('sign_identity')
                                    ->label('Jenis Identitas')
                                    ->badge()
                                    ->placeholder('-'),
                                
                                TextEntry::make('no_identity')
                                    ->label('Nomor Identitas')
                                    ->placeholder('-')
                                    ->copyable()
                                    ->copyMessage('Nomor identitas disalin!'),
                            ]),
                        
                        ImageEntry::make('image_identity')
                            ->label('Foto Identitas')
                            ->placeholder('Tidak ada foto')
                            ->stacked()
                            ->limit(3)
                            ->limitedRemainingText(),

                        ImageEntry::make('avatar')
                            ->label('Foto Profile')
                            ->placeholder('Tidak ada foto')
                            ->circular()
                            ->size(100),
                    ])
                    ->collapsible(),

                Section::make('Kontak')
                    ->icon('heroicon-o-phone')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('email')
                                    ->label('Email')
                                    ->placeholder('-')
                                    ->icon('heroicon-o-envelope')
                                    ->copyable()
                                    ->copyMessage('Email disalin!'),
                                
                                TextEntry::make('phone')
                                    ->label('Nomor Telepon')
                                    ->placeholder('-')
                                    ->icon('heroicon-o-phone'),
                                
                                TextEntry::make('whatsapp')
                                    ->label('Nomor WhatsApp')
                                    ->placeholder('-')
                                    ->icon('heroicon-o-chat-bubble-left-ellipsis')
                                    ->url(fn ($state) => $state ? "https://wa.me/{$state}" : null)
                                    ->openUrlInNewTab(),
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Alamat')
                    ->icon('heroicon-o-map-pin')
                    ->schema([
                        TextEntry::make('address')
                            ->label('Alamat Lengkap')
                            ->placeholder('-')
                            ->columnSpanFull(),
                        
                        Grid::make(4)
                            ->schema([
                                TextEntry::make('province_id')
                                    ->label('ID Provinsi')
                                    ->placeholder('-'),
                                
                                TextEntry::make('city_id')
                                    ->label('ID Kota/Kabupaten')
                                    ->placeholder('-'),
                                
                                TextEntry::make('district_id')
                                    ->label('ID Kecamatan')
                                    ->placeholder('-'),
                                
                                TextEntry::make('village_id')
                                    ->label('ID Kelurahan/Desa')
                                    ->placeholder('-'),
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Pekerjaan & Status')
                    ->icon('heroicon-o-briefcase')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('job')
                                    ->label('Pekerjaan')
                                    ->placeholder('-'),
                                
                                TextEntry::make('mariage')
                                    ->label('Status Pernikahan')
                                    ->placeholder('-')
                                    ->badge()
                                    ->color(fn (string $state): string => match ($state) {
                                        'Menikah' => 'success',
                                        'Belum Menikah' => 'warning',
                                        'Cerai' => 'danger',
                                        default => 'gray',
                                    }),
                                
                                TextEntry::make('monthly_income')
                                    ->label('Penghasilan Bulanan')
                                    ->placeholder('-')
                                    ->money('IDR', locale: 'id'),
                                
                                TextEntry::make('type_member')
                                    ->label('Tipe Anggota')
                                    ->placeholder('-')
                                    ->badge()
                                    ->color(fn (?string $state): string => match ($state) {
                                        'VIP' => 'success',
                                        'Premium' => 'warning',
                                        'Regular' => 'gray',
                                        default => 'gray',
                                    }),

                                TextEntry::make('is_active')
                                    ->label('Status Aktif')
                                    ->placeholder('-')
                                    ->badge()
                                    ->formatStateUsing(fn ($state) => $state ? 'Aktif' : 'Tidak Aktif')
                                    ->color(fn ($state): string => $state ? 'success' : 'danger'),
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Informasi Tambahan')
                    ->icon('heroicon-o-document-text')
                    ->schema([
                        TextEntry::make('notes')
                            ->label('Catatan')
                            ->placeholder('-')
                            ->markdown(),
                        
                        TextEntry::make('remote_url')
                            ->label('URL Remote')
                            ->placeholder('-')
                            ->url(fn ($state) => $state)
                            ->openUrlInNewTab(),

                        TextEntry::make('created_at')
                            ->label('Terdaftar Sejak')
                            ->dateTime('d F Y, H:i')
                            ->placeholder('-'),
                    ])
                    ->collapsible()
                    ->collapsed(),
            ]);
    }
}
