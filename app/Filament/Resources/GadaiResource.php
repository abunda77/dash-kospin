<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Gadai;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Support\RawJs;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\GadaiResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\GadaiResource\RelationManagers;
use Filament\Notifications\Notification;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;

class GadaiResource extends Resource
{
    protected static ?string $model = Gadai::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Gadai & Kredit Elektronik';
    protected static ?string $navigationLabel = 'Gadai';
    protected static ?string $pluralLabel = 'Gadai';
    protected static ?string $pluralModelLabel = 'Gadai';
    protected static ?string $modelLabel = 'Gadai';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Gadai')
                    ->schema([
                        Forms\Components\Select::make('pinjaman_id')
                            ->label('Pinjaman')
                            ->relationship('pinjaman', 'no_pinjaman')
                            ->searchable()
                            ->required(),
                        Forms\Components\TextInput::make('kode_barang')
                            ->label('Kode Barang')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->helperText('Masukkan IMEI, No Seri, No.Barcode pada barang'),
                        Forms\Components\TextInput::make('nama_barang')
                            ->label('Nama Barang')
                            ->required(),
                        Forms\Components\Select::make('jenis_barang')
                            ->label('Jenis Barang')
                            ->options([
                                'handphone' => 'Handphone',
                                'tablet' => 'Tablet',
                                'laptop' => 'Laptop',
                                'komputer' => 'Komputer',
                                'tv' => 'TV',
                                'jam_tangan' => 'Jam Tangan',
                                'lainnya' => 'Lainnya'
                            ])
                            ->required(),
                        Forms\Components\Select::make('merk')
                            ->label('Merk')
                            ->options([
                                'samsung' => 'Samsung',
                                'apple' => 'Apple',
                                'xiaomi' => 'Xiaomi',
                                'oppo' => 'OPPO',
                                'vivo' => 'Vivo',
                                'realme' => 'Realme',
                                'infinix' => 'Infinix',
                                'tecno' => 'TECNO',
                                'asus' => 'ASUS',
                                'acer' => 'Acer',
                                'lenovo' => 'Lenovo',
                                'hp' => 'HP',
                                'dell' => 'Dell',
                                'lg' => 'LG',
                                'sony' => 'Sony',
                                'panasonic' => 'Panasonic',
                                'sharp' => 'Sharp',
                                'polytron' => 'Polytron',
                                'lainnya' => 'Lainnya'
                            ])
                            ->required(),
                        Forms\Components\Select::make('tipe')
                            ->label('Tipe')
                            ->options([
                                'entry-mode' => 'Entry Mode',
                                'mid-range' => 'Mid Range', 
                                'high-end' => 'High End',
                                'premium' => 'Premium',
                                'flagship' => 'Flagship',
                                'smartphone' => 'Smartphone',
                                'smartphone_mini' => 'Smartphone Mini',
                                'smartphone_medium' => 'Smartphone Medium',
                                'smartphone_large' => 'Smartphone Large',
                                'smartphone_plus' => 'Smartphone Plus',
                                'smartphone_pro' => 'Smartphone Pro',
                                'smartphone_ultra' => 'Smartphone Ultra',
                                'lainnya' => 'Lainnya'
                            ])
                            ->required(),
                        Forms\Components\Select::make('tahun_pembuatan')
                            ->label('Tahun Pembuatan')
                            ->options([
                                date('Y') => date('Y'),
                                date('Y')-1 => date('Y')-1,
                                date('Y')-2 => date('Y')-2,
                                date('Y')-3 => date('Y')-3,
                                date('Y')-4 => date('Y')-4,
                                date('Y')-5 => date('Y')-5,
                            ])
                            ->required(),
                        Forms\Components\Select::make('kondisi')
                            ->label('Kondisi')
                            ->options([
                                'baru' => 'Baru',
                                'bekas' => 'Bekas'
                            ])
                            ->required(),
                        Forms\Components\Textarea::make('kelengkapan')
                            ->label('Kelengkapan')
                            ->helperText('Isi kelengakapn barang seperti: Dus, charger, nota, dan lain sebagainya')
                            ->required(),
                        Forms\Components\TextInput::make('harga_barang')
                            ->label('Harga Barang')
                            ->numeric()
                            ->required()
                            ->prefix('Rp')
                            ->placeholder('1000000')
                            ->formatStateUsing(function ($state) {
                                return $state ? number_format($state, 0, ',', '.') : '';
                            })
                            ->dehydrateStateUsing(fn ($state) => preg_replace('/[^0-9]/', '', $state)),
                        Forms\Components\Select::make('nilai_taksasi')
                            ->label('Nilai Taksasi')
                            ->options([
                                '30' => '30%',
                                '35' => '35%',
                                '40' => '40%',
                                '45' => '45%',
                                '50' => '50%',
                                '55' => '55%',
                                '60' => '60%',
                                '65' => '65%',
                                '70' => '70%',
                                '75' => '75%',
                                '80' => '80%'
                            ])
                            ->required(),
                        Forms\Components\TextInput::make('nilai_hutang')
                            ->label('Nilai Hutang')
                            ->prefix('Rp')
                            ->disabled(false)
                            ->dehydrated()
                            ->numeric()
                            ->helperText(function ($get, $state) {
                                $pinjaman = \App\Models\Pinjaman::find($get('pinjaman_id'));
                                if (!$pinjaman) return null;

                                $nilaiHutang = (int) preg_replace('/[^0-9]/', '', $state);
                                $jumlahPinjaman = $pinjaman->jumlah_pinjaman;

                                $status = $nilaiHutang === $jumlahPinjaman ? 'success' : 'danger';
                                $message = "Jumlah Pinjaman: Rp " . number_format($jumlahPinjaman, 0, ',', '.');

                                if ($status === 'danger') {
                                    Notification::make()
                                        ->danger()
                                        ->title('Peringatan!')
                                        ->body('Nilai hutang harus sama dengan jumlah pinjaman')
                                        ->persistent()
                                        ->send();
                                }

                                return new \Illuminate\Support\HtmlString(
                                    "<span class='fi-help-text text-{$status}-600'>{$message}</span>"
                                );
                            }),
                        Forms\Components\Textarea::make('note')
                            ->label('Catatan')
                            ->helperText('Berikan catatan PENTING untuk keadaan barang'),
                        Forms\Components\Select::make('status_gadai')
                            ->label('Status Gadai')
                            ->options([
                                'aktif' => 'Aktif',
                                'lunas' => 'Lunas',
                                'lelang' => 'Lelang'
                            ])
                            ->default('aktif')
                            ->required()
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
                    ->columns([
                        Tables\Columns\TextColumn::make('pinjaman.no_pinjaman')
                            ->label('No. Pinjaman')
                            ->searchable()
                            ->sortable(),
                        Tables\Columns\TextColumn::make('kode_barang')
                            ->searchable()
                            ->sortable(),
                        Tables\Columns\TextColumn::make('nama_barang')
                            ->searchable()
                            ->sortable(),
                        Tables\Columns\TextColumn::make('harga_barang')
                            ->label('Nilai Barang')
                            ->money('idr')
                            ->sortable(),
                        Tables\Columns\TextColumn::make('nilai_hutang')
                            ->label('Nilai Hutang')
                            ->money('idr')
                            ->sortable(),
                        Tables\Columns\TextColumn::make('nilai_taksasi')
                            ->label('Nilai Taksasi')
                            ->suffix('%')
                            ->sortable(),
                        Tables\Columns\TextColumn::make('jenis_barang')
                            ->searchable()
                            ->sortable(),
                        Tables\Columns\TextColumn::make('merk')
                            ->searchable()
                            ->sortable(),
                        Tables\Columns\TextColumn::make('tipe')
                            ->searchable(),
                        Tables\Columns\TextColumn::make('tahun_pembuatan')
                            ->sortable(),
                        Tables\Columns\TextColumn::make('kondisi'),
                        Tables\Columns\TextColumn::make('kelengkapan'),
                    ])
            ->filters([
                Tables\Filters\SelectFilter::make('status_gadai')
                    ->options([
                        'aktif' => 'Aktif',
                        'lunas' => 'Lunas',
                        'lelang' => 'Lelang',
                    ]),
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
            'index' => Pages\ListGadais::route('/'),
            'create' => Pages\CreateGadai::route('/create'),
            'view' => Pages\ViewGadai::route('/{record}'),
            'edit' => Pages\EditGadai::route('/{record}/edit'),
        ];
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Informasi Gadai')
                    ->schema([
                        TextEntry::make('pinjaman.no_pinjaman')
                            ->label('No. Pinjaman'),
                        TextEntry::make('kode_barang')
                            ->label('Kode Barang'),
                        TextEntry::make('nama_barang')
                            ->label('Nama Barang'),
                        TextEntry::make('jenis_barang')
                            ->label('Jenis Barang'),
                        TextEntry::make('merk')
                            ->label('Merk'),
                        TextEntry::make('tipe')
                            ->label('Tipe'),
                        TextEntry::make('tahun_pembuatan')
                            ->label('Tahun Pembuatan'),
                        TextEntry::make('kondisi')
                            ->label('Kondisi'),
                        TextEntry::make('kelengkapan')
                            ->label('Kelengkapan')
                            ->markdown(),
                        TextEntry::make('harga_barang')
                            ->label('Harga Barang')
                            ->money('idr'),
                        TextEntry::make('nilai_taksasi')
                            ->label('Nilai Taksasi')
                            ->suffix('%'),
                        TextEntry::make('nilai_hutang')
                            ->label('Nilai Hutang')
                            ->money('idr'),
                        TextEntry::make('note')
                            ->label('Catatan')
                            ->markdown(),
                        TextEntry::make('status_gadai')
                            ->label('Status Gadai')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'aktif' => 'success',
                                'lunas' => 'info',
                                'lelang' => 'danger',
                            }),
                    ])
                    ->columns(3)
            ]);
    }
}
