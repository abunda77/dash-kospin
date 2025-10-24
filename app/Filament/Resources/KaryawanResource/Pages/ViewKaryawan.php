<?php

namespace App\Filament\Resources\KaryawanResource\Pages;

use App\Filament\Resources\KaryawanResource;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;

class ViewKaryawan extends ViewRecord
{
    protected static string $resource = KaryawanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('print')
                ->label('Cetak Data Karyawan')
                ->icon('heroicon-o-printer')
                ->action(function () {
                    $pdf = Pdf::loadView('pdf.karyawan', [
                        'karyawan' => $this->record,
                    ]);

                    $filename = 'data_karyawan_'.str_replace(' ', '_', $this->record->nama).'.pdf';

                    return response()->streamDownload(function () use ($pdf) {
                        echo $pdf->output();
                    }, $filename);
                }),
        ];
    }
}
