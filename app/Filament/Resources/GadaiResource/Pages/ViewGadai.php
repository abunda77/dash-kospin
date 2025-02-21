<?php

namespace App\Filament\Resources\GadaiResource\Pages;

use App\Filament\Resources\GadaiResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;
use Barryvdh\DomPDF\Facade\Pdf;

class ViewGadai extends ViewRecord
{
    protected static string $resource = GadaiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('print_contract')
                ->label('Cetak Surat Gadai')
                ->icon('heroicon-o-document-text')
                ->action(function () {
                    $pdf = Pdf::loadView('pdf.surat-gadai', [
                        'gadai' => $this->record
                    ]);

                    $filename = 'surat_gadai_' . $this->record->pinjaman->no_pinjaman . '.pdf';

                    return response()->streamDownload(function () use ($pdf) {
                        echo $pdf->output();
                    }, $filename);
                }),
        ];
    }
} 