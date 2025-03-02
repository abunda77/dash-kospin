<?php

namespace App\Filament\Resources\CicilanEmasResource\Pages;

use App\Filament\Resources\CicilanEmasResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Grid;

class ViewCicilanEmas extends ViewRecord
{
    protected static string $resource = CicilanEmasResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\Action::make('print')
                ->label('Cetak Kontrak')
                ->icon('heroicon-o-printer')
                ->color('info')
                ->action(function () {
                    $record = $this->getRecord();
                    $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.kontrak-cicilan-emas', ['cicilan' => $record]);
                    return response()->streamDownload(function () use ($pdf) {
                        echo $pdf->output();
                    }, 'kontrak-cicilan-emas-' . $record->no_transaksi . '.pdf');
                })
        ];
    }

    protected function mutateRecord(array $data): array
    {
        $record = $this->getRecord();
        $record->load(['pinjaman.transaksiPinjaman' => function ($query) {
            $query->orderBy('angsuran_ke', 'asc');
        }]);

        return $data;
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Informasi Cicilan Emas')
                    ->schema([
                        TextEntry::make('no_transaksi')
                            ->label('Nomor Transaksi'),

                        TextEntry::make('pinjaman.no_pinjaman')
                            ->label('Nomor Pinjaman'),

                        TextEntry::make('berat_emas')
                            ->label('Berat Emas')
                            ->suffix(' gram'),

                        TextEntry::make('total_harga')
                            ->label('Total Harga')
                            ->money('IDR'),

                        TextEntry::make('setoran_awal')
                            ->label('Setoran Awal')
                            ->money('IDR')
                            ->helperText('5% dari harga emas'),

                        TextEntry::make('biaya_admin')
                            ->label('Biaya Admin')
                            ->money('IDR')
                            ->helperText('0.5% dari total harga'),

                        TextEntry::make('status')
                            ->label('Status')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'aktif' => 'primary',
                                'lunas' => 'success',
                                'gagal_bayar' => 'danger',
                            }),

                        TextEntry::make('keterangan')
                            ->label('Keterangan')
                            ->markdown(),

                        TextEntry::make('created_at')
                            ->label('Tanggal Dibuat')
                            ->dateTime(),

                        TextEntry::make('updated_at')
                            ->label('Terakhir Diperbarui')
                            ->dateTime(),
                    ])
                    ->columns(2),

                Section::make('Detail Angsuran Pinjaman')
                    ->schema([
                        TextEntry::make('pinjaman.no_pinjaman')
                            ->label('Nomor Pinjaman'),

                        TextEntry::make('pinjaman.jumlah_pinjaman')
                            ->label('Jumlah Pinjaman')
                            ->money('IDR'),

                        TextEntry::make('pinjaman.tanggal_pinjaman')
                            ->label('Tanggal Pinjaman')
                            ->date(),

                        TextEntry::make('pinjaman.jangka_waktu')
                            ->label('Jangka Waktu')
                            ->suffix(fn ($record) => ' ' . $record->pinjaman->jangka_waktu_satuan),

                        TextEntry::make('pinjaman.tanggal_jatuh_tempo')
                            ->label('Tanggal Jatuh Tempo')
                            ->date(),

                        TextEntry::make('pinjaman.status_pinjaman')
                            ->label('Status Pinjaman')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'pending' => 'warning',
                                'approved' => 'success',
                                'rejected' => 'danger',
                                'completed' => 'primary',
                                default => 'gray',
                            }),
                    ])
                    ->columns(2),
            ]);
    }
}
