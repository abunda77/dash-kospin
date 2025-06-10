<?php

namespace App\Filament\Resources\PelunasanResource\Pages;

use App\Filament\Resources\PelunasanResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Barryvdh\DomPDF\Facade\Pdf;

class ViewPelunasan extends ViewRecord
{
    protected static string $resource = PelunasanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\Action::make('printSuratPelunasan')
                ->label('Cetak Surat Pelunasan')
                ->color('success')
                ->icon('heroicon-o-document-text')
                ->action(function () {
                    $pdf = Pdf::loadView('pdf.surat-pelunasan', [
                        'pelunasan' => $this->record
                    ]);

                    $filename = 'surat_pelunasan_' . $this->record->profile->first_name . '_' . $this->record->no_pinjaman . '.pdf';

                    return response()->streamDownload(function () use ($pdf) {
                        echo $pdf->output();
                    }, $filename);
                })
        ];
    }
}
