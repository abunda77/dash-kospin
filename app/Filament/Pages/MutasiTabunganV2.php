<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\TransaksiTabungan;
use App\Models\Tabungan;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Forms\Components\Actions\Action;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Facades\Blade;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;
use Dompdf\Dompdf;
use Dompdf\Options;

class MutasiTabunganV2 extends Page implements HasTable, HasForms
{
    use InteractsWithTable;
    use InteractsWithForms;
    use HasPageShield;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'Mutasi Tabungan V2';
    protected static string $view = 'filament.pages.mutasi-tabungan-v2';
    public static function getNavigationGroup(): ?string
            {
                return 'Tabungan';
            }

    public $isSearchSubmitted = false;
    public $no_rekening = '';
    public $periode = '';
    public $saldo_berjalan = 0;
    public $tabungan = null;
    public $filterDate = [];
    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    protected function getFormSchema(): array
    {
        return [
            Wizard::make([
                Wizard\Step::make('Pencarian')
                    ->schema([
                        TextInput::make('no_rekening')
                            ->label('Nomor Rekening')
                            ->required()
                            ->placeholder('Masukkan nomor rekening'),
                    ]),
                Wizard\Step::make('Periode')
                    ->schema([
                        Select::make('periode')
                            ->label('Pilih Periode')
                            ->options([
                                '7_weeks' => '7 Minggu Terakhir',
                                '1_month' => '1 Bulan Terakhir',
                                '3_months' => '3 Bulan Terakhir',
                                '6_months' => '6 Bulan Terakhir',
                                '1_year' => '1 Tahun Terakhir',
                                'all' => 'Semua',
                            ])
                            ->required(),
                    ]),
            ])
            ->nextAction(fn (Action $action) => $action->label('Lanjut'))
            ->submitAction(new HtmlString(
                '<button type="submit" wire:click="search()" wire:loading.class="animate-spin" class="filament-button filament-button-size-md inline-flex items-center justify-center py-1 gap-1 font-medium rounded-lg border transition-colors outline-none focus:ring-offset-2 focus:ring-2 focus:ring-inset min-h-[2.25rem] px-4 text-sm text-white shadow focus:ring-white border-transparent bg-primary-600 hover:bg-primary-500 focus:bg-primary-700 focus:ring-offset-primary-700">Cari Data</button>'
            ))
            ->live()
        ];
    }

    public function search(): void
    {
        $state = $this->form->getState();

        try {
            $this->validateSearchData($state);
            $this->findTabungan($state);
        } catch (\Exception $e) {
            $this->handleSearchError($e);
        }
    }

    protected function validateSearchData(array $data): void
    {
        if (!isset($data['no_rekening']) || !isset($data['periode'])) {
            throw new \InvalidArgumentException('Data pencarian tidak lengkap');
        }

        $this->no_rekening = $data['no_rekening'];
        $this->periode = $data['periode'];
    }

    protected function findTabungan(array $data): void
    {
        $this->tabungan = Tabungan::with(['profile', 'produkTabungan'])
            ->where('no_tabungan', $data['no_rekening'])
            ->first();

        if ($this->tabungan) {
            $this->handleTabunganFound();
        } else {
            $this->showTabunganNotFoundNotification();
        }
    }

    protected function handleTabunganFound(): void
    {
        $this->saldo_berjalan = $this->tabungan->saldo;
        $this->isSearchSubmitted = true;
        $this->dispatch('refresh');
    }

    protected function showTabunganNotFoundNotification(): void
    {
        Notification::make()
            ->title('Rekening tidak ditemukan')
            ->danger()
            ->send();
    }

    protected function handleSearchError(\Exception $e): void
    {
        Log::error('Error in search: ' . $e->getMessage());
        Notification::make()
            ->title('Terjadi kesalahan')
            ->danger()
            ->send();
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())
            ->columns([
                TextColumn::make('kode_transaksi')
                    ->label('Kode Transaksi'),
                TextColumn::make('tanggal_transaksi')
                    ->label('Tanggal')
                    ->dateTime()
                    ->sortable(),
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
            ])
            ->defaultSort('tanggal_transaksi', 'ASC');
    }

    protected function getTableQuery(): callable
    {
        return function () {
            if (!$this->isSearchSubmitted || !$this->tabungan) {
                return TransaksiTabungan::query()->whereNull('id');
            }

            return $this->buildTransactionQuery();
        };
    }

    protected function buildTransactionQuery()
    {
        $query = TransaksiTabungan::query()
            ->where('id_tabungan', $this->tabungan->id);

        if ($this->periode && $this->periode !== 'all') {
            $startDate = $this->calculateStartDate();
            if ($startDate) {
                $query->where('tanggal_transaksi', '>=', $startDate);
            }
        }

        return $query->orderBy('tanggal_transaksi', 'ASC')
                    ->orderBy('id', 'ASC');
    }

    protected function calculateStartDate(): ?Carbon
    {
        return match($this->periode) {
            '7_weeks' => now()->subWeeks(7),
            '1_month' => now()->subMonth(),
            '3_months' => now()->subMonths(3),
            '6_months' => now()->subMonths(6),
            '1_year' => now()->subYear(),
            default => null,
        };
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
            $options->set('isRemoteEnabled', true);
            $options->set('chroot', public_path());

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

    public function printTable()
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
            $options->set('isRemoteEnabled', true);
            $options->set('chroot', public_path());

            $dompdf = new Dompdf($options);
            $dompdf->setPaper(array(0, 0, 368.504, 510.236), 'portrait');

            $query = $this->getFilteredTransactionQuery();
            $transaksi = $this->processTransactions($query);

            $html = $this->generateTablePdfHtml($transaksi);

            $dompdf->loadHtml($html);
            $dompdf->render();

            $filename = $this->generateTablePdfFilename();

            return response()->streamDownload(
                fn () => print($dompdf->output()),
                $filename,
                ['Content-Type' => 'application/pdf']
            );
        } catch (\Exception $e) {
            Log::error('Error in printTable: ' . $e->getMessage());
            Notification::make()
                ->title('Terjadi kesalahan saat mencetak tabel')
                ->danger()
                ->send();
            return null;
        }
    }

    private function getFilteredTransactionQuery()
    {
        try {
            $query = TransaksiTabungan::query()
                ->where('id_tabungan', $this->tabungan->id);

            if ($this->periode && $this->periode !== 'all') {
                $startDate = $this->calculateStartDate();
                if ($startDate) {
                    $query->where('tanggal_transaksi', '>=', $startDate);
                }
            }

            return $query->orderBy('tanggal_transaksi', 'ASC')
                        ->orderBy('id', 'ASC');
        } catch (\Exception $e) {
            Log::error('Error saat filter transaksi:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw new \Exception('Terjadi kesalahan saat memfilter data: ' . $e->getMessage());
        }
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
        try {
            return view('pdf.mutasi-tabungan-v2', [
                'tabungan' => $this->tabungan,
                'transaksi' => $transaksi,
                'saldo_berjalan' => $this->saldo_berjalan,
                'periode' => $this->periode
            ])->render();
        } catch (\Exception $e) {
            Log::error('Error saat generate PDF:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw new \Exception('Terjadi kesalahan saat menyiapkan PDF: ' . $e->getMessage());
        }
    }

    private function generateTablePdfHtml($transaksi)
    {
        return view('pdf.mutasi-tabungan-table-v2', [
            'tabungan' => $this->tabungan,
            'transaksi' => $transaksi,
            'periode' => $this->periode
        ])->render();
    }

    private function generatePdfFilename()
    {
        return 'mutasi_' . $this->no_rekening . '_' . date('Y-m-d_H-i-s') . '.pdf';
    }

    private function generateTablePdfFilename()
    {
        return 'tabel_mutasi_' . $this->no_rekening . '_' . date('Y-m-d_H-i-s') . '.pdf';
    }

    // ... rest of the code remains the same ...
}
