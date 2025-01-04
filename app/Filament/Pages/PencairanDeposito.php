<?php

namespace App\Filament\Pages;

use App\Models\Deposito;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Actions\Action;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Illuminate\Support\Facades\Log;

class PencairanDeposito extends Page implements HasTable
{
    use InteractsWithTable;
    use HasPageShield;

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';
    protected static string $view = 'filament.pages.pencairan-deposito';
    protected static ?string $navigationGroup = 'Deposito';
    protected static ?string $title = 'Pencairan Deposito';
    protected static ?string $navigationLabel = 'Pencairan Deposito';

    public function getTableQuery()
    {
        return Deposito::query()
            ->with('profile')
            ->where('status', 'active')
            ->where('tanggal_jatuh_tempo', '<=', Carbon::today());
    }

    protected function getTableColumns(): array
    {
        return [
            TextColumn::make('nomor_rekening')
                ->label('No. Rekening')
                ->searchable()
                ->sortable(),
            TextColumn::make('profile.first_name')
                ->label('Nama Nasabah')
                ->formatStateUsing(fn($record) =>
                    trim("{$record->profile->first_name} {$record->profile->last_name}")
                )
                ->searchable()
                ->sortable(),
            TextColumn::make('nominal_penempatan')
                ->label('Nominal')
                ->money('IDR')
                ->sortable(),
            TextColumn::make('tanggal_jatuh_tempo')
                ->label('Jatuh Tempo')
                ->date('d M Y')
                ->sortable(),
            BadgeColumn::make('status_jatuh_tempo')
                ->label('Status')
                ->getStateUsing(fn($record) =>
                    $record->tanggal_jatuh_tempo->isToday() ? 'Jatuh Tempo' : 'Lewat Jatuh Tempo'
                )
                ->colors([
                    'warning' => 'Jatuh Tempo',
                    'danger' => 'Lewat Jatuh Tempo',
                ]),
            TextColumn::make('total_penarikan')
                ->label('Total Penarikan')
                ->money('IDR')
                ->getStateUsing(fn($record) =>
                    $record->nominal_penempatan + $record->nominal_bunga
                )
                ->sortable(),
        ];
    }

    protected function getTableActions(): array
    {
        return [




            Action::make('cetakInvoice')
                ->label('Cetak Invoice')
                ->icon('heroicon-o-printer')
                ->action(function (Deposito $record) {
                    return response()->streamDownload(function () use ($record) {
                        $pdf = Pdf::loadView('pdf.pencairan-deposito', [
                            'deposito' => $record
                        ]);
                        echo $pdf->output();
                    }, "invoice-pencairan-{$record->nomor_rekening}.pdf");
                })
                ->color('primary'),

                Action::make('endDeposito')
                ->label('Cairkan')
                ->icon('heroicon-o-check-circle')
                ->action(function (Deposito $record) {
                    $record->update(['status' => 'ended']);
                })
                ->requiresConfirmation()
                ->color('success'),

            Action::make('perpanjangDeposito')
                ->label('Perpanjang')
                ->icon('heroicon-o-arrow-path')
                ->action(function (Deposito $record) {
                    $totalPenarikan = $record->nominal_penempatan + $record->nominal_bunga;

                    $record->update([
                        'nominal_penempatan' => $totalPenarikan,
                        'tanggal_pembukaan' => now(),
                        'tanggal_jatuh_tempo' => now()->addMonths($record->jangka_waktu),
                        // Hitung ulang nominal_bunga berdasarkan nominal baru
                        'nominal_bunga' => ($totalPenarikan * $record->rate_bunga/100 * $record->jangka_waktu) / 12,
                    ]);
                })
                ->requiresConfirmation()
                ->modalHeading('Perpanjang Deposito')
                ->modalDescription('Apakah Anda yakin ingin memperpanjang deposito ini? Nominal penempatan akan diperbarui dengan menambahkan bunga sebelumnya.')
                ->modalSubmitActionLabel('Ya, Perpanjang')
                ->color('warning'),
        ];
    }
}
