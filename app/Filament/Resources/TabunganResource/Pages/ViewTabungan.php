<?php

namespace App\Filament\Resources\TabunganResource\Pages;

use App\Filament\Resources\TabunganResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\Section;
use Barryvdh\DomPDF\Facade\Pdf;

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
            Action::make('print')
                ->label('Cetak Formuli Pembukaan Rekening Tabungan')
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
        ];
    }
}
