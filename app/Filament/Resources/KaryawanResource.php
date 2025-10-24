<?php

namespace App\Filament\Resources;

use App\Filament\Resources\KaryawanResource\Pages;
use App\Models\Karyawan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Support\RawJs;
use Filament\Tables;
use Filament\Tables\Table;
use Ysfkaya\FilamentPhoneInput\Forms\PhoneInput;

class KaryawanResource extends Resource
{
    protected static ?string $model = Karyawan::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationLabel = 'Data Karyawan';

    protected static ?string $title = 'Data Karyawan';

    public static function getNavigationGroup(): ?string
    {
        return 'Data Karyawan';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nik_karyawan')
                    // ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('first_name')
                    // ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('last_name')
                    // ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('tempat_lahir')
                    // ->required()
                    ->maxLength(255),
                Forms\Components\DatePicker::make('tanggal_lahir'),
                // ->required(),
                Forms\Components\Select::make('jenis_kelamin')
                    // ->required()
                    ->options([
                        'L' => 'Laki-laki',
                        'P' => 'Perempuan',
                    ]),
                Forms\Components\Select::make('status_pernikahan')
                    // ->required()
                    ->options([
                        'Kawin' => 'Kawin',
                        'Belum Kawin' => 'Belum Kawin',
                        'Cerai Hidup' => 'Cerai Hidup',
                        'Cerai Mati' => 'Cerai Mati',
                    ]),
                Forms\Components\Select::make('agama')
                    // ->required()
                    ->options([
                        'Islam' => 'Islam',
                        'Kristen' => 'Kristen',
                        'Katolik' => 'Katolik',
                        'Hindu' => 'Hindu',
                        'Buddha' => 'Buddha',
                        'Konghucu' => 'Konghucu',
                        'Lainnya' => 'Lainnya',
                    ]),
                Forms\Components\Select::make('golongan_darah')
                    ->options([
                        'A' => 'A',
                        'B' => 'B',
                        'AB' => 'AB',
                        'O' => 'O',
                    ]),
                Forms\Components\Textarea::make('alamat')
                    // ->required()
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('no_ktp')
                    // ->required()
                    ->maxLength(255),
                Forms\Components\FileUpload::make('foto_ktp')
                    ->image(),
                // ->required(),
                Forms\Components\TextInput::make('no_npwp')
                    ->maxLength(255),
                Forms\Components\FileUpload::make('foto_npwp')
                    ->image(),
                Forms\Components\TextInput::make('email')
                    ->email()
                    // ->required()
                    ->maxLength(255),
                PhoneInput::make('no_telepon')
                    ->defaultCountry('ID')
                    ->label('No. Telepon/ WhatsApp'),
                // ->required(),
                Forms\Components\FileUpload::make('foto_profil')
                    ->image(),
                // ->required(),
                Forms\Components\TextInput::make('kontak_darurat_nama')
                    // ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('kontak_darurat_hubungan')
                    ->label('Hubungan Kontak Darurat')
                    ->options([
                        'orang_tua' => 'Orang Tua',
                        'suami_istri' => 'Suami/Istri',
                        'anak' => 'Anak',
                        'saudara_kandung' => 'Saudara Kandung',
                        'paman_bibi' => 'Paman/Bibi',
                        'sepupu' => 'Sepupu',
                        'mertua' => 'Mertua',
                        'ipar' => 'Ipar',
                        'lainnya' => 'Lainnya',
                    ]),
                // ->required(),
                PhoneInput::make('kontak_darurat_telepon')
                    ->defaultCountry('ID')
                    ->label('No. Telepon Kontak Darurat'),
                // ->required(),
                Forms\Components\TextInput::make('nomor_pegawai')
                    // ->required()
                    ->maxLength(255),
                Forms\Components\DatePicker::make('tanggal_bergabung'),
                // ->required(),
                Forms\Components\Select::make('status_kepegawaian')
                    ->options([
                        'aktif' => 'Aktif',
                        'resign' => 'Resign',
                        'pensiun' => 'Pensiun',
                    ]),
                // ->required(),
                Forms\Components\Select::make('departemen')
                    ->options([
                        'direksi' => 'Direksi',
                        'operasional' => 'Operasional',
                        'keuangan' => 'Keuangan & Akuntansi',
                        'kredit' => 'Kredit & Pembiayaan',
                        'marketing' => 'Marketing & Bisnis',
                        'customer_service' => 'Customer Service',
                        'teller' => 'Teller',
                        'legal' => 'Legal & Compliance',
                        'audit' => 'Internal Audit',
                        'risk' => 'Risk Management',
                        'it' => 'Information Technology',
                        'sdm' => 'SDM & HRD',
                        'umum' => 'Umum & Logistik',
                        'collection' => 'Collection & Recovery',
                        'treasury' => 'Treasury',
                    ])
                    // ->required()
                    ->searchable(),
                Forms\Components\Select::make('jabatan')
                    ->options([
                        // Direksi
                        'direktur_utama' => 'Direktur Utama',
                        'direktur_operasional' => 'Direktur Operasional',
                        'direktur_keuangan' => 'Direktur Keuangan',
                        'direktur_kepatuhan' => 'Direktur Kepatuhan',

                        // Operasional
                        'kepala_operasional' => 'Kepala Operasional',
                        'supervisor_operasional' => 'Supervisor Operasional',
                        'staff_operasional' => 'Staff Operasional',

                        // Keuangan & Akuntansi
                        'kepala_keuangan' => 'Kepala Keuangan',
                        'manager_akuntansi' => 'Manager Akuntansi',
                        'staff_keuangan' => 'Staff Keuangan',
                        'staff_akuntansi' => 'Staff Akuntansi',

                        // Kredit & Pembiayaan
                        'kepala_kredit' => 'Kepala Kredit',
                        'analis_kredit' => 'Analis Kredit',
                        'account_officer' => 'Account Officer',
                        'staff_kredit' => 'Staff Kredit',

                        // Marketing & Bisnis
                        'kepala_marketing' => 'Kepala Marketing',
                        'relationship_manager' => 'Relationship Manager',
                        'marketing_officer' => 'Marketing Officer',
                        'sales_officer' => 'Sales Officer',

                        // Customer Service & Teller
                        'supervisor_layanan' => 'Supervisor Layanan',
                        'customer_service' => 'Customer Service',
                        'teller' => 'Teller',

                        // Legal & Compliance
                        'kepala_legal' => 'Kepala Legal',
                        'staff_legal' => 'Staff Legal',
                        'compliance_officer' => 'Compliance Officer',

                        // Internal Audit & Risk
                        'kepala_audit' => 'Kepala Audit Internal',
                        'auditor' => 'Auditor',
                        'risk_officer' => 'Risk Officer',

                        // IT
                        'kepala_it' => 'Kepala IT',
                        'programmer' => 'Programmer',
                        'system_analyst' => 'System Analyst',
                        'technical_support' => 'Technical Support',

                        // SDM & HRD
                        'kepala_sdm' => 'Kepala SDM',
                        'staff_hrd' => 'Staff HRD',
                        'recruitment_officer' => 'Recruitment Officer',

                        // Collection & Recovery
                        'kepala_collection' => 'Kepala Collection',
                        'collection_officer' => 'Collection Officer',
                        'recovery_officer' => 'Recovery Officer',
                    ])
                    // ->required()
                    ->searchable(),
                Forms\Components\Select::make('level_jabatan')
                    ->label('Level Jabatan')
                    ->options([
                        // Top Level Management
                        'board_of_director' => 'Board of Director',
                        'executive' => 'Executive Level',
                        'general_manager' => 'General Manager',

                        // Middle Management
                        'division_head' => 'Division Head',
                        'department_head' => 'Department Head',
                        'branch_manager' => 'Branch Manager',

                        // Lower Management
                        'supervisor' => 'Supervisor',
                        'section_head' => 'Section Head',
                        'unit_head' => 'Unit Head',

                        // Staff Level
                        'senior_staff' => 'Senior Staff',
                        'regular_staff' => 'Regular Staff',
                        'junior_staff' => 'Junior Staff',

                        // Entry Level
                        'trainee' => 'Management Trainee',
                        'probation' => 'Probation Staff',
                    ])
                    // ->required()
                    ->searchable(),
                Forms\Components\Select::make('lokasi_kerja')
                    ->label('Lokasi Kerja')
                    ->options([
                        // Jawa Barat
                        'bandung' => 'Bandung',
                        'bogor' => 'Bogor',
                        'bekasi' => 'Bekasi',
                        'depok' => 'Depok',

                        // Jakarta
                        'jakarta_pusat' => 'Jakarta Pusat',
                        'jakarta_utara' => 'Jakarta Utara',
                        'jakarta_barat' => 'Jakarta Barat',
                        'jakarta_selatan' => 'Jakarta Selatan',
                        'jakarta_timur' => 'Jakarta Timur',

                        // Jawa Tengah
                        'semarang' => 'Semarang',
                        'solo' => 'Solo',
                        'yogyakarta' => 'Yogyakarta',

                        // Jawa Timur
                        'surabaya' => 'Surabaya',
                        'malang' => 'Malang',
                        'sidoarjo' => 'Sidoarjo',
                    ])
                    // ->required()
                    ->searchable(),
                Forms\Components\TextInput::make('gaji_pokok')
                    ->label('Gaji Pokok (IDR)')
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
                    ->dehydrateStateUsing(fn ($state) => (int) str_replace(',', '', $state)),
                // ->required(),
                Forms\Components\Select::make('pendidikan_terakhir')
                    ->label('Pendidikan Terakhir')
                    ->options([
                        'sd' => 'SD/Sederajat',
                        'smp' => 'SMP/Sederajat',
                        'sma' => 'SMA/Sederajat',
                        'd1' => 'D1',
                        'd2' => 'D2',
                        'd3' => 'D3',
                        'd4' => 'D4',
                        's1' => 'S1',
                        's2' => 'S2',
                        's3' => 'S3',
                    ])
                    // ->required()
                    ->searchable(),
                Forms\Components\TextInput::make('nama_institusi')
                    // ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('jurusan')
                    // ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('tahun_lulus')
                    ->options(array_combine(range(2000, 2030), range(2000, 2030)))
                    // ->required()
                    ->searchable(),
                Forms\Components\TextInput::make('ipk')
                    ->numeric(),
                Forms\Components\Select::make('pengalaman_kerja')
                    ->label('Pengalaman Kerja')
                    ->options(array_combine(range(0, 10), array_map(function ($year) {
                        return $year.' Tahun';
                    }, range(0, 10))))
                    // ->required()
                    ->searchable(),
                Forms\Components\TextInput::make('keahlian'),
                Forms\Components\TextInput::make('sertifikasi'),
                Forms\Components\Select::make('nama_bank')
                    ->label('Nama Bank')
                    ->options([
                        'bca' => 'Bank Central Asia (BCA)',
                        'bni' => 'Bank Negara Indonesia (BNI)',
                        'bri' => 'Bank Rakyat Indonesia (BRI)',
                        'mandiri' => 'Bank Mandiri',
                        'cimb' => 'CIMB Niaga',
                        'danamon' => 'Bank Danamon',
                        'permata' => 'Bank Permata',
                        'btn' => 'Bank Tabungan Negara (BTN)',
                        'bsi' => 'Bank Syariah Indonesia (BSI)',
                        'mega' => 'Bank Mega',
                        'ocbc' => 'OCBC NISP',
                        'panin' => 'Panin Bank',
                        'uob' => 'UOB Indonesia',
                        'maybank' => 'Maybank Indonesia',
                        'other' => 'Bank Lainnya',
                    ])
                    // ->required()
                    ->searchable(),
                Forms\Components\TextInput::make('nomor_rekening')
                    // ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('nama_pemilik_rekening')
                    // ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('no_bpjs_kesehatan')
                    ->maxLength(255),
                Forms\Components\TextInput::make('no_bpjs_ketenagakerjaan')
                    ->maxLength(255),
                Forms\Components\Toggle::make('is_active'),
                // ->required(),
                Forms\Components\DatePicker::make('tanggal_keluar'),
                Forms\Components\TextInput::make('alasan_keluar')
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nik_karyawan')
                    ->searchable(),
                Tables\Columns\ImageColumn::make('foto_profil')
                    ->circular()
                    ->defaultImageUrl(url('/images/default-avatar.png')),
                Tables\Columns\TextColumn::make('first_name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('last_name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('tempat_lahir')
                    ->searchable(),
                Tables\Columns\TextColumn::make('tanggal_lahir')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('jenis_kelamin'),
                Tables\Columns\TextColumn::make('status_pernikahan')
                    ->searchable(),
                Tables\Columns\TextColumn::make('agama')
                    ->searchable(),
                Tables\Columns\TextColumn::make('golongan_darah')
                    ->searchable(),
                Tables\Columns\TextColumn::make('no_ktp')
                    ->searchable(),
                Tables\Columns\TextColumn::make('no_npwp')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('no_telepon')
                    ->searchable(),
                Tables\Columns\TextColumn::make('kontak_darurat_nama')
                    ->searchable(),
                Tables\Columns\TextColumn::make('kontak_darurat_hubungan')
                    ->searchable(),
                Tables\Columns\TextColumn::make('kontak_darurat_telepon')
                    ->searchable(),
                Tables\Columns\TextColumn::make('nomor_pegawai')
                    ->searchable(),
                Tables\Columns\TextColumn::make('tanggal_bergabung')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status_kepegawaian')
                    ->searchable(),
                Tables\Columns\TextColumn::make('departemen')
                    ->searchable(),
                Tables\Columns\TextColumn::make('jabatan')
                    ->searchable(),
                Tables\Columns\TextColumn::make('level_jabatan')
                    ->searchable(),
                Tables\Columns\TextColumn::make('lokasi_kerja')
                    ->searchable(),
                Tables\Columns\TextColumn::make('gaji_pokok')
                    ->money('IDR')
                    ->formatStateUsing(fn ($state) => $state ? $state : '-')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('pendidikan_terakhir')
                    ->searchable(),
                Tables\Columns\TextColumn::make('nama_institusi')
                    ->searchable(),
                Tables\Columns\TextColumn::make('jurusan')
                    ->searchable(),
                Tables\Columns\TextColumn::make('tahun_lulus')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('ipk')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('nama_bank')
                    ->searchable(),
                Tables\Columns\TextColumn::make('nomor_rekening')
                    ->searchable(),
                Tables\Columns\TextColumn::make('nama_pemilik_rekening')
                    ->searchable(),
                Tables\Columns\TextColumn::make('no_bpjs_kesehatan')
                    ->searchable(),
                Tables\Columns\TextColumn::make('no_bpjs_ketenagakerjaan')
                    ->searchable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),
                Tables\Columns\TextColumn::make('tanggal_keluar')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('alasan_keluar')
                    ->searchable(),
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
            'index' => Pages\ListKaryawans::route('/'),
            'create' => Pages\CreateKaryawan::route('/create'),
            'view' => Pages\ViewKaryawan::route('/{record}'),
            'edit' => Pages\EditKaryawan::route('/{record}/edit'),
        ];
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                // Section::make('Informasi Pribadi')
                //     ->schema([
                //         ImageEntry::make('foto_profil')
                //             ->circular(),
                //         TextEntry::make('nik_karyawan'),
                //         TextEntry::make('first_name')
                //             ->label('Nama Depan'),
                //         TextEntry::make('last_name')
                //             ->label('Nama Belakang'),
                //         TextEntry::make('tempat_lahir'),
                //         TextEntry::make('tanggal_lahir')
                //             ->date(),
                //         TextEntry::make('jenis_kelamin')
                //             ->state(function ($state) {
                //                 return match($state) {
                //                     'L' => 'Laki-laki',
                //                     'P' => 'Perempuan',
                //                     default => $state
                //                 };
                //             }),
                //         TextEntry::make('status_pernikahan'),
                //         TextEntry::make('agama'),
                //         TextEntry::make('golongan_darah'),
                //         TextEntry::make('alamat')->columnSpanFull(),
                //     ])->columns(2),

                // Section::make('Dokumen Identitas')
                //     ->schema([
                //         TextEntry::make('no_ktp')
                //             ->label('Nomor KTP'),
                //         ImageEntry::make('foto_ktp')
                //             ->label('Foto KTP')
                //             ->visible(fn ($state) => $state !== null),
                //         TextEntry::make('no_npwp')
                //             ->label('Nomor NPWP'),
                //         ImageEntry::make('foto_npwp')
                //             ->label('Foto NPWP')
                //             ->visible(fn ($state) => $state !== null),
                //     ])->columns(2),

                // Section::make('Kontak')
                //     ->schema([
                //         TextEntry::make('email'),
                //         TextEntry::make('no_telepon'),
                //         TextEntry::make('kontak_darurat_nama')
                //             ->label('Nama Kontak Darurat'),
                //         TextEntry::make('kontak_darurat_hubungan')
                //             ->label('Hubungan Kontak Darurat'),
                //         TextEntry::make('kontak_darurat_telepon')
                //             ->label('Telepon Kontak Darurat'),
                //     ])->columns(2),

                // Section::make('Informasi Kepegawaian')
                //     ->schema([
                //         TextEntry::make('nomor_pegawai'),
                //         TextEntry::make('tanggal_bergabung')
                //             ->date(),
                //         TextEntry::make('status_kepegawaian'),
                //         TextEntry::make('departemen'),
                //         TextEntry::make('jabatan'),
                //         TextEntry::make('level_jabatan'),
                //         TextEntry::make('lokasi_kerja'),
                //         TextEntry::make('gaji_pokok')
                //             ->money('IDR')
                //             ->formatStateUsing(fn ($state) => $state ? $state : '-'),
                //     ])->columns(2),

                // Section::make('Pendidikan')
                //     ->schema([
                //         TextEntry::make('pendidikan_terakhir'),
                //         TextEntry::make('nama_institusi'),
                //         TextEntry::make('jurusan'),
                //         TextEntry::make('tahun_lulus'),
                //         TextEntry::make('ipk'),
                //     ])->columns(2),

                // Section::make('Pengalaman & Keahlian')
                //     ->schema([
                //         TextEntry::make('pengalaman_kerja')
                //             ->label('Pengalaman Kerja (Tahun)'),
                //         TextEntry::make('keahlian'),
                //         TextEntry::make('sertifikasi'),
                //     ])->columns(2),

                // Section::make('Informasi Bank')
                //     ->schema([
                //         TextEntry::make('nama_bank'),
                //         TextEntry::make('nomor_rekening'),
                //         TextEntry::make('nama_pemilik_rekening'),
                //     ])->columns(2),

                // Section::make('BPJS')
                //     ->schema([
                //         TextEntry::make('no_bpjs_kesehatan')
                //             ->label('BPJS Kesehatan'),
                //         TextEntry::make('no_bpjs_ketenagakerjaan')
                //             ->label('BPJS Ketenagakerjaan'),
                //     ])->columns(2),

                // Section::make('Status Kepegawaian')
                //     ->schema([
                //         TextEntry::make('is_active')
                //             ->label('Status Aktif')
                //             ->badge()
                //             ->color(fn ($state): string => match ($state) {
                //                 true, 1, '1' => 'success',
                //                 false, 0, '0' => 'danger',
                //                 default => 'warning',
                //             })
                //             ->formatStateUsing(fn ($state): string => match ($state) {
                //                 true, 1, '1' => 'Aktif',
                //                 false, 0, '0' => 'Tidak Aktif',
                //                 default => 'Tidak Diketahui',
                //             }),
                //         TextEntry::make('tanggal_keluar')
                //             ->date()
                //             ->placeholder('-'),
                //         TextEntry::make('alasan_keluar')
                //             ->placeholder('-'),
                //     ])->columns(2),
            ]);
    }
}
