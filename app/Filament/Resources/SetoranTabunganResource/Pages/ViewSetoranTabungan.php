<?php

namespace App\Filament\Resources\SetoranTabunganResource\Pages;

use App\Filament\Resources\SetoranTabunganResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewSetoranTabungan extends ViewRecord
{
    protected static string $resource = SetoranTabunganResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('mulaiReview')
                ->label('Mulai Review')
                ->icon('heroicon-o-play')
                ->color('primary')
                ->visible(fn () => auth('admin')->user()->can('mulaiReview', $this->record) && $this->record->status === \App\Enums\StatusSetoran::MENUNGGU_VERIFIKASI)
                ->action(function () {
                    try {
                        app(\App\Services\MulaiReviewSetoranService::class)->execute(auth('admin')->user(), $this->record);
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
                ->visible(fn () => auth('admin')->user()->can('mintaRevisi', $this->record) && $this->record->status === \App\Enums\StatusSetoran::SEDANG_DIPERIKSA)
                ->form([
                    \Filament\Forms\Components\Textarea::make('catatan_verifikasi')
                        ->label('Alasan / Catatan Revisi')
                        ->required()
                        ->minLength(3),
                ])
                ->action(function (array $data) {
                    try {
                        app(\App\Services\MintaRevisiSetoranService::class)->execute(auth('admin')->user(), $this->record, $data['catatan_verifikasi']);
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
                ->visible(fn () => auth('admin')->user()->can('tolak', $this->record) && in_array($this->record->status, [\App\Enums\StatusSetoran::MENUNGGU_VERIFIKASI, \App\Enums\StatusSetoran::SEDANG_DIPERIKSA, \App\Enums\StatusSetoran::PERLU_REVISI]))
                ->form([
                    \Filament\Forms\Components\Textarea::make('alasan_penolakan')
                        ->label('Alasan Penolakan')
                        ->required()
                        ->minLength(3),
                ])
                ->action(function (array $data) {
                    try {
                        app(\App\Services\TolakSetoranService::class)->execute(auth('admin')->user(), $this->record, $data['alasan_penolakan']);
                        $this->refreshFormData([
                            'status',
                            'alasan_penolakan',
                            'ditolak_at',
                            'ditolak_oleh',
                        ]);
                        \Filament\Notifications\Notification::make()
                            ->title('Setoran ditolak')
                            ->success()
                            ->send();
                    } catch (\Throwable $e) {
                        \Filament\Notifications\Notification::make()
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
                ->visible(fn () => auth('admin')->user()->can('setujui', $this->record) && $this->record->status === \App\Enums\StatusSetoran::SEDANG_DIPERIKSA)
                ->form([
                    \Filament\Forms\Components\TextInput::make('referensi_transaksi_provider')
                        ->label('Referensi Transaksi Provider'),
                    \Filament\Forms\Components\DateTimePicker::make('waktu_bayar_provider')
                        ->label('Waktu Bayar Provider'),
                    \Filament\Forms\Components\TextInput::make('nama_pembayar_provider')
                        ->label('Nama Pembayar Provider'),
                    \Filament\Forms\Components\Textarea::make('catatan_verifikasi')
                        ->label('Catatan Verifikasi'),
                ])
                ->action(function (array $data) {
                    try {
                        app(\App\Services\SetujuiSetoranService::class)->execute(
                            auth('admin')->user(),
                            $this->record,
                            $data['referensi_transaksi_provider'] ?? null,
                            $data['waktu_bayar_provider'] ? \Illuminate\Support\Carbon::parse($data['waktu_bayar_provider']) : null,
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
                        \Filament\Notifications\Notification::make()
                            ->title('Setoran disetujui')
                            ->success()
                            ->send();
                    } catch (\Throwable $e) {
                        \Filament\Notifications\Notification::make()
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
                ->visible(fn () => auth('admin')->user()->can('cobaUlangPosting', $this->record) && $this->record->status === \App\Enums\StatusSetoran::DISETUJUI)
                ->action(function () {
                    try {
                        app(\App\Services\PostingSetoranKeTabunganService::class)->execute($this->record->id, auth('admin')->user()->id);
                        $this->refreshFormData([
                            'status',
                            'diposting_at',
                            'selesai_at',
                        ]);
                        \Filament\Notifications\Notification::make()
                            ->title('Posting saldo berhasil')
                            ->success()
                            ->send();
                    } catch (\Throwable $e) {
                        \Filament\Notifications\Notification::make()
                            ->title('Gagal posting saldo')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
        ];
    }
}
