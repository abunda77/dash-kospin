<?php

namespace App\Filament\Resources\DepositoResource\Pages;

use App\Filament\Resources\DepositoResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\Section;
use Barryvdh\DomPDF\Facade\Pdf;

class ViewDeposito extends ViewRecord
{
    protected static string $resource = DepositoResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Informasi Deposito')
                    ->schema([
                        TextEntry::make('nomor_rekening')
                            ->label('No Rekening'),
                        TextEntry::make('profile.first_name')
                            ->label('Nama Nasabah')
                            ->formatStateUsing(fn ($record) => "{$record->profile->first_name} {$record->profile->last_name}"),
                        TextEntry::make('nominal_penempatan')
                            ->label('Nominal Penempatan')
                            ->money('IDR'),
                        TextEntry::make('jangka_waktu')
                            ->label('Jangka Waktu')
                            ->suffix(' bulan'),
                        TextEntry::make('tanggal_pembukaan')
                            ->label('Tanggal Pembukaan')
                            ->date(),
                        TextEntry::make('tanggal_jatuh_tempo')
                            ->label('Tanggal Jatuh Tempo')
                            ->date(),
                        TextEntry::make('rate_bunga')
                            ->label('Suku Bunga')
                            ->suffix('%'),
                        TextEntry::make('nominal_bunga')
                            ->label('Nominal Bunga')
                            ->money('IDR'),
                        TextEntry::make('perpanjangan_otomatis')
                            ->label('Perpanjangan Otomatis')
                            ->badge(),
                        TextEntry::make('status')
                            ->label('Status')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'active' => 'success',
                                'ended' => 'danger',
                            })
                    ]),

                Section::make('Informasi Rekening Bank')
                    ->schema([
                        TextEntry::make('nama_bank')
                            ->label('Nama Bank'),
                        TextEntry::make('nomor_rekening_bank')
                            ->label('Nomor Rekening'),
                        TextEntry::make('nama_pemilik_rekening_bank')
                            ->label('Nama Pemilik Rekening'),
                    ])
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('print')
                ->label('Cetak Formulir Pembukaan Deposito')
                ->icon('heroicon-o-document')
                ->action(function () {
                    $pdf = Pdf::loadView('pdf.deposito', [
                        'deposito' => $this->record
                    ]);

                    $filename = 'deposito_' . $this->record->profile->first_name . '_' . $this->record->profile->last_name . '_' . $this->record->nomor_rekening . '.pdf';

                    return response()->streamDownload(function () use ($pdf) {
                        echo $pdf->output();
                    }, $filename);
                }),

            Action::make('printCertificate')
                ->label('Cetak Sertifikat Deposito')
                ->icon('heroicon-o-document-text')
                ->color('success')
                ->action(function () {
                    $pdf = Pdf::loadView('pdf.sertifikat-deposito', [
                        'deposito' => $this->record
                    ]);

                    $pdf->setPaper('a4', 'landscape');

                    $filename = 'sertifikat_deposito_' . $this->record->profile->first_name . '_' . $this->record->nomor_rekening . '.pdf';

                    return response()->streamDownload(function () use ($pdf) {
                        echo $pdf->output();
                    }, $filename);
                }),
        ];
    }
}
