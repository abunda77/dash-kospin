<?php

namespace App\Filament\Resources\PenarikanTabunganResource\Pages;

use App\Filament\Resources\PenarikanTabunganResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewPenarikanTabungan extends ViewRecord
{
    protected static string $resource = PenarikanTabunganResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('mulaiReview')
                ->label('Mulai Review')
                ->icon('heroicon-o-play')
                ->color('primary')
                ->visible(fn () => auth('admin')->user()->can('mulaiReview', $this->record) && $this->record->status === \App\Enums\StatusPenarikan::MENUNGGU_VERIFIKASI)
                ->action(function () {
                    try {
                        app(\App\Services\MulaiReviewPenarikanService::class)->execute(auth('admin')->user(), $this->record);
                        $this->refreshFormData([
                            'status',
                            'mulai_review_at',
                            'diperiksa_oleh',
                        ]);
                        \Filament\Notifications\Notification::make()
                            ->title('Proses review dimulai')
                            ->success()
                            ->send();
                    } catch (\Throwable $e) {
                        \Filament\Notifications\Notification::make()
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
                ->visible(fn () => auth('admin')->user()->can('mintaRevisi', $this->record) && $this->record->status === \App\Enums\StatusPenarikan::SEDANG_DIPERIKSA)
                ->form([
                    \Filament\Forms\Components\Textarea::make('catatan_verifikasi')
                        ->label('Alasan / Catatan Revisi')
                        ->required()
                        ->minLength(3),
                ])
                ->action(function (array $data) {
                    try {
                        app(\App\Services\MintaRevisiPenarikanService::class)->execute(auth('admin')->user(), $this->record, $data['catatan_verifikasi']);
                        $this->refreshFormData([
                            'status',
                            'catatan_verifikasi',
                        ]);
                        \Filament\Notifications\Notification::make()
                            ->title('Permintaan revisi dikirim')
                            ->success()
                            ->send();
                    } catch (\Throwable $e) {
                        \Filament\Notifications\Notification::make()
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
                ->visible(fn () => auth('admin')->user()->can('tolak', $this->record) && in_array($this->record->status, [\App\Enums\StatusPenarikan::MENUNGGU_VERIFIKASI, \App\Enums\StatusPenarikan::SEDANG_DIPERIKSA, \App\Enums\StatusPenarikan::PERLU_REVISI]))
                ->form([
                    \Filament\Forms\Components\Textarea::make('alasan_penolakan')
                        ->label('Alasan Penolakan')
                        ->required()
                        ->minLength(3),
                ])
                ->action(function (array $data) {
                    try {
                        app(\App\Services\TolakPenarikanService::class)->execute(auth('admin')->user(), $this->record, $data['alasan_penolakan']);
                        $this->refreshFormData([
                            'status',
                            'alasan_penolakan',
                            'ditolak_at',
                            'ditolak_oleh',
                        ]);
                        \Filament\Notifications\Notification::make()
                            ->title('Penarikan ditolak')
                            ->success()
                            ->send();
                    } catch (\Throwable $e) {
                        \Filament\Notifications\Notification::make()
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
                ->visible(fn () => auth('admin')->user()->can('setujui', $this->record) && $this->record->status === \App\Enums\StatusPenarikan::SEDANG_DIPERIKSA)
                ->form([
                    \Filament\Forms\Components\TextInput::make('referensi_transfer')
                        ->label('Referensi Transfer'),
                    \Filament\Forms\Components\DateTimePicker::make('waktu_transfer')
                        ->label('Waktu Transfer'),
                    \Filament\Forms\Components\Textarea::make('catatan_verifikasi')
                        ->label('Catatan Verifikasi'),
                ])
                ->action(function (array $data) {
                    try {
                        app(\App\Services\SetujuiPenarikanService::class)->execute(
                            auth('admin')->user(),
                            $this->record,
                            $data['referensi_transfer'] ?? null,
                            $data['waktu_transfer'] ? \Illuminate\Support\Carbon::parse($data['waktu_transfer']) : null,
                            $data['catatan_verifikasi'] ?? null
                        );
                        $this->refreshFormData([
                            'status',
                            'referensi_transfer',
                            'waktu_transfer',
                            'catatan_verifikasi',
                            'disetujui_at',
                        ]);
                        \Filament\Notifications\Notification::make()
                            ->title('Penarikan disetujui')
                            ->success()
                            ->send();
                    } catch (\Throwable $e) {
                        \Filament\Notifications\Notification::make()
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
                ->visible(fn () => auth('admin')->user()->can('cobaUlangPosting', $this->record) && $this->record->status === \App\Enums\StatusPenarikan::DISETUJUI)
                ->action(function () {
                    try {
                        app(\App\Services\PostingPenarikanKeTabunganService::class)->execute($this->record->id, auth('admin')->user()->id);
                        $this->refreshFormData([
                            'status',
                            'diposting_at',
                            'selesai_at',
                        ]);
                        \Filament\Notifications\Notification::make()
                            ->title('Posting penarikan berhasil')
                            ->success()
                            ->send();
                    } catch (\Throwable $e) {
                        \Filament\Notifications\Notification::make()
                            ->title('Gagal posting penarikan')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
        ];
    }
}
