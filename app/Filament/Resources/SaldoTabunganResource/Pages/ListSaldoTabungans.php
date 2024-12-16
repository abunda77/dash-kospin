<?php

namespace App\Filament\Resources\SaldoTabunganResource\Pages;

use App\Filament\Resources\SaldoTabunganResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions\Action;
use Barryvdh\DomPDF\Facade\Pdf;

class ListSaldoTabungans extends ListRecords
{
    protected static string $resource = SaldoTabunganResource::class;

    protected function getActions(): array
    {
        return [
            Action::make('print')
                ->label('Cetak PDF')
                ->icon('heroicon-o-printer')
                ->action(function () {
                    $saldoTabungans = $this->getResource()::getModel()::all();

                    $pdf = Pdf::loadView('pdf.saldo-tabungan', [
                        'saldoTabungans' => $saldoTabungans
                    ]);

                    return response()->streamDownload(function () use ($pdf) {
                        echo $pdf->output();
                    }, 'saldo-tabungan.pdf');
                })
        ];
    }
}
