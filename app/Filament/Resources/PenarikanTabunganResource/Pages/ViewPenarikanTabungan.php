<?php

namespace App\Filament\Resources\PenarikanTabunganResource\Pages;

use App\Enums\StatusPenarikan;
use App\Filament\Resources\PenarikanTabunganResource;
use App\Services\MintaRevisiPenarikanService;
use App\Services\MulaiReviewPenarikanService;
use App\Services\PostingPenarikanKeTabunganService;
use App\Services\SetujuiPenarikanService;
use App\Services\TolakPenarikanService;
use Filament\Actions;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Carbon;

class ViewPenarikanTabungan extends ViewRecord
{
    protected static string $resource = PenarikanTabunganResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('cetakSlip')
                ->label('Cetak Slip Penarikan')
                ->icon('heroicon-o-printer')
                ->color('success')
                ->visible(fn () => in_array($this->record->status, [StatusPenarikan::DISETUJUI, StatusPenarikan::SELESAI]))
                ->action(function () {
                    return $this->cetakSlipPenarikan();
                }),
            Actions\Action::make('mulaiReview')
                ->label('Mulai Review')
                ->icon('heroicon-o-play')
                ->color('primary')
                ->visible(fn () => auth('admin')->user()->can('mulaiReview', $this->record) && $this->record->status === StatusPenarikan::MENUNGGU_VERIFIKASI)
                ->action(function () {
                    try {
                        app(MulaiReviewPenarikanService::class)->execute(auth('admin')->user(), $this->record);
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
                ->visible(fn () => auth('admin')->user()->can('mintaRevisi', $this->record) && $this->record->status === StatusPenarikan::SEDANG_DIPERIKSA)
                ->form([
                    Textarea::make('catatan_verifikasi')
                        ->label('Alasan / Catatan Revisi')
                        ->required()
                        ->minLength(3),
                ])
                ->action(function (array $data) {
                    try {
                        app(MintaRevisiPenarikanService::class)->execute(auth('admin')->user(), $this->record, $data['catatan_verifikasi']);
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
                ->visible(fn () => auth('admin')->user()->can('tolak', $this->record) && in_array($this->record->status, [StatusPenarikan::MENUNGGU_VERIFIKASI, StatusPenarikan::SEDANG_DIPERIKSA, StatusPenarikan::PERLU_REVISI]))
                ->form([
                    Textarea::make('alasan_penolakan')
                        ->label('Alasan Penolakan')
                        ->required()
                        ->minLength(3),
                ])
                ->action(function (array $data) {
                    try {
                        app(TolakPenarikanService::class)->execute(auth('admin')->user(), $this->record, $data['alasan_penolakan']);
                        $this->refreshFormData([
                            'status',
                            'alasan_penolakan',
                            'ditolak_at',
                            'ditolak_oleh',
                        ]);
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

            Actions\Action::make('setujui')
                ->label('Setujui')
                ->icon('heroicon-o-check')
                ->color('success')
                ->visible(fn () => auth('admin')->user()->can('setujui', $this->record) && $this->record->status === StatusPenarikan::SEDANG_DIPERIKSA)
                ->form([
                    TextInput::make('referensi_transfer')
                        ->label('Referensi Transfer'),
                    DateTimePicker::make('waktu_transfer')
                        ->label('Waktu Transfer'),
                    Textarea::make('catatan_verifikasi')
                        ->label('Catatan Verifikasi'),
                ])
                ->action(function (array $data) {
                    try {
                        app(SetujuiPenarikanService::class)->execute(
                            auth('admin')->user(),
                            $this->record,
                            $data['referensi_transfer'] ?? null,
                            $data['waktu_transfer'] ? Carbon::parse($data['waktu_transfer']) : null,
                            $data['catatan_verifikasi'] ?? null
                        );
                        $this->refreshFormData([
                            'status',
                            'referensi_transfer',
                            'waktu_transfer',
                            'catatan_verifikasi',
                            'disetujui_at',
                        ]);
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

            Actions\Action::make('cobaUlangPosting')
                ->label('Coba Ulang Posting')
                ->icon('heroicon-o-arrow-path')
                ->color('info')
                ->visible(fn () => auth('admin')->user()->can('cobaUlangPosting', $this->record) && $this->record->status === StatusPenarikan::DISETUJUI)
                ->action(function () {
                    try {
                        app(PostingPenarikanKeTabunganService::class)->execute($this->record->id, auth('admin')->user()->id);
                        $this->refreshFormData([
                            'status',
                            'diposting_at',
                            'selesai_at',
                        ]);
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
        ];
    }

    public function cetakSlipPenarikan()
    {
        try {
            return PenarikanTabunganResource::cetakSlip($this->record);
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
