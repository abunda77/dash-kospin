<?php

namespace App\Filament\Resources\KreditElektronikResource\Pages;

use App\Filament\Resources\KreditElektronikResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewKreditElektronik extends ViewRecord
{
    protected static string $resource = KreditElektronikResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\Action::make('print')
                ->label('Cetak Kontrak')
                ->icon('heroicon-o-printer')
                ->action(function () {
                    $record = $this->getRecord();
                    $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.kontrak-kredit', ['kredit' => $record]);
                    return response()->streamDownload(function () use ($pdf) {
                        echo $pdf->output();
                    }, 'kontrak-kredit-' . $record->kode_barang . '.pdf');
                })
        ];
    }
}
