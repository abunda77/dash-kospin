<?php

namespace App\Filament\Resources\SetoranTabunganResource\Pages;

use App\Enums\StatusSetoran;
use App\Filament\Resources\SetoranTabunganResource;
use App\Services\MintaRevisiSetoranService;
use App\Services\MulaiReviewSetoranService;
use App\Services\PostingSetoranKeTabunganService;
use App\Services\SetujuiSetoranService;
use App\Services\TolakSetoranService;
use Filament\Actions;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Carbon;

class ViewSetoranTabungan extends ViewRecord
{
    protected static string $resource = SetoranTabunganResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('cetakSlip')
                ->label('Cetak Slip Setoran')
                ->icon('heroicon-o-printer')
                ->color('success')
                ->visible(fn () => in_array($this->record->status, [StatusSetoran::DISETUJUI, StatusSetoran::SELESAI]))
                ->action(function () {
                    return $this->cetakSlipSetoran();
                }),
            Actions\Action::make('mulaiReview')
                ->label('Mulai Review')
                ->icon('heroicon-o-play')
                ->color('primary')
                ->visible(fn () => auth('admin')->user()->can('mulaiReview', $this->record) && $this->record->status === StatusSetoran::MENUNGGU_VERIFIKASI)
                ->action(function () {
                    try {
                        app(MulaiReviewSetoranService::class)->execute(auth('admin')->user(), $this->record);
                        $this->refreshFormData([
                            'status',
                            'mulai_review_at',
                            'diperiksa_oleh',
                        ]);
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

            Actions\Action::make('mintaRevisi')
                ->label('Minta Revisi')
                ->icon('heroicon-o-arrow-path')
                ->color('warning')
                ->visible(fn () => auth('admin')->user()->can('mintaRevisi', $this->record) && $this->record->status === StatusSetoran::SEDANG_DIPERIKSA)
                ->form([
                    Textarea::make('catatan_verifikasi')
                        ->label('Alasan / Catatan Revisi')
                        ->required()
                        ->minLength(3),
                ])
                ->action(function (array $data) {
                    try {
                        app(MintaRevisiSetoranService::class)->execute(auth('admin')->user(), $this->record, $data['catatan_verifikasi']);
                        $this->refreshFormData([
                            'status',
                            'catatan_verifikasi',
                        ]);
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

            Actions\Action::make('tolak')
                ->label('Tolak')
                ->icon('heroicon-o-x-mark')
                ->color('danger')
                ->visible(fn () => auth('admin')->user()->can('tolak', $this->record) && in_array($this->record->status, [StatusSetoran::MENUNGGU_VERIFIKASI, StatusSetoran::SEDANG_DIPERIKSA, StatusSetoran::PERLU_REVISI]))
                ->form([
                    Textarea::make('alasan_penolakan')
                        ->label('Alasan Penolakan')
                        ->required()
                        ->minLength(3),
                ])
                ->action(function (array $data) {
                    try {
                        app(TolakSetoranService::class)->execute(auth('admin')->user(), $this->record, $data['alasan_penolakan']);
                        $this->refreshFormData([
                            'status',
                            'alasan_penolakan',
                            'ditolak_at',
                            'ditolak_oleh',
                        ]);
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

            Actions\Action::make('setujui')
                ->label('Setujui')
                ->icon('heroicon-o-check')
                ->color('success')
                ->visible(fn () => auth('admin')->user()->can('setujui', $this->record) && $this->record->status === StatusSetoran::SEDANG_DIPERIKSA)
                ->form([
                    TextInput::make('referensi_transaksi_provider')
                        ->label('Referensi Transaksi Provider'),
                    DateTimePicker::make('waktu_bayar_provider')
                        ->label('Waktu Bayar Provider'),
                    TextInput::make('nama_pembayar_provider')
                        ->label('Nama Pembayar Provider'),
                    Textarea::make('catatan_verifikasi')
                        ->label('Catatan Verifikasi'),
                ])
                ->action(function (array $data) {
                    try {
                        app(SetujuiSetoranService::class)->execute(
                            auth('admin')->user(),
                            $this->record,
                            $data['referensi_transaksi_provider'] ?? null,
                            $data['waktu_bayar_provider'] ? Carbon::parse($data['waktu_bayar_provider']) : null,
                            $data['nama_pembayar_provider'] ?? null,
                            $data['catatan_verifikasi'] ?? null
                        );
                        $this->refreshFormData([
                            'status',
                            'referensi_transaksi_provider',
                            'waktu_bayar_provider',
                            'nama_pembayar_provider',
                            'catatan_verifikasi',
                            'disetujui_at',
                        ]);
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

            Actions\Action::make('cobaUlangPosting')
                ->label('Coba Ulang Posting')
                ->icon('heroicon-o-arrow-path')
                ->color('info')
                ->visible(fn () => auth('admin')->user()->can('cobaUlangPosting', $this->record) && $this->record->status === StatusSetoran::DISETUJUI)
                ->action(function () {
                    try {
                        app(PostingSetoranKeTabunganService::class)->execute($this->record->id, auth('admin')->user()->id);
                        $this->refreshFormData([
                            'status',
                            'diposting_at',
                            'selesai_at',
                        ]);
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
        ];
    }

    public function cetakSlipSetoran()
    {
        try {
            return SetoranTabunganResource::cetakSlip($this->record);
        } catch (\Throwable $e) {
            Notification::make()
                ->title('Terjadi kesalahan saat mencetak slip')
                ->body($e->getMessage())
                ->danger()
                ->send();

            return null;
        }
    }
}
