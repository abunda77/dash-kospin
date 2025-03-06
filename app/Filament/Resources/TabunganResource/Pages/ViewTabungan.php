<?php

namespace App\Filament\Resources\TabunganResource\Pages;

use Dompdf\Dompdf;
use Dompdf\Options;
use Filament\Actions\Action;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Infolists\Infolist;
use App\Models\TransaksiTabungan;
use Illuminate\Support\Facades\Log;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Components\Section;
use App\Filament\Resources\TabunganResource;
use Filament\Infolists\Components\TextEntry;
use Illuminate\Http\Response;

class ViewTabungan extends ViewRecord
{
    protected static string $resource = TabunganResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Informasi Rekening')
                    ->schema([
                        TextEntry::make('no_tabungan')
                            ->label('No Rekening'),
                        TextEntry::make('profile.first_name')
                            ->label('Nama Nasabah')
                            ->formatStateUsing(fn ($record) => "{$record->profile->first_name} {$record->profile->last_name}"),
                        TextEntry::make('produkTabungan.nama_produk')
                            ->label('Produk Tabungan'),
                        TextEntry::make('saldo')
                            ->label('Saldo')
                            ->money('IDR'),
                        TextEntry::make('tanggal_buka_rekening')
                            ->label('Tanggal Buka')
                            ->date(),
                        TextEntry::make('status_rekening')
                            ->label('Status')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'aktif' => 'success',
                                'tidak_aktif' => 'danger',
                                'blokir' => 'warning',
                            })
                    ])
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('printSlip')
                ->label('Cetak Slip Setoran Awal')
                ->icon('heroicon-o-document-text')
                ->color('success')
                ->action(function () {
                    return $this->printSlipTabungan();
                }),

            Action::make('print')
                ->label('Cetak Formulir Pembukaan Rekening')
                ->icon('heroicon-o-printer')
                ->action(function () {
                    $pdf = Pdf::loadView('pdf.tabungan', [
                        'tabungan' => $this->record
                    ]);

                    $filename = 'rekening_' . $this->record->profile->first_name . '_' . $this->record->profile->last_name . '_' . $this->record->no_tabungan . '.pdf';

                    return response()->streamDownload(function () use ($pdf) {
                        echo $pdf->output();
                    }, $filename);
                }),

            Action::make('printUmroh')
                ->label('Cetak Form Tabungan Umroh')
                ->icon('heroicon-o-printer')
                ->color('danger')
                ->action(function () {
                    $pdf = Pdf::loadView('pdf.tabungan-umroh', [
                        'tabungan' => $this->record
                    ]);

                    $filename = 'rekening_umroh_' . $this->record->profile->first_name . '_' . $this->record->profile->last_name . '_' . $this->record->no_tabungan . '.pdf';

                    return response()->streamDownload(function () use ($pdf) {
                        echo $pdf->output();
                    }, $filename);
                }),

            Action::make('printLebaran')
                ->label('Cetak Form Tabungan Lebaran')
                ->icon('heroicon-o-printer')
                ->color('info')
                ->action(function () {
                    $pdf = Pdf::loadView('pdf.tabungan-lebaran', [
                        'tabungan' => $this->record
                    ]);

                    $filename = 'rekening_lebaran_' . $this->record->profile->first_name . '_' . $this->record->profile->last_name . '_' . $this->record->no_tabungan . '.pdf';

                    return response()->streamDownload(function () use ($pdf) {
                        echo $pdf->output();
                    }, $filename);
                }),

            Action::make('printLiburan')
                ->label('Cetak Form Tabungan Liburan')
                ->icon('heroicon-o-printer')
                ->color('purple')
                ->action(function () {
                    $pdf = Pdf::loadView('pdf.tabungan-liburan', [
                        'tabungan' => $this->record
                    ]);

                    $filename = 'rekening_liburan_' . $this->record->profile->first_name . '_' . $this->record->profile->last_name . '_' . $this->record->no_tabungan . '.pdf';

                    return response()->streamDownload(function () use ($pdf) {
                        echo $pdf->output();
                    }, $filename);
                }),
        ];
    }

    public function printSlipTabungan()
    {
        try {
            $options = new Options();
            $options->set('isHtml5ParserEnabled', true);
            $options->set('isPhpEnabled', true);
            $options->set('defaultFont', 'Arial');
            $options->set('isRemoteEnabled', true);
            $options->set('chroot', public_path());

            $dompdf = new Dompdf($options);
            $dompdf->setPaper(array(0, 0, 368.504, 510.236), 'portrait');

            // Ambil transaksi terbaru
            $transaksi = TransaksiTabungan::where('id_tabungan', $this->record->id)
                ->orderBy('tanggal_transaksi', 'DESC')
                ->orderBy('id', 'DESC')
                ->first();

            $html = view('pdf.slip-tabungan', [
                'tabungan' => $this->record,
                'transaksi' => $transaksi,
            ])->render();

            $dompdf->loadHtml($html);
            $dompdf->render();

            $filename = 'slip_tabungan_' . $this->record->no_tabungan . '_' . date('Y-m-d_H-i-s') . '.pdf';

            return response()->streamDownload(
                function() use ($dompdf) {
                    echo $dompdf->output();
                },
                $filename,
                [
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => 'attachment; filename="'.$filename.'"'
                ]
            );

        } catch (\Exception $e) {
            Log::error('Error in printSlipTabungan:', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            Notification::make()
                ->title('Terjadi kesalahan saat mencetak slip')
                ->body($e->getMessage())
                ->danger()
                ->send();

            return null;
        }
    }
}
