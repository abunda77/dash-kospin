<?php

namespace App\Filament\Resources;

use App\Enums\MetodePembayaranSetoran;
use App\Enums\StatusSetoran;
use App\Filament\Resources\SetoranTabunganResource\Pages;
use App\Models\SetoranTabungan;
use App\Services\MintaRevisiSetoranService;
use App\Services\MulaiReviewSetoranService;
use App\Services\PostingSetoranKeTabunganService;
use App\Services\SetujuiSetoranService;
use App\Services\TolakSetoranService;
use Dompdf\Dompdf;
use Dompdf\Options;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SetoranTabunganResource extends Resource
{
    protected static ?string $model = SetoranTabungan::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-path';

    protected static ?string $navigationLabel = 'Setoran Simpanan';

    protected static ?string $modelLabel = 'Setoran Simpanan';

    protected static ?string $pluralModelLabel = 'Setoran Simpanan';

    public static function getNavigationGroup(): ?string
    {
        return 'Tabungan';
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ])
            ->orderBy('created_at', 'desc');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nomor_setoran')->disabled(),
                Forms\Components\TextInput::make('jumlah')->disabled(),
                Forms\Components\TextInput::make('status')->disabled(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nomor_setoran')
                    ->label('Nomor Setoran')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Anggota')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('tabungan.no_tabungan')
                    ->label('Rekening')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('jenis_simpanan')
                    ->label('Jenis')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('metode_pembayaran')
                    ->label('Metode')
                    ->badge()
                    ->formatStateUsing(fn (MetodePembayaranSetoran $state): string => $state->label())
                    ->color(fn (MetodePembayaranSetoran $state): string => match ($state) {
                        MetodePembayaranSetoran::Qris => 'info',
                        MetodePembayaranSetoran::TransferRekening => 'success',
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('jumlah')
                    ->label('Nominal')
                    ->money('IDR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('kode_unik')
                    ->label('Kode Unik')
                    ->sortable(),

                Tables\Columns\TextColumn::make('jumlah_bayar')
                    ->label('Total')
                    ->money('IDR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('waktu_klaim_bayar')
                    ->label('Waktu Klaim')
                    ->dateTime()
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (StatusSetoran $state): string => match ($state) {
                        StatusSetoran::MENUNGGU_PEMBAYARAN => 'warning',
                        StatusSetoran::MENUNGGU_VERIFIKASI => 'info',
                        StatusSetoran::SEDANG_DIPERIKSA => 'primary',
                        StatusSetoran::PERLU_REVISI => 'danger',
                        StatusSetoran::DISETUJUI => 'success',
                        StatusSetoran::SELESAI => 'success',
                        StatusSetoran::DITOLAK => 'danger',
                        StatusSetoran::KEDALUWARSA => 'gray',
                        StatusSetoran::DIBATALKAN => 'gray',
                    })
                    ->formatStateUsing(fn (StatusSetoran $state): string => match ($state) {
                        StatusSetoran::MENUNGGU_PEMBAYARAN => 'Menunggu Pembayaran',
                        StatusSetoran::MENUNGGU_VERIFIKASI => 'Menunggu Verifikasi',
                        StatusSetoran::SEDANG_DIPERIKSA => 'Sedang Diperiksa',
                        StatusSetoran::PERLU_REVISI => 'Perlu Revisi',
                        StatusSetoran::DISETUJUI => 'Disetujui',
                        StatusSetoran::SELESAI => 'Selesai',
                        StatusSetoran::DITOLAK => 'Ditolak',
                        StatusSetoran::KEDALUWARSA => 'Kedaluwarsa',
                        StatusSetoran::DIBATALKAN => 'Dibatalkan',
                    })
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('pemeriksa.name')
                    ->label('Admin Pemeriksa')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Usia')
                    ->since()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('metode_pembayaran')
                    ->label('Metode Pembayaran')
                    ->options([
                        MetodePembayaranSetoran::Qris->value => MetodePembayaranSetoran::Qris->label(),
                        MetodePembayaranSetoran::TransferRekening->value => MetodePembayaranSetoran::TransferRekening->label(),
                    ]),

                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'menunggu_pembayaran' => 'Menunggu Pembayaran',
                        'menunggu_verifikasi' => 'Menunggu Verifikasi',
                        'sedang_diperiksa' => 'Sedang Diperiksa',
                        'perlu_revisi' => 'Perlu Revisi',
                        'disetujui' => 'Disetujui',
                        'selesai' => 'Selesai',
                        'ditolak' => 'Ditolak',
                        'kedaluwarsa' => 'Kedaluwarsa',
                        'dibatalkan' => 'Dibatalkan',
                    ]),

                Tables\Filters\TernaryFilter::make('is_terlambat')
                    ->label('Terlambat'),

                Tables\Filters\Filter::make('posting_gagal')
                    ->label('Posting Gagal (Status Disetujui)')
                    ->query(fn (Builder $query) => $query->where('status', StatusSetoran::DISETUJUI)),

                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\Action::make('cetakSlip')
                    ->label('Cetak Slip Setoran')
                    ->icon('heroicon-o-printer')
                    ->color('success')
                    ->visible(fn (SetoranTabungan $record) => in_array($record->status, [StatusSetoran::DISETUJUI, StatusSetoran::SELESAI]))
                    ->action(function (SetoranTabungan $record) {
                        try {
                            return static::cetakSlip($record);
                        } catch (\Throwable $e) {
                            Notification::make()
                                ->title('Terjadi kesalahan saat mencetak slip')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
                Tables\Actions\Action::make('mulaiReview')
                    ->label('Mulai Review')
                    ->icon('heroicon-o-play')
                    ->color('primary')
                    ->visible(fn (SetoranTabungan $record) => auth('admin')->user()->can('mulaiReview', $record) && $record->status === StatusSetoran::MENUNGGU_VERIFIKASI)
                    ->action(function (SetoranTabungan $record) {
                        try {
                            app(MulaiReviewSetoranService::class)->execute(auth('admin')->user(), $record);
                            Notification::make()
                                ->title('Proses review dimulai')
                                ->success()
                                ->send();
                        } catch (\Throwable $e) {
                            Notification::make()
                                ->title('Gagal memulai review')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
                Tables\Actions\Action::make('mintaRevisi')
                    ->label('Minta Revisi')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->visible(fn (SetoranTabungan $record) => auth('admin')->user()->can('mintaRevisi', $record) && $record->status === StatusSetoran::SEDANG_DIPERIKSA)
                    ->form([
                        Forms\Components\Textarea::make('catatan_verifikasi')
                            ->label('Alasan / Catatan Revisi')
                            ->required()
                            ->minLength(3),
                    ])
                    ->action(function (SetoranTabungan $record, array $data) {
                        try {
                            app(MintaRevisiSetoranService::class)->execute(auth('admin')->user(), $record, $data['catatan_verifikasi']);
                            Notification::make()
                                ->title('Permintaan revisi dikirim')
                                ->success()
                                ->send();
                        } catch (\Throwable $e) {
                            Notification::make()
                                ->title('Gagal mengirim permintaan revisi')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
                Tables\Actions\Action::make('tolak')
                    ->label('Tolak')
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->visible(fn (SetoranTabungan $record) => auth('admin')->user()->can('tolak', $record) && in_array($record->status, [StatusSetoran::MENUNGGU_VERIFIKASI, StatusSetoran::SEDANG_DIPERIKSA, StatusSetoran::PERLU_REVISI]))
                    ->form([
                        Forms\Components\Textarea::make('alasan_penolakan')
                            ->label('Alasan Penolakan')
                            ->required()
                            ->minLength(3),
                    ])
                    ->action(function (SetoranTabungan $record, array $data) {
                        try {
                            app(TolakSetoranService::class)->execute(auth('admin')->user(), $record, $data['alasan_penolakan']);
                            Notification::make()
                                ->title('Setoran ditolak')
                                ->success()
                                ->send();
                        } catch (\Throwable $e) {
                            Notification::make()
                                ->title('Gagal menolak setoran')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
                Tables\Actions\Action::make('setujui')
                    ->label('Setujui')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->visible(fn (SetoranTabungan $record) => auth('admin')->user()->can('setujui', $record) && $record->status === StatusSetoran::SEDANG_DIPERIKSA)
                    ->form([
                        Forms\Components\TextInput::make('referensi_transaksi_provider')
                            ->label('Referensi Transaksi Provider'),
                        Forms\Components\DateTimePicker::make('waktu_bayar_provider')
                            ->label('Waktu Bayar Provider'),
                        Forms\Components\TextInput::make('nama_pembayar_provider')
                            ->label('Nama Pembayar Provider'),
                        Forms\Components\Textarea::make('catatan_verifikasi')
                            ->label('Catatan Verifikasi'),
                    ])
                    ->action(function (SetoranTabungan $record, array $data) {
                        try {
                            app(SetujuiSetoranService::class)->execute(
                                auth('admin')->user(),
                                $record,
                                $data['referensi_transaksi_provider'] ?? null,
                                $data['waktu_bayar_provider'] ? Carbon::parse($data['waktu_bayar_provider']) : null,
                                $data['nama_pembayar_provider'] ?? null,
                                $data['catatan_verifikasi'] ?? null
                            );
                            Notification::make()
                                ->title('Setoran disetujui')
                                ->success()
                                ->send();
                        } catch (\Throwable $e) {
                            Notification::make()
                                ->title('Gagal menyetujui setoran')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
                Tables\Actions\Action::make('cobaUlangPosting')
                    ->label('Coba Ulang Posting')
                    ->icon('heroicon-o-arrow-path')
                    ->color('info')
                    ->visible(fn (SetoranTabungan $record) => auth('admin')->user()->can('cobaUlangPosting', $record) && $record->status === StatusSetoran::DISETUJUI)
                    ->action(function (SetoranTabungan $record) {
                        try {
                            app(PostingSetoranKeTabunganService::class)->execute($record->id, auth('admin')->user()->id);
                            Notification::make()
                                ->title('Posting saldo berhasil')
                                ->success()
                                ->send();
                        } catch (\Throwable $e) {
                            Notification::make()
                                ->title('Gagal posting saldo')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([]),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Data Anggota')
                    ->schema([
                        Infolists\Components\TextEntry::make('user.name')
                            ->label('Nama Anggota'),
                        Infolists\Components\TextEntry::make('user.email')
                            ->label('Email Anggota'),
                    ])->columns(2),

                Infolists\Components\Section::make('Data Rekening Tabungan')
                    ->schema([
                        Infolists\Components\TextEntry::make('tabungan.no_tabungan')
                            ->label('Nomor Rekening'),
                        Infolists\Components\TextEntry::make('tabungan.produkTabungan.nama_produk')
                            ->label('Produk Tabungan'),
                    ])->columns(2),

                Infolists\Components\Section::make('Detail Setoran')
                    ->schema([
                        Infolists\Components\TextEntry::make('nomor_setoran')
                            ->label('Nomor Setoran'),
                        Infolists\Components\TextEntry::make('jenis_simpanan')
                            ->label('Jenis Simpanan'),
                        Infolists\Components\TextEntry::make('metode_pembayaran')
                            ->label('Metode Pembayaran')
                            ->badge()
                            ->formatStateUsing(fn (MetodePembayaranSetoran $state): string => $state->label()),
                        Infolists\Components\TextEntry::make('jumlah')
                            ->label('Nominal')
                            ->money('IDR'),
                        Infolists\Components\TextEntry::make('kode_unik')
                            ->label('Kode Unik'),
                        Infolists\Components\TextEntry::make('jumlah_bayar')
                            ->label('Total Bayar')
                            ->money('IDR'),
                        Infolists\Components\TextEntry::make('is_terlambat')
                            ->label('Terlambat')
                            ->badge()
                            ->color(fn ($state) => $state ? 'danger' : 'success')
                            ->formatStateUsing(fn ($state) => $state ? 'Ya' : 'Tidak'),
                        Infolists\Components\TextEntry::make('status')
                            ->label('Status')
                            ->badge()
                            ->color(fn (StatusSetoran $state): string => match ($state) {
                                StatusSetoran::MENUNGGU_PEMBAYARAN => 'warning',
                                StatusSetoran::MENUNGGU_VERIFIKASI => 'info',
                                StatusSetoran::SEDANG_DIPERIKSA => 'primary',
                                StatusSetoran::PERLU_REVISI => 'danger',
                                StatusSetoran::DISETUJUI => 'success',
                                StatusSetoran::SELESAI => 'success',
                                StatusSetoran::DITOLAK => 'danger',
                                StatusSetoran::KEDALUWARSA => 'gray',
                                StatusSetoran::DIBATALKAN => 'gray',
                            })
                            ->formatStateUsing(fn (StatusSetoran $state): string => match ($state) {
                                StatusSetoran::MENUNGGU_PEMBAYARAN => 'Menunggu Pembayaran',
                                StatusSetoran::MENUNGGU_VERIFIKASI => 'Menunggu Verifikasi',
                                StatusSetoran::SEDANG_DIPERIKSA => 'Sedang Diperiksa',
                                StatusSetoran::PERLU_REVISI => 'Perlu Revisi',
                                StatusSetoran::DISETUJUI => 'Disetujui',
                                StatusSetoran::SELESAI => 'Selesai',
                                StatusSetoran::DITOLAK => 'Ditolak',
                                StatusSetoran::KEDALUWARSA => 'Kedaluwarsa',
                                StatusSetoran::DIBATALKAN => 'Dibatalkan',
                            }),
                        Infolists\Components\TextEntry::make('created_at')
                            ->label('Waktu Dibuat')
                            ->dateTime(),
                    ])->columns(3),

                Infolists\Components\Section::make('Data QRIS')
                    ->schema([
                        Infolists\Components\TextEntry::make('qris_payload')
                            ->label('QRIS Payload')
                            ->limit(30),
                        Infolists\Components\TextEntry::make('qris_image_path')
                            ->label('Path Gambar QRIS')
                            ->hintAction(
                                Infolists\Components\Actions\Action::make('downloadQris')
                                    ->label('Download QRIS')
                                    ->icon('heroicon-o-arrow-down-tray')
                                    ->visible(fn ($record) => ! empty($record->qris_image_path))
                                    ->action(function ($record) {
                                        if ($record->qris_image_path && Storage::disk('public')->exists($record->qris_image_path)) {
                                            return Storage::disk('public')->download($record->qris_image_path);
                                        }
                                        Notification::make()
                                            ->title('File QRIS tidak ditemukan')
                                            ->danger()
                                            ->send();
                                    })
                            ),
                        Infolists\Components\TextEntry::make('qris_dibuat_at')
                            ->label('QRIS Dibuat Pada')
                            ->dateTime(),
                        Infolists\Components\TextEntry::make('kedaluwarsa_at')
                            ->label('Kedaluwarsa Pada')
                            ->dateTime(),
                    ])
                    ->columns(2)
                    ->visible(fn ($record) => $record?->metode_pembayaran === MetodePembayaranSetoran::Qris),

                Infolists\Components\Section::make('Bukti & Data Klaim')
                    ->schema([
                        Infolists\Components\TextEntry::make('nama_pembayar')
                            ->label('Nama Pembayar (Klaim)'),
                        Infolists\Components\TextEntry::make('referensi_pembayaran')
                            ->label('Referensi Pembayaran (Klaim)'),
                        Infolists\Components\TextEntry::make('waktu_klaim_bayar')
                            ->label('Waktu Klaim Bayar')
                            ->dateTime(),
                        Infolists\Components\TextEntry::make('dikirim_at')
                            ->label('Waktu Dikirim')
                            ->dateTime(),
                        Infolists\Components\TextEntry::make('bukti_pembayaran_path')
                            ->label('Path Bukti Pembayaran')
                            ->hintAction(
                                Infolists\Components\Actions\Action::make('downloadBuktiUtama')
                                    ->label('Download')
                                    ->icon('heroicon-o-arrow-down-tray')
                                    ->visible(fn ($record) => ! empty($record->bukti_pembayaran_path))
                                    ->action(function ($record) {
                                        if ($record->bukti_pembayaran_path && Storage::disk('private')->exists($record->bukti_pembayaran_path)) {
                                            return Storage::disk('private')->download($record->bukti_pembayaran_path);
                                        }
                                        Notification::make()
                                            ->title('File tidak ditemukan')
                                            ->danger()
                                            ->send();
                                    })
                            ),
                    ])->columns(2),

                Infolists\Components\Section::make('Riwayat Bukti Pengiriman')
                    ->schema([
                        Infolists\Components\RepeatableEntry::make('buktiSetoran')
                            ->label('')
                            ->schema([
                                Infolists\Components\TextEntry::make('nama_asli')
                                    ->label('Nama File'),
                                Infolists\Components\TextEntry::make('ukuran_file')
                                    ->label('Ukuran')
                                    ->formatStateUsing(fn ($state) => number_format($state / 1024, 2).' KB'),
                                Infolists\Components\TextEntry::make('is_terkini')
                                    ->label('Terkini')
                                    ->badge()
                                    ->color(fn ($state) => $state ? 'success' : 'gray')
                                    ->formatStateUsing(fn ($state) => $state ? 'Ya' : 'Tidak'),
                                Infolists\Components\TextEntry::make('file_path')
                                    ->label('Path File')
                                    ->hintAction(
                                        Infolists\Components\Actions\Action::make('downloadHistoryBukti')
                                            ->label('Download')
                                            ->icon('heroicon-o-arrow-down-tray')
                                            ->action(function ($record) {
                                                if ($record && $record->file_path && Storage::disk('private')->exists($record->file_path)) {
                                                    return Storage::disk('private')->download($record->file_path, $record->nama_asli);
                                                }
                                                Notification::make()
                                                    ->title('File tidak ditemukan')
                                                    ->danger()
                                                    ->send();
                                            })
                                    ),
                            ])->columns(4),
                    ]),

                Infolists\Components\Section::make('Informasi Provider')
                    ->schema([
                        Infolists\Components\TextEntry::make('referensi_transaksi_provider')
                            ->label('Referensi Transaksi Provider'),
                        Infolists\Components\TextEntry::make('waktu_bayar_provider')
                            ->label('Waktu Bayar Provider')
                            ->dateTime(),
                        Infolists\Components\TextEntry::make('nama_pembayar_provider')
                            ->label('Nama Pembayar Provider'),
                    ])->columns(3),

                Infolists\Components\Section::make('Transaksi Tabungan Terkait')
                    ->schema([
                        Infolists\Components\TextEntry::make('transaksiTabungan.kode_transaksi')
                            ->label('Kode Transaksi'),
                        Infolists\Components\TextEntry::make('transaksiTabungan.keterangan')
                            ->label('Keterangan'),
                        Infolists\Components\TextEntry::make('transaksiTabungan.jumlah')
                            ->label('Jumlah Posting')
                            ->money('IDR'),
                        Infolists\Components\TextEntry::make('transaksiTabungan.tanggal_transaksi')
                            ->label('Tanggal Posting')
                            ->dateTime(),
                    ])
                    ->columns(2)
                    ->visible(fn ($record) => $record && $record->transaksiTabungan()->exists()),

                Infolists\Components\Section::make('Catatan Pembayaran & Pemeriksaan')
                    ->schema([
                        Infolists\Components\TextEntry::make('catatan_pengguna')
                            ->label('Catatan Pengguna / Anggota')
                            ->columnSpanFull(),
                        Infolists\Components\TextEntry::make('pemeriksa.name')
                            ->label('Diperiksa Oleh'),
                        Infolists\Components\TextEntry::make('mulai_review_at')
                            ->label('Mulai Review')
                            ->dateTime(),
                        Infolists\Components\TextEntry::make('direview_at')
                            ->label('Direview Pada')
                            ->dateTime(),
                        Infolists\Components\TextEntry::make('catatan_verifikasi')
                            ->label('Catatan Verifikasi')
                            ->columnSpanFull(),
                        Infolists\Components\TextEntry::make('disetujui_at')
                            ->label('Disetujui Pada')
                            ->dateTime(),
                        Infolists\Components\TextEntry::make('penolak.name')
                            ->label('Ditolak Oleh'),
                        Infolists\Components\TextEntry::make('ditolak_at')
                            ->label('Ditolak Pada')
                            ->dateTime(),
                        Infolists\Components\TextEntry::make('alasan_penolakan')
                            ->label('Alasan Penolakan')
                            ->columnSpanFull(),
                        Infolists\Components\TextEntry::make('diposting_at')
                            ->label('Diposting Pada')
                            ->dateTime(),
                        Infolists\Components\TextEntry::make('selesai_at')
                            ->label('Selesai Pada')
                            ->dateTime(),
                    ])->columns(3),

                Infolists\Components\Section::make('Riwayat Perubahan Status')
                    ->schema([
                        Infolists\Components\RepeatableEntry::make('riwayatStatus')
                            ->label('')
                            ->schema([
                                Infolists\Components\TextEntry::make('status_sebelumnya')
                                    ->label('Sebelumnya')
                                    ->badge()
                                    ->color('gray')
                                    ->formatStateUsing(fn ($state) => $state instanceof StatusSetoran ? str($state->value)->replace('_', ' ')->title() : str($state)->replace('_', ' ')->title()),
                                Infolists\Components\TextEntry::make('status_baru')
                                    ->label('Baru')
                                    ->badge()
                                    ->color('success')
                                    ->formatStateUsing(fn ($state) => $state instanceof StatusSetoran ? str($state->value)->replace('_', ' ')->title() : str($state)->replace('_', ' ')->title()),
                                Infolists\Components\TextEntry::make('catatan')
                                    ->label('Catatan'),
                                Infolists\Components\TextEntry::make('created_at')
                                    ->label('Waktu')
                                    ->dateTime(),
                            ])->columns(4),
                    ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSetoranTabungans::route('/'),
            'view' => Pages\ViewSetoranTabungan::route('/{record}'),
        ];
    }

    public static function cetakSlip(SetoranTabungan $setoran): StreamedResponse
    {
        $options = new Options;
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isPhpEnabled', true);
        $options->set('defaultFont', 'Arial');
        $options->set('isRemoteEnabled', true);
        $options->set('chroot', public_path());

        $dompdf = new Dompdf($options);
        $dompdf->setPaper([0, 0, 368.504, 510.236], 'portrait');

        $html = view('pdf.slip-setoran', [
            'setoran' => $setoran,
        ])->render();

        $dompdf->loadHtml($html);
        $dompdf->render();

        $filename = 'slip_setoran_'.$setoran->nomor_setoran.'_'.date('Y-m-d_H-i-s').'.pdf';

        return response()->streamDownload(
            function () use ($dompdf) {
                echo $dompdf->output();
            },
            $filename,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="'.$filename.'"',
            ]
        );
    }
}
