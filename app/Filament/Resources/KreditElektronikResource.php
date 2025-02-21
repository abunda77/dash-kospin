<?php

namespace App\Filament\Resources;

use App\Filament\Resources\KreditElektronikResource\Pages;
use App\Filament\Resources\KreditElektronikResource\RelationManagers;
use App\Models\KreditElektronik;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Support\RawJs;
use Illuminate\Support\Facades\Log;
use Filament\Notifications\Notification;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Barryvdh\DomPDF\Facade\Pdf;

class KreditElektronikResource extends Resource
{
    protected static ?string $model = KreditElektronik::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Gadai & Kredit Elektronik';
    protected static ?string $navigationLabel = 'Kredit Elektronik';
    protected static ?string $pluralLabel = 'Kredit Elektronik';
    protected static ?string $pluralModelLabel = 'Kredit Elektronik';
    protected static ?string $modelLabel = 'Kredit Elektronik';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Kredit Elektronik')
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
                        Forms\Components\TextInput::make('uang_muka')
                            ->label('Uang Muka')
                            ->numeric()
                            ->required()
                            ->prefix('Rp')
                            ->placeholder('1000000')
                            ->formatStateUsing(function ($state) {
                                return $state ? number_format($state, 0, ',', '.') : '';
                            })
                            ->dehydrateStateUsing(fn ($state) => preg_replace('/[^0-9]/', '', $state)),
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
                        Forms\Components\Select::make('status_kredit')
                            ->label('Status Kredit')
                            ->options([
                                'aktif' => 'Aktif',
                                'lunas' => 'Lunas',
                                'macet' => 'Macet'
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
                Tables\Columns\TextColumn::make('uang_muka')
                    ->label('Uang Muka')
                    ->money('idr')
                    ->sortable(),
                Tables\Columns\TextColumn::make('nilai_hutang')
                    ->label('Nilai Hutang')
                    ->money('idr')
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
                Tables\Filters\SelectFilter::make('status_kredit')
                    ->options([
                        'aktif' => 'Aktif',
                        'lunas' => 'Lunas',
                        'macet' => 'Macet',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('print')
                    ->label('Cetak Kontrak')
                    ->icon('heroicon-o-printer')
                    ->action(function (KreditElektronik $record) {
                        $pdf = Pdf::loadView('pdf.kontrak-kredit', ['kredit' => $record]);
                        return response()->streamDownload(function () use ($pdf) {
                            echo $pdf->output();
                        }, 'kontrak-kredit-' . $record->kode_barang . '.pdf');
                    })
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
            'index' => Pages\ListKreditElektroniks::route('/'),
            'create' => Pages\CreateKreditElektronik::route('/create'),
            'view' => Pages\ViewKreditElektronik::route('/{record}'),
            'edit' => Pages\EditKreditElektronik::route('/{record}/edit'),
        ];
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Informasi Kredit Elektronik')
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
                        TextEntry::make('uang_muka')
                            ->label('Uang Muka')
                            ->money('idr'),
                        TextEntry::make('nilai_hutang')
                            ->label('Nilai Hutang')
                            ->money('idr'),
                        TextEntry::make('note')
                            ->label('Catatan')
                            ->markdown(),
                        TextEntry::make('status_kredit')
                            ->label('Status Kredit')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'aktif' => 'success',
                                'lunas' => 'info',
                                'macet' => 'danger',
                            }),
                    ])
                    ->columns(3)
            ]);
    }
}
