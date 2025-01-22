<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Tables\Table;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\TransaksiReferral;
use Filament\Tables\Actions\Action;
use Illuminate\Contracts\View\View;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Concerns\InteractsWithTable;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;

class HistoryPenarikan extends Page implements HasTable, HasForms

{
    use InteractsWithTable, HasPageShield;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.history-penarikan';
    protected static ?string $navigationGroup = 'Referral';
    protected static ?string $navigationLabel = 'History Penarikan';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                TransaksiReferral::query()
                    ->where('jenis_transaksi', 'withdrawal')
            )
            ->columns([
                TextColumn::make('tanggal_transaksi')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('anggotaReferral.nama')
                    ->label('Nama Anggota Referral')
                    ->searchable(),
                TextColumn::make('nilai_withdrawal')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('status_komisi')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                    }),
                TextColumn::make('keterangan'),
            ])
            ->defaultSort('tanggal_transaksi', 'desc')
            ->headerActions([
                Action::make('printAll')
                    ->label('Cetak Semua')
                    ->icon('heroicon-o-printer')
                    ->color('success')
                    ->action(fn () => $this->cetakSemuaPDF())
            ]);
    }

    public function cetakSemuaPDF()
    {
        $transaksi = TransaksiReferral::with('profile', 'anggotaReferral')
            ->where('jenis_transaksi', 'withdrawal')
            ->orderBy('tanggal_transaksi', 'desc')
            ->get();

        $pdf = Pdf::loadView('pdf.history-penarikan-all', [
            'transaksi' => $transaksi
        ]);

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, 'history-penarikan-all.pdf');
    }
}
