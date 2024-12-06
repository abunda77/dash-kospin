<?php

namespace App\Filament\Pages;

use App\Models\Deposito;
use Filament\Pages\Page;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class JatuhTempo extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-calendar';
    protected static ?string $navigationLabel = 'Jatuh Tempo';
    protected static ?string $title = 'Daftar Jatuh Tempo Deposito';
    protected static string $view = 'filament.pages.jatuh-tempo';
    protected static ?string $navigationGroup = 'Deposito';

    public $periode = 'bulan-ini';

    public function getTableQuery()
    {
        $query = Deposito::query()->with('profile');

        switch($this->periode) {
            case 'bulan-ini':
                $query->whereBetween('tanggal_jatuh_tempo', [
                    Carbon::now()->startOfMonth(),
                    Carbon::now()->endOfMonth()
                ]);
                break;
            case 'bulan-depan':
                $query->whereBetween('tanggal_jatuh_tempo', [
                    Carbon::now()->addMonth()->startOfMonth(),
                    Carbon::now()->addMonth()->endOfMonth()
                ]);
                break;
            case 'tahun-depan':
                $query->whereBetween('tanggal_jatuh_tempo', [
                    Carbon::now()->addYear()->startOfYear(),
                    Carbon::now()->addYear()->endOfYear()
                ]);
                break;
        }

        return $query;
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
                ->formatStateUsing(fn ($record) => "{$record->profile->first_name} {$record->profile->last_name}")
                ->searchable()
                ->sortable(),
            TextColumn::make('nominal_penempatan')
                ->label('Nominal')
                ->money('IDR')
                ->sortable(),
            TextColumn::make('jangka_waktu')
                ->label('Jangka Waktu')
                ->suffix(' Bulan')
                ->sortable(),
            TextColumn::make('tanggal_jatuh_tempo')
                ->label('Jatuh Tempo')
                ->date('d M Y')
                ->sortable(),
            TextColumn::make('nominal_bunga')
                ->label('Bunga')
                ->money('IDR')
                ->sortable(),
            TextColumn::make('total_penarikan')
                ->label('Total Penarikan')
                ->money('IDR')
                ->getStateUsing(fn ($record) => $record->nominal_penempatan + $record->nominal_bunga)
                ->sortable(),
        ];
    }

    protected function getFormSchema(): array
    {
        return [
            Select::make('periode')
                ->label('Periode Jatuh Tempo')
                ->options([
                    'bulan-ini' => 'Bulan Ini',
                    'bulan-depan' => 'Bulan Depan',
                    'tahun-depan' => 'Tahun Depan'
                ])
                ->default('bulan-ini')
                ->live()
                ->afterStateUpdated(function() {
                    $this->resetTable();
                })
        ];
    }

    public function cetakPDF()
    {
        $data = $this->getTableQuery()->get();

        $pdf = Pdf::loadView('pdf.jatuh-tempo', [
            'data' => $data,
            'periode' => $this->periode
        ]);

        return response()->streamDownload(function() use($pdf) {
            echo $pdf->output();
        }, 'jatuh-tempo-deposito.pdf');
    }
}
