<?php

namespace App\Filament\Resources;

use App\Enums\StatusPenarikan;
use App\Filament\Resources\PenarikanTabunganResource\Pages;
use App\Models\PenarikanTabungan;
use App\Services\MintaRevisiPenarikanService;
use App\Services\MulaiReviewPenarikanService;
use App\Services\PostingPenarikanKeTabunganService;
use App\Services\SetujuiPenarikanService;
use App\Services\TolakPenarikanService;
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

class PenarikanTabunganResource extends Resource
{
    protected static ?string $model = PenarikanTabungan::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationLabel = 'Penarikan Simpanan';

    protected static ?string $modelLabel = 'Penarikan Simpanan';

    protected static ?string $pluralModelLabel = 'Penarikan Simpanan';

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
                Forms\Components\TextInput::make('nomor_penarikan')->disabled(),
                Forms\Components\TextInput::make('jumlah')->disabled(),
                Forms\Components\TextInput::make('status')->disabled(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nomor_penarikan')
                    ->label('Nomor Penarikan')
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

                Tables\Columns\TextColumn::make('jumlah')
                    ->label('Nominal')
                    ->money('IDR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('bank')
                    ->label('Bank')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('nama_bank')
                    ->label('Nama Bank')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('nama_nasabah')
                    ->label('Nama Nasabah')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('dikirim_at')
                    ->label('Waktu Pengajuan')
                    ->dateTime()
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (StatusPenarikan $state): string => match ($state) {
                        StatusPenarikan::MENUNGGU_VERIFIKASI => 'info',
                        StatusPenarikan::SEDANG_DIPERIKSA => 'primary',
                        StatusPenarikan::PERLU_REVISI => 'danger',
                        StatusPenarikan::DISETUJUI => 'success',
                        StatusPenarikan::SELESAI => 'success',
                        StatusPenarikan::DITOLAK => 'danger',
                        StatusPenarikan::DIBATALKAN => 'gray',
                    })
                    ->formatStateUsing(fn (StatusPenarikan $state): string => match ($state) {
                        StatusPenarikan::MENUNGGU_VERIFIKASI => 'Menunggu Verifikasi',
                        StatusPenarikan::SEDANG_DIPERIKSA => 'Sedang Diperiksa',
                        StatusPenarikan::PERLU_REVISI => 'Perlu Revisi',
                        StatusPenarikan::DISETUJUI => 'Disetujui',
                        StatusPenarikan::SELESAI => 'Selesai',
                        StatusPenarikan::DITOLAK => 'Ditolak',
                        StatusPenarikan::DIBATALKAN => 'Dibatalkan',
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
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'menunggu_verifikasi' => 'Menunggu Verifikasi',
                        'sedang_diperiksa' => 'Sedang Diperiksa',
                        'perlu_revisi' => 'Perlu Revisi',
                        'disetujui' => 'Disetujui',
                        'selesai' => 'Selesai',
                        'ditolak' => 'Ditolak',
                        'dibatalkan' => 'Dibatalkan',
                    ]),

                Tables\Filters\Filter::make('posting_gagal')
                    ->label('Posting Gagal (Status Disetujui)')
                    ->query(fn (Builder $query) => $query->where('status', StatusPenarikan::DISETUJUI)),

                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\Action::make('cetakSlip')
                    ->label('Cetak Slip Penarikan')
                    ->icon('heroicon-o-printer')
                    ->color('success')
                    ->visible(fn (PenarikanTabungan $record) => in_array($record->status, [StatusPenarikan::DISETUJUI, StatusPenarikan::SELESAI]))
                    ->action(function (PenarikanTabungan $record) {
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
                    ->visible(fn (PenarikanTabungan $record) => auth('admin')->user()->can('mulaiReview', $record) && $record->status === StatusPenarikan::MENUNGGU_VERIFIKASI)
                    ->action(function (PenarikanTabungan $record) {
                        try {
                            app(MulaiReviewPenarikanService::class)->execute(auth('admin')->user(), $record);
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
                    ->visible(fn (PenarikanTabungan $record) => auth('admin')->user()->can('mintaRevisi', $record) && $record->status === StatusPenarikan::SEDANG_DIPERIKSA)
                    ->form([
                        Forms\Components\Textarea::make('catatan_verifikasi')
                            ->label('Alasan / Catatan Revisi')
                            ->required()
                            ->minLength(3),
                    ])
                    ->action(function (PenarikanTabungan $record, array $data) {
                        try {
                            app(MintaRevisiPenarikanService::class)->execute(auth('admin')->user(), $record, $data['catatan_verifikasi']);
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
                    ->visible(fn (PenarikanTabungan $record) => auth('admin')->user()->can('tolak', $record) && in_array($record->status, [StatusPenarikan::MENUNGGU_VERIFIKASI, StatusPenarikan::SEDANG_DIPERIKSA, StatusPenarikan::PERLU_REVISI]))
                    ->form([
                        Forms\Components\Textarea::make('alasan_penolakan')
                            ->label('Alasan Penolakan')
                            ->required()
                            ->minLength(3),
                    ])
                    ->action(function (PenarikanTabungan $record, array $data) {
                        try {
                            app(TolakPenarikanService::class)->execute(auth('admin')->user(), $record, $data['alasan_penolakan']);
                            Notification::make()
                                ->title('Penarikan ditolak')
                                ->success()
                                ->send();
                        } catch (\Throwable $e) {
                            Notification::make()
                                ->title('Gagal menolak penarikan')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
                Tables\Actions\Action::make('setujui')
                    ->label('Setujui')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->visible(fn (PenarikanTabungan $record) => auth('admin')->user()->can('setujui', $record) && $record->status === StatusPenarikan::SEDANG_DIPERIKSA)
                    ->form([
                        Forms\Components\TextInput::make('referensi_transfer')
                            ->label('Referensi Transfer'),
                        Forms\Components\DateTimePicker::make('waktu_transfer')
                            ->label('Waktu Transfer'),
                        Forms\Components\Textarea::make('catatan_verifikasi')
                            ->label('Catatan Verifikasi'),
                    ])
                    ->action(function (PenarikanTabungan $record, array $data) {
                        try {
                            app(SetujuiPenarikanService::class)->execute(
                                auth('admin')->user(),
                                $record,
                                $data['referensi_transfer'] ?? null,
                                $data['waktu_transfer'] ? Carbon::parse($data['waktu_transfer']) : null,
                                $data['catatan_verifikasi'] ?? null
                            );
                            Notification::make()
                                ->title('Penarikan disetujui')
                                ->success()
                                ->send();
                        } catch (\Throwable $e) {
                            Notification::make()
                                ->title('Gagal menyetujui penarikan')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
                Tables\Actions\Action::make('cobaUlangPosting')
                    ->label('Coba Ulang Posting')
                    ->icon('heroicon-o-arrow-path')
                    ->color('info')
                    ->visible(fn (PenarikanTabungan $record) => auth('admin')->user()->can('cobaUlangPosting', $record) && $record->status === StatusPenarikan::DISETUJUI)
                    ->action(function (PenarikanTabungan $record) {
                        try {
                            app(PostingPenarikanKeTabunganService::class)->execute($record->id, auth('admin')->user()->id);
                            Notification::make()
                                ->title('Posting penarikan berhasil')
                                ->success()
                                ->send();
                        } catch (\Throwable $e) {
                            Notification::make()
                                ->title('Gagal posting penarikan')
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

                Infolists\Components\Section::make('Detail Penarikan')
                    ->schema([
                        Infolists\Components\TextEntry::make('nomor_penarikan')
                            ->label('Nomor Penarikan'),
                        Infolists\Components\TextEntry::make('jenis_simpanan')
                            ->label('Jenis Simpanan'),
                        Infolists\Components\TextEntry::make('jumlah')
                            ->label('Nominal')
                            ->money('IDR'),
                        Infolists\Components\TextEntry::make('status')
                            ->label('Status')
                            ->badge()
                            ->color(fn (StatusPenarikan $state): string => match ($state) {
                                StatusPenarikan::MENUNGGU_VERIFIKASI => 'info',
                                StatusPenarikan::SEDANG_DIPERIKSA => 'primary',
                                StatusPenarikan::PERLU_REVISI => 'danger',
                                StatusPenarikan::DISETUJUI => 'success',
                                StatusPenarikan::SELESAI => 'success',
                                StatusPenarikan::DITOLAK => 'danger',
                                StatusPenarikan::DIBATALKAN => 'gray',
                            })
                            ->formatStateUsing(fn (StatusPenarikan $state): string => match ($state) {
                                StatusPenarikan::MENUNGGU_VERIFIKASI => 'Menunggu Verifikasi',
                                StatusPenarikan::SEDANG_DIPERIKSA => 'Sedang Diperiksa',
                                StatusPenarikan::PERLU_REVISI => 'Perlu Revisi',
                                StatusPenarikan::DISETUJUI => 'Disetujui',
                                StatusPenarikan::SELESAI => 'Selesai',
                                StatusPenarikan::DITOLAK => 'Ditolak',
                                StatusPenarikan::DIBATALKAN => 'Dibatalkan',
                            }),
                        Infolists\Components\TextEntry::make('created_at')
                            ->label('Waktu Dibuat')
                            ->dateTime(),
                    ])->columns(3),

                Infolists\Components\Section::make('Data Rekening Tujuan')
                    ->schema([
                        Infolists\Components\TextEntry::make('bank')
                            ->label('Bank'),
                        Infolists\Components\TextEntry::make('nama_bank')
                            ->label('Nama Bank'),
                        Infolists\Components\TextEntry::make('nama_nasabah')
                            ->label('Nama Nasabah'),
                    ])->columns(3),

                Infolists\Components\Section::make('Bukti & Data Pengajuan')
                    ->schema([
                        Infolists\Components\TextEntry::make('referensi_penarikan')
                            ->label('Referensi Penarikan'),
                        Infolists\Components\TextEntry::make('dikirim_at')
                            ->label('Waktu Pengajuan')
                            ->dateTime(),
                        Infolists\Components\TextEntry::make('bukti_penarikan_path')
                            ->label('Path Bukti Penarikan')
                            ->hintAction(
                                Infolists\Components\Actions\Action::make('downloadBuktiUtama')
                                    ->label('Download')
                                    ->icon('heroicon-o-arrow-down-tray')
                                    ->visible(fn ($record) => ! empty($record->bukti_penarikan_path))
                                    ->action(function ($record) {
                                        if ($record->bukti_penarikan_path && Storage::disk('private')->exists($record->bukti_penarikan_path)) {
                                            return Storage::disk('private')->download($record->bukti_penarikan_path);
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
                        Infolists\Components\RepeatableEntry::make('buktiPenarikan')
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

                Infolists\Components\Section::make('Informasi Transfer')
                    ->schema([
                        Infolists\Components\TextEntry::make('referensi_transfer')
                            ->label('Referensi Transfer'),
                        Infolists\Components\TextEntry::make('waktu_transfer')
                            ->label('Waktu Transfer')
                            ->dateTime(),
                    ])->columns(2),

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

                Infolists\Components\Section::make('Catatan Pengajuan & Pemeriksaan')
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
                                    ->formatStateUsing(fn ($state) => $state ? str($state)->replace('_', ' ')->title() : '-'),
                                Infolists\Components\TextEntry::make('status_baru')
                                    ->label('Baru')
                                    ->badge()
                                    ->color('success')
                                    ->formatStateUsing(fn ($state) => str($state)->replace('_', ' ')->title()),
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
            'index' => Pages\ListPenarikanTabungans::route('/'),
            'view' => Pages\ViewPenarikanTabungan::route('/{record}'),
        ];
    }

    public static function cetakSlip(PenarikanTabungan $penarikan): StreamedResponse
    {
        $options = new Options;
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isPhpEnabled', true);
        $options->set('defaultFont', 'Arial');
        $options->set('isRemoteEnabled', true);
        $options->set('chroot', public_path());

        $dompdf = new Dompdf($options);
        $dompdf->setPaper([0, 0, 368.504, 510.236], 'portrait');

        $html = view('pdf.slip-penarikan', [
            'penarikan' => $penarikan,
        ])->render();

        $dompdf->loadHtml($html);
        $dompdf->render();

        $filename = 'slip_penarikan_'.$penarikan->nomor_penarikan.'_'.date('Y-m-d_H-i-s').'.pdf';

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
