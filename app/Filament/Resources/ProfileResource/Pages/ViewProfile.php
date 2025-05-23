<?php

namespace App\Filament\Resources\ProfileResource\Pages;

use App\Filament\Resources\ProfileResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;
use Barryvdh\DomPDF\Facade\Pdf;

class ViewProfile extends ViewRecord
{
    protected static string $resource = ProfileResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('print')
                ->label('Cetak PDF')
                ->icon('heroicon-o-printer')
                ->action(function () {
                    $pdf = Pdf::loadView('pdf.profile', [
                        'profile' => $this->record
                    ]);

                    $filename = 'profile_nasabah_' . $this->record->first_name . '_' . $this->record->last_name . '.pdf';

                    return response()->streamDownload(function () use ($pdf) {
                        echo $pdf->output();
                    }, $filename);
                }),

            Action::make('printRegistrationForm')
                ->label('Cetak Form Pendaftaran')
                ->icon('heroicon-o-document')
                ->color('success')
                ->action(function () {
                    $pdf = Pdf::loadView('pdf.registration-form', [
                        'profile' => $this->record
                    ]);

                    $filename = 'form_pendaftaran_' . $this->record->first_name . '_' . $this->record->last_name . '.pdf';

                    return response()->streamDownload(function () use ($pdf) {
                        echo $pdf->output();
                    }, $filename);
                }),
        ];
    }
}
