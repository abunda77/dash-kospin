<?php

namespace App\Filament\Resources\SaldoTabunganResource\Pages;

use App\Filament\Resources\SaldoTabunganResource;
use App\Jobs\GenerateSaldoTabunganPdf;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class ListSaldoTabungans extends ListRecords
{
    protected static string $resource = SaldoTabunganResource::class;

    protected static ?string $title = 'Saldo Tabungan';

    public ?string $pdfCacheKey = null;

    public ?string $pdfDownloadUrl = null;

    public bool $pdfProcessing = false;

    public function mount(): void
    {
        parent::mount();
    }

    public function getListeners(): array
    {
        return array_merge(parent::getListeners(), [
            'checkPdfProgress' => 'checkPdfProgress',
        ]);
    }

    public function checkPdfProgress(): void
    {
        if (! $this->pdfCacheKey) {
            return;
        }

        $progress = Cache::get($this->pdfCacheKey);

        if (! $progress) {
            return;
        }

        if ($progress['status'] === 'done') {
            $this->pdfDownloadUrl = $progress['url'];
            $this->pdfProcessing = false;
            $this->dispatch('stop-polling');
        } elseif ($progress['status'] === 'failed') {
            $this->pdfProcessing = false;
            $this->pdfCacheKey = null;
            $this->dispatch('stop-polling');

            Notification::make()
                ->title('Gagal membuat PDF')
                ->danger()
                ->send();
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('download_pdf')
                ->label('Unduh PDF')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->url(fn () => $this->pdfDownloadUrl)
                ->openUrlInNewTab()
                ->visible(fn () => $this->pdfDownloadUrl !== null),

            Action::make('print')
                ->label(fn () => $this->pdfProcessing ? 'Membuat PDF...' : 'Cetak PDF')
                ->icon(fn () => $this->pdfProcessing ? 'heroicon-o-arrow-path' : 'heroicon-o-printer')
                ->disabled(fn () => $this->pdfProcessing)
                ->action(function () {
                    $this->pdfDownloadUrl = null;
                    $this->pdfCacheKey = 'saldo_tabungan_pdf_'.Str::uuid();
                    $this->pdfProcessing = true;

                    dispatch(new GenerateSaldoTabunganPdf(
                        auth('admin')->id(),
                        $this->pdfCacheKey
                    ));

                    Notification::make()
                        ->title('PDF sedang dibuat')
                        ->body('Proses berjalan di background. Tombol unduh akan muncul otomatis setelah selesai.')
                        ->info()
                        ->send();

                    $this->dispatch('start-polling');
                }),
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [];
    }

    public function getExtraAttributes(): array
    {
        return [
            'x-data' => '{ pollingInterval: null }',
            'x-on:start-polling.window' => 'pollingInterval = setInterval(() => $wire.checkPdfProgress(), 3000)',
            'x-on:stop-polling.window' => 'clearInterval(pollingInterval)',
        ];
    }
}
