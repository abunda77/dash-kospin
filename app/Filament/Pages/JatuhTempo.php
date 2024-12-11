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
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Illuminate\Support\Facades\Log;

class JatuhTempo extends Page implements HasTable
{
    use InteractsWithTable;
    use HasPageShield;

    protected static ?string $navigationIcon = 'heroicon-o-calendar';
    protected static ?string $navigationLabel = 'Jatuh Tempo';
    protected static ?string $title = 'Daftar Jatuh Tempo Deposito';
    protected static string $view = 'filament.pages.jatuh-tempo';
    protected static ?string $navigationGroup = 'Deposito';

    public $periode = 'bulan-ini';

    private function getDateRange(string $periode): array
    {
        $ranges = [
            'bulan-ini' => [
                Carbon::now()->startOfMonth(),
                Carbon::now()->endOfMonth()
            ],
            'bulan-depan' => [
                Carbon::now()->addMonth()->startOfMonth(),
                Carbon::now()->addMonth()->endOfMonth()
            ],
            'tahun-depan' => [
                Carbon::now()->addYear()->startOfYear(),
                Carbon::now()->addYear()->endOfYear()
            ]
        ];

        return $ranges[$periode] ?? $ranges['bulan-ini'];
    }

    public function getTableQuery()
    {
        try {
            $query = Deposito::query()->with('profile');

            list($startDate, $endDate) = $this->getDateRange($this->periode);

            Log::info("Querying deposits for period: {$this->periode}", [
                'start_date' => $startDate,
                'end_date' => $endDate
            ]);

            return $query->whereBetween('tanggal_jatuh_tempo', [$startDate, $endDate]);

        } catch (\Exception $e) {
            Log::error("Error in getTableQuery: " . $e->getMessage());
            throw $e;
        }
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
                ->formatStateUsing(function($record) {
                    return trim("{$record->profile->first_name} {$record->profile->last_name}");
                })
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
                ->getStateUsing(function($record) {
                    return $record->nominal_penempatan + $record->nominal_bunga;
                })
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
        try {
            Log::info("Generating PDF for period: {$this->periode}");

            $data = $this->getTableQuery()->get();

            if ($data->isEmpty()) {
                Log::warning("No data found for PDF generation");
            }

            $pdf = Pdf::loadView('pdf.jatuh-tempo', [
                'data' => $data,
                'periode' => $this->periode
            ]);

            return response()->streamDownload(function() use($pdf) {
                echo $pdf->output();
            }, "jatuh-tempo-deposito-{$this->periode}.pdf");

        } catch (\Exception $e) {
            Log::error("Error generating PDF: " . $e->getMessage());
            throw $e;
        }
    }
}
