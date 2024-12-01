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
            ->heading(
                fn() => $this->isSearchSubmitted ? $this->getAccountInfo() : null
            )
            ->columns($this->getTableColumns())
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
                ->dateTime(),
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

    private function getAccountInfo()
    {
        try {
            if (!$this->tabungan) {
                $this->handleMissingAccount();
                return null;
            }

            return $this->renderAccountInfo();
        } catch (\Exception $e) {
            Log::error('Error getting account info: ' . $e->getMessage());
            return null;
        }
    }

    private function handleMissingAccount()
    {
        Log::warning('Attempt to get account info with no account selected');
        Notification::make()
            ->title('Rekening tidak ditemukan')
            ->danger()
            ->send();
    }

    private function renderAccountInfo()
    {
        return view('filament.components.account-info', [
            'nama' => $this->tabungan->profile->first_name . ' ' . $this->tabungan->profile->last_name,
            'no_rekening' => $this->tabungan->no_tabungan,
            'saldo' => $this->tabungan->saldo,
            'jenis_tabungan' => $this->tabungan->produkTabungan->nama_produk
        ]);
    }
}
