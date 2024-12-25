<?php

namespace App\Filament\Resources\PinjamanResource\Pages;

use App\Filament\Resources\PinjamanResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\Section;
use Barryvdh\DomPDF\Facade\Pdf;

class ViewPinjaman extends ViewRecord
{
    protected static string $resource = PinjamanResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Informasi Pinjaman')
                    ->schema([
                        TextEntry::make('no_pinjaman')
                            ->label('No Pinjaman'),
                        TextEntry::make('profile.user.name')
                            ->label('Nama Nasabah'),
                        TextEntry::make('produkPinjaman.nama_produk')
                            ->label('Produk Pinjaman'),
                        TextEntry::make('jumlah_pinjaman')
                            ->label('Jumlah Pinjaman')
                            ->money('IDR'),
                        TextEntry::make('jangka_waktu')
                            ->label('Jangka Waktu')
                            ->suffix(' Bulan'),
                        TextEntry::make('tanggal_pinjaman')
                            ->label('Tanggal Pinjaman')
                            ->date(),
                        TextEntry::make('tanggal_jatuh_tempo')
                            ->label('Jatuh Tempo')
                            ->date(),
                        TextEntry::make('denda.rate_denda')
                            ->label('Rate Denda'),
                        TextEntry::make('status_pinjaman')
                            ->label('Status')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'pending' => 'warning',
                                'approved' => 'success',
                                'rejected' => 'danger',
                                'completed' => 'info',
                                default => 'warning'
                            })
                    ])
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('print_form')
                ->label('Cetak Form Pinjaman')
                ->icon('heroicon-o-printer')
                ->action(function () {
                    $pdf = Pdf::loadView('pdf.pinjaman', [
                        'pinjaman' => $this->record
                    ]);

                    $filename = 'kontrak_pinjaman_' . $this->record->profile->first_name . '_' . $this->record->profile->last_name . '_' . $this->record->no_pinjaman . '.pdf';

                    return response()->streamDownload(function () use ($pdf) {
                        echo $pdf->output();
                    }, $filename);
                }),

            Action::make('print_contract')
                ->label('Cetak Kontrak Perjanjian')
                ->icon('heroicon-o-document-text')
                ->action(function () {
                    $pdf = Pdf::loadView('pdf.kontrak-pinjaman', [
                        'pinjaman' => $this->record
                    ]);

                    $filename = 'kontrak_pinjaman_' . $this->record->profile->first_name . '_' . $this->record->profile->last_name . '_' . $this->record->no_pinjaman . '.pdf';

                    return response()->streamDownload(function () use ($pdf) {
                        echo $pdf->output();
                    }, $filename);
                }),
        ];
    }
}
