<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\TransaksiTabungan;
use App\Models\Tabungan;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Dompdf\Dompdf;
use Dompdf\Options;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Malzariey\FilamentDaterangepickerFilter\Filters\DateRangeFilter;

class MutasiTabungan extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'Mutasi Tabungan';
    protected static ?string $navigationGroup = 'Tabungan';
    protected static string $view = 'filament.pages.mutasi-tabungan';

    public $no_rekening = '';
    public $saldo_berjalan = 0;
    public $isSearchSubmitted = false;
    public $firstRecord = null;
    public $tabungan = null;

    public function mount()
    {
        try {
            $this->reset();
            Log::info('MutasiTabungan mounted successfully');
        } catch (\Exception $e) {
            Log::error('Error in mount: ' . $e->getMessage());
            throw $e;
        }
    }

    public function search()
    {
        try {
            $this->validate([
                'no_rekening' => 'required'
            ]);

            Log::info('Searching for account: ' . $this->no_rekening);

            $this->resetSearchState();

            $this->tabungan = $this->findTabungan();

            if ($this->tabungan) {
                $this->handleSuccessfulSearch();
            } else {
                $this->handleFailedSearch();
            }
        } catch (\Exception $e) {
            Log::error('Error in search: ' . $e->getMessage());
            Notification::make()
                ->title('Terjadi kesalahan')
                ->danger()
                ->send();
        }
    }

    private function resetSearchState()
    {
        $this->reset(['saldo_berjalan', 'firstRecord', 'tabungan']);
        $this->isSearchSubmitted = false;
        $this->resetTable();
    }

    private function findTabungan()
    {
        return Tabungan::with(['profile', 'produkTabungan'])
            ->where('no_tabungan', $this->no_rekening)
            ->first();
    }

    private function handleSuccessfulSearch()
    {
        $this->saldo_berjalan = $this->tabungan->saldo;
        $this->isSearchSubmitted = true;
        $this->dispatch('refresh');
        Log::info('Account found: ' . $this->no_rekening);
    }

    private function handleFailedSearch()
    {
        Log::warning('Account not found: ' . $this->no_rekening);
        Notification::make()
            ->title('Rekening tidak ditemukan')
            ->danger()
            ->send();
    }

    public function clearSearch()
    {
        try {
            $this->reset();
            $this->resetTable();

            cache()->forget('mutasi_tabungan_' . $this->no_rekening);
            session()->forget('mutasi_tabungan_search');

            $this->resetSearchProperties();

            $this->dispatch('refresh');

            Log::info('Search cleared successfully');
        } catch (\Exception $e) {
            Log::error('Error in clearSearch: ' . $e->getMessage());
            throw $e;
        }
    }

    private function resetSearchProperties()
    {
        $this->isSearchSubmitted = false;
        $this->tabungan = null;
        $this->saldo_berjalan = 0;
        $this->firstRecord = null;
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('no_rekening')
                    ->label('Nomor Rekening')
                    ->required()
                    ->placeholder('Masukkan nomor rekening')
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())
            ->columns($this->getTableColumns())
            ->filters([
                DateRangeFilter::make('tanggal_transaksi')
                    ->label('Rentang Waktu')
                    ->placeholder('Pilih rentang waktu')
            ])
            ->defaultSort('tanggal_transaksi', 'ASC');
    }

    private function getTableQuery()
    {
        return function () {
            if (!$this->isSearchSubmitted || !$this->tabungan) {
                return TransaksiTabungan::query()->whereNull('id');
            }

            return TransaksiTabungan::query()
                ->where('id_tabungan', $this->tabungan->id)
                ->orderBy('tanggal_transaksi', 'ASC')
                ->orderBy('id', 'ASC');
        };
    }

    private function getTableColumns()
    {
        return [
            TextColumn::make('kode_transaksi')
                ->label('Kode Transaksi'),
            TextColumn::make('tanggal_transaksi')
                ->label('Tanggal')
                ->dateTime()
                ->sortable()
                ->toggleable()
                ->searchable()
                ->sortable(['tanggal_transaksi']),
            TextColumn::make('kredit')
                ->label('Kredit')
                ->money('IDR')
                ->getStateUsing(fn ($record) => $record->jenis_transaksi === 'kredit' ? $record->jumlah : null),
            TextColumn::make('debit')
                ->label('Debit')
                ->money('IDR')
                ->getStateUsing(fn ($record) => $record->jenis_transaksi === 'debit' ? $record->jumlah : null),
            TextColumn::make('saldo_berjalan')
                ->label('Saldo')
                ->money('IDR')
                ->getStateUsing(fn ($record) => $this->calculateSaldoBerjalan($record)),
            TextColumn::make('kode_teller')
                ->label('Kode Teller')
        ];
    }

    private function calculateSaldoBerjalan($record)
    {
        try {
            if (!$this->tabungan || $record->id_tabungan !== $this->tabungan->id) {
                return 0;
            }

            $saldo = $this->tabungan->saldo;
            $previousTransactions = $this->getPreviousTransactions($record);

            foreach ($previousTransactions as $transaction) {
                $saldo = $this->updateSaldo($saldo, $transaction);
            }

            return $saldo;
        } catch (\Exception $e) {
            Log::error('Error calculating saldo berjalan: ' . $e->getMessage());
            return 0;
        }
    }

    private function getPreviousTransactions($record)
    {
        return TransaksiTabungan::where('id_tabungan', $this->tabungan->id)
            ->where(function($query) use ($record) {
                $query->where('tanggal_transaksi', '<', $record->tanggal_transaksi)
                    ->orWhere(function($q) use ($record) {
                        $q->where('tanggal_transaksi', '=', $record->tanggal_transaksi)
                          ->where('id', '<=', $record->id);
                    });
            })
            ->orderBy('tanggal_transaksi', 'ASC')
            ->orderBy('id', 'ASC')
            ->get();
    }

    private function updateSaldo($saldo, $transaction)
    {
        return $transaction->jenis_transaksi === 'debit'
            ? $saldo + $transaction->jumlah
            : $saldo - $transaction->jumlah;
    }

    public function print()
    {
        try {
            if (!$this->tabungan) {
                Notification::make()
                    ->title('Silahkan cari data terlebih dahulu')
                    ->warning()
                    ->send();
                return;
            }

            $options = new Options();
            $options->set('isHtml5ParserEnabled', true);
            $options->set('isPhpEnabled', true);
            $options->set('defaultFont', 'Arial');

            $dompdf = new Dompdf($options);
            $dompdf->setPaper('A4', 'portrait');

            $query = $this->getFilteredTransactionQuery();
            $transaksi = $this->processTransactions($query);

            $html = $this->generatePdfHtml($transaksi);

            $dompdf->loadHtml($html);
            $dompdf->render();

            $filename = $this->generatePdfFilename();

            return response()->streamDownload(
                fn () => print($dompdf->output()),
                $filename,
                ['Content-Type' => 'application/pdf']
            );

        } catch (\Exception $e) {
            Log::error('Error in print: ' . $e->getMessage());
            Notification::make()
                ->title('Terjadi kesalahan saat mencetak')
                ->danger()
                ->send();
            return null;
        }
    }

    private function getFilteredTransactionQuery()
    {
        $query = TransaksiTabungan::query()
            ->where('id_tabungan', $this->tabungan->id);

        $dateFilter = $this->getTableFilterState('tanggal_transaksi');
        if ($dateFilter) {
            if (!empty($dateFilter['start'])) {
                $query->whereDate('tanggal_transaksi', '>=', $dateFilter['start']);
            }
            if (!empty($dateFilter['end'])) {
                $query->whereDate('tanggal_transaksi', '<=', $dateFilter['end']);
            }
        }

        return $query->orderBy('tanggal_transaksi', 'ASC')
                    ->orderBy('id', 'ASC');
    }

    private function processTransactions($query)
    {
        return $query->get()->map(function ($record) {
            $record->saldo_berjalan = $this->calculateSaldoBerjalan($record);
            return $record;
        });
    }

    private function generatePdfHtml($transaksi)
    {
        $dateFilter = $this->getTableFilterState('tanggal_transaksi');
        return view('pdf.mutasi-tabungan', [
            'tabungan' => $this->tabungan,
            'transaksi' => $transaksi,
            'saldo_berjalan' => $this->saldo_berjalan,
            'filter_date' => $dateFilter ? [
                'start' => $dateFilter['start'] ?? null,
                'end' => $dateFilter['end'] ?? null,
            ] : null
        ])->render();
    }

    private function generatePdfFilename()
    {
        $filename = 'mutasi_' . $this->no_rekening;
        $dateFilter = $this->getTableFilterState('tanggal_transaksi');
        if ($dateFilter) {
            $start = $dateFilter['start'] ?? '';
            $end = $dateFilter['end'] ?? '';
            if ($start && $end) {
                $filename .= "_{$start}_{$end}";
            }
        }
        return $filename . '_' . date('Y-m-d_H-i-s') . '.pdf';
    }

}
