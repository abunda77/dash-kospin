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
        $this->reset();
    }

    public function search()
{
    $this->validate([
        'no_rekening' => 'required'
    ]);

    // Reset semua state terlebih dahulu
    $this->reset(['saldo_berjalan', 'firstRecord', 'tabungan']);
    $this->isSearchSubmitted = false;

    // Reset table state untuk memastikan data bersih
    $this->resetTable();

    // Cari tabungan berdasarkan nomor rekening
    $this->tabungan = Tabungan::with(['profile', 'produkTabungan'])
        ->where('no_tabungan', $this->no_rekening)
        ->first();

    if ($this->tabungan) {
        // Set saldo awal
        $this->saldo_berjalan = $this->tabungan->saldo;

        // Ambil transaksi pertama untuk inisialisasi saldo
        $firstTransaction = TransaksiTabungan::where('id_tabungan', $this->tabungan->id)
            ->orderBy('tanggal_transaksi', 'ASC')
            ->first();

        if ($firstTransaction) {
            $this->firstRecord = $firstTransaction;
        }

        $this->isSearchSubmitted = true;

        // Refresh tabel dengan data baru
        $this->dispatch('refresh');
    } else {
        Notification::make()
            ->title('Rekening tidak ditemukan')
            ->danger()
            ->send();
    }
}

                public function clearSearch()
    {
        // Reset semua properti
        $this->reset();

        // Reset state tabel
        $this->resetTable();

        // Hapus cache pencarian
        cache()->forget('mutasi_tabungan_' . $this->no_rekening);

        // Reset flag pencarian
        $this->isSearchSubmitted = false;

        // Hapus data tabungan
        $this->tabungan = null;
        $this->saldo_berjalan = 0;
        $this->firstRecord = null;

        // Bersihkan session
        session()->forget('mutasi_tabungan_search');

        // Refresh halaman untuk membersihkan state
        $this->dispatch('refresh');
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
        ->query(
            TransaksiTabungan::query()
                ->when($this->isSearchSubmitted && $this->tabungan, function($query) {
                    return $query->where('id_tabungan', $this->tabungan->id)
                                ->orderBy('tanggal_transaksi', 'ASC');
                })
        )
        ->heading(
            fn() => $this->isSearchSubmitted ? $this->getAccountInfo() : null
        )
            ->columns([
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
                    ->getStateUsing(function ($record) {
                        // Hitung saldo berjalan berdasarkan saldo awal
                        $saldo = $this->tabungan->saldo;

                        // Ambil semua transaksi sebelum record saat ini
                        $previousTransactions = TransaksiTabungan::where('id_tabungan', $this->tabungan->id)
                            ->where('tanggal_transaksi', '<=', $record->tanggal_transaksi)
                            ->where('id', '<=', $record->id)
                            ->orderBy('tanggal_transaksi', 'ASC')
                            ->orderBy('id', 'ASC')
                            ->get();

                        // Hitung saldo berjalan
                        foreach ($previousTransactions as $transaction) {
                            if ($transaction->jenis_transaksi === 'debit') {
                                $saldo += $transaction->jumlah;
                            } else {
                                $saldo -= $transaction->jumlah;
                            }
                        }

                        return $saldo;
                    }),
                TextColumn::make('kode_teller')
                    ->label('Kode Teller')
            ])
            ->defaultSort('tanggal_transaksi', 'ASC');
    }

    private function getAccountInfo()
    {
        if (!$this->tabungan) {
            Notification::make()
                ->title('Rekening tidak ditemukan')
                ->danger()
                ->send();

            return null;
        }

        return view('filament.components.account-info', [
            'nama' => $this->tabungan->profile->first_name . ' ' . $this->tabungan->profile->last_name,
            'no_rekening' => $this->tabungan->no_tabungan,
            'saldo' => $this->tabungan->saldo,
            'jenis_tabungan' => $this->tabungan->produkTabungan->nama_produk
        ]);
    }
}
