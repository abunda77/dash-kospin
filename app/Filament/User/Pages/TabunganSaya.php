<?php

namespace App\Filament\User\Pages;

use Filament\Pages\Page;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Infolists\Concerns\InteractsWithInfolists;
use Filament\Infolists\Contracts\HasInfolists;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\RepeatableEntry;
use App\Models\Tabungan;
use App\Models\TransaksiTabungan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

class TabunganSaya extends Page implements HasTable, HasInfolists
{
    use InteractsWithTable, InteractsWithInfolists;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    
    protected static ?string $navigationLabel = 'Tabungan Saya';
    
    protected static ?string $title = 'Tabungan Saya';

    protected static string $view = 'filament.user.pages.tabungan-saya';
    
    protected static ?int $navigationSort = 10;

    public ?Tabungan $selectedTabungan = null;

    /**
     * Hitung saldo akhir tabungan berdasarkan saldo awal + transaksi
     * Rumus: saldo_akhir = saldo_awal + (total_debit - total_kredit)
     */
    public function hitungSaldoAkhir(Tabungan $tabungan): float
    {
        $saldoAwal = $tabungan->saldo;

        $totalDebit = TransaksiTabungan::where('id_tabungan', $tabungan->id)
            ->where('jenis_transaksi', 'debit')
            ->sum('jumlah');

        $totalKredit = TransaksiTabungan::where('id_tabungan', $tabungan->id)
            ->where('jenis_transaksi', 'kredit')
            ->sum('jumlah');

        return $saldoAwal + ($totalDebit - $totalKredit);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())
            ->columns([
                TextColumn::make('no_tabungan')
                    ->label('No. Tabungan')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('Nomor tabungan disalin!')
                    ->weight('bold')
                    ->color('primary'),

                TextColumn::make('produkTabungan.nama_produk')
                    ->label('Produk')
                    ->searchable()
                    ->badge()
                    ->color('info'),

                TextColumn::make('saldo_akhir')
                    ->label('Saldo')
                    ->state(fn (Tabungan $record) => $this->hitungSaldoAkhir($record))
                    ->money('IDR', locale: 'id')
                    ->weight('bold')
                    ->color('success')
                    ->alignEnd(),

                TextColumn::make('tanggal_buka_rekening')
                    ->label('Tanggal Buka')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('status_rekening')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'aktif', 'Aktif' => 'success',
                        'tutup', 'Tutup' => 'danger',
                        'blokir', 'Blokir' => 'warning',
                        default => 'gray',
                    }),
            ])
            ->defaultSort('tanggal_buka_rekening', 'desc')
            ->striped()
            ->actions([
                \Filament\Tables\Actions\Action::make('detail')
                    ->label('Lihat Detail')
                    ->icon('heroicon-o-eye')
                    ->color('primary')
                    ->action(fn (Tabungan $record) => $this->selectedTabungan = $record),
            ])
            ->emptyStateHeading('Belum Ada Tabungan')
            ->emptyStateDescription('Anda belum memiliki rekening tabungan.')
            ->emptyStateIcon('heroicon-o-banknotes');
    }

    protected function getTableQuery(): Builder
    {
        $profile = Auth::user()->profile;
        
        if (!$profile) {
            return Tabungan::query()->whereRaw('1 = 0'); // Return empty query
        }

        return Tabungan::query()
            ->where('id_profile', $profile->id_user)
            ->with(['produkTabungan', 'transaksi']);
    }

    public function tabunganInfolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->record($this->selectedTabungan)
            ->schema([
                Section::make('Detail Tabungan')
                    ->icon('heroicon-o-banknotes')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('no_tabungan')
                                    ->label('Nomor Tabungan')
                                    ->weight('bold')
                                    ->size('lg')
                                    ->copyable()
                                    ->copyMessage('Nomor tabungan disalin!'),

                                TextEntry::make('produkTabungan.nama_produk')
                                    ->label('Produk Tabungan')
                                    ->badge()
                                    ->color('info'),

                                TextEntry::make('status_rekening')
                                    ->label('Status')
                                    ->badge()
                                    ->color(fn (string $state): string => match ($state) {
                                        'aktif', 'Aktif' => 'success',
                                        'tutup', 'Tutup' => 'danger',
                                        'blokir', 'Blokir' => 'warning',
                                        default => 'gray',
                                    }),
                            ]),

                        Grid::make(2)
                            ->schema([
                                TextEntry::make('saldo_akhir')
                                    ->label('Saldo Saat Ini')
                                    ->state(fn ($record) => $this->hitungSaldoAkhir($record))
                                    ->money('IDR', locale: 'id')
                                    ->size('lg')
                                    ->weight('bold')
                                    ->color('success'),

                                TextEntry::make('tanggal_buka_rekening')
                                    ->label('Tanggal Buka Rekening')
                                    ->date('d F Y'),
                            ]),
                    ]),

                Section::make('Transaksi Terakhir')
                    ->icon('heroicon-o-arrow-path')
                    ->schema([
                        RepeatableEntry::make('transaksi')
                            ->label('')
                            ->schema([
                                Grid::make(5)
                                    ->schema([
                                        TextEntry::make('tanggal_transaksi')
                                            ->label('Tanggal')
                                            ->date('d/m/Y H:i'),

                                        TextEntry::make('kode_transaksi')
                                            ->label('Kode')
                                            ->badge(),

                                        TextEntry::make('jenis_transaksi')
                                            ->label('Jenis')
                                            ->badge()
                                            ->color(fn (string $state): string => match ($state) {
                                                'debit' => 'success',
                                                'kredit' => 'danger',
                                                default => 'gray',
                                            })
                                            ->formatStateUsing(fn (string $state): string => match ($state) {
                                                'debit' => 'Setoran',
                                                'kredit' => 'Penarikan',
                                                default => $state,
                                            }),

                                        TextEntry::make('jumlah')
                                            ->label('Jumlah')
                                            ->money('IDR', locale: 'id')
                                            ->weight('bold'),

                                        TextEntry::make('keterangan')
                                            ->label('Keterangan')
                                            ->placeholder('-'),
                                    ]),
                            ])
                            ->contained(false)
                    ])
                    ->collapsible(),
            ]);
    }

    public function closeDetail(): void
    {
        $this->selectedTabungan = null;
    }

    public function getTotalSaldo(): string
    {
        $profile = Auth::user()->profile;
        
        if (!$profile) {
            return format_rupiah(0);
        }

        $tabungans = Tabungan::where('id_profile', $profile->id_user)
            ->where('status_rekening', 'aktif')
            ->get();

        $totalSaldo = 0;
        foreach ($tabungans as $tabungan) {
            $totalSaldo += $this->hitungSaldoAkhir($tabungan);
        }

        return format_rupiah($totalSaldo);
    }

    public function getJumlahTabungan(): int
    {
        $profile = Auth::user()->profile;
        
        if (!$profile) {
            return 0;
        }

        return Tabungan::where('id_profile', $profile->id_user)->count();
    }
}
