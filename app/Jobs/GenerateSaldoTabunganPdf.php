<?php

namespace App\Jobs;

use App\Models\Tabungan;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class GenerateSaldoTabunganPdf implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 300;

    public int $tries = 2;

    public function __construct(
        protected int $userId,
        protected string $cacheKey
    ) {}

    public function handle(): void
    {
        try {
            Cache::put($this->cacheKey, ['status' => 'processing'], now()->addHours(2));

            $tabungans = Tabungan::with(['profile'])
                ->withSum(['transaksi as total_debit' => function ($q) {
                    $q->where('jenis_transaksi', 'debit');
                }], 'jumlah')
                ->withSum(['transaksi as total_kredit' => function ($q) {
                    $q->where('jenis_transaksi', 'kredit');
                }], 'jumlah')
                ->get()
                ->map(function ($tabungan) {
                    $tabungan->saldo_akhir_computed =
                        (float) $tabungan->saldo
                        + ((float) ($tabungan->total_debit ?? 0))
                        - ((float) ($tabungan->total_kredit ?? 0));

                    return $tabungan;
                });

            $pdf = Pdf::loadView('pdf.saldo-tabungan', [
                'tabungans' => $tabungans,
            ])->setPaper('a4', 'portrait');

            $filename = 'saldo-tabungan-'.now()->format('Ymd-His').'.pdf';

            Storage::disk('public')->put('reports/'.$filename, $pdf->output());

            $downloadUrl = url(route('report.download', ['filename' => $filename]));

            Cache::put($this->cacheKey, [
                'status' => 'done',
                'filename' => $filename,
                'url' => $downloadUrl,
            ], now()->addHours(2));

            $recipient = \App\Models\Admin::find($this->userId);

            if ($recipient) {
                $notification = Notification::make()
                    ->title('Laporan Saldo Tabungan Siap')
                    ->body('PDF telah berhasil dibuat. Klik tombol di bawah untuk mengunduh.')
                    ->success()
                    ->actions([
                        Action::make('download')
                            ->label('Unduh PDF')
                            ->url($downloadUrl)
                            ->openUrlInNewTab()
                            ->button(),
                    ]);

                $recipient->notify($notification->toDatabase()->onConnection('sync'));
            }
        } catch (\Throwable $e) {
            Cache::put($this->cacheKey, ['status' => 'failed'], now()->addHours(2));
            Log::error('GenerateSaldoTabunganPdf failed: '.$e->getMessage());
            throw $e;
        }
    }
}
