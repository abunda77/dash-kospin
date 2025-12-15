<?php

namespace App\Filament\User\Pages;

use Filament\Pages\Page;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Infolists\Concerns\InteractsWithInfolists;
use Filament\Infolists\Contracts\HasInfolists;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\RepeatableEntry;
use App\Models\Pinjaman;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class PinjamanSaya extends Page implements HasTable, HasInfolists
{
    use InteractsWithTable, InteractsWithInfolists;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';
    
    protected static ?string $navigationLabel = 'Pinjaman Saya';
    
    protected static ?string $title = 'Pinjaman Saya';

    protected static string $view = 'filament.user.pages.pinjaman-saya';
    
    protected static ?int $navigationSort = 20;

    public ?Pinjaman $selectedPinjaman = null;

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())
            ->columns([
                TextColumn::make('no_pinjaman')
                    ->label('No. Pinjaman')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('Nomor pinjaman disalin!')
                    ->weight('bold')
                    ->color('primary'),

                TextColumn::make('produkPinjaman.nama_produk')
                    ->label('Produk')
                    ->searchable()
                    ->badge()
                    ->color('info'),

                TextColumn::make('jumlah_pinjaman')
                    ->label('Jumlah Pinjaman')
                    ->money('IDR', locale: 'id')
                    ->weight('bold')
                    ->alignEnd(),

                TextColumn::make('jangka_waktu')
                    ->label('Tenor')
                    ->formatStateUsing(fn ($state, $record) => $state . ' ' . $record->jangka_waktu_satuan)
                    ->badge()
                    ->color('gray'),

                TextColumn::make('tanggal_pinjaman')
                    ->label('Tanggal Pinjaman')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('tanggal_jatuh_tempo')
                    ->label('Jatuh Tempo')
                    ->date('d M Y')
                    ->color(fn ($state) => Carbon::parse($state)->isPast() ? 'danger' : 'success'),

                TextColumn::make('status_pinjaman')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match (strtolower($state)) {
                        'pending' => 'Menunggu',
                        'approved' => 'Disetujui',
                        'rejected' => 'Ditolak',
                        'completed' => 'Lunas',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match (strtolower($state)) {
                        'approved' => 'success',
                        'pending' => 'warning',
                        'completed' => 'info',
                        'rejected' => 'danger',
                        default => 'gray',
                    }),
            ])
            ->defaultSort('tanggal_pinjaman', 'desc')
            ->striped()
            ->actions([
                \Filament\Tables\Actions\Action::make('detail')
                    ->label('Lihat Detail')
                    ->icon('heroicon-o-eye')
                    ->color('primary')
                    ->action(fn (Pinjaman $record) => $this->selectedPinjaman = $record),
            ])
            ->emptyStateHeading('Belum Ada Pinjaman')
            ->emptyStateDescription('Anda belum memiliki pinjaman.')
            ->emptyStateIcon('heroicon-o-credit-card');
    }

    protected function getTableQuery(): Builder
    {
        $profile = Auth::user()->profile;
        
        if (!$profile) {
            return Pinjaman::query()->whereRaw('1 = 0');
        }

        return Pinjaman::query()
            ->where('profile_id', $profile->id_user)
            ->with(['produkPinjaman', 'biayaBungaPinjaman', 'transaksiPinjaman', 'denda']);
    }

    public function pinjamanInfolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->record($this->selectedPinjaman)
            ->schema([
                Section::make('Informasi Pinjaman')
                    ->icon('heroicon-o-credit-card')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('no_pinjaman')
                                    ->label('Nomor Pinjaman')
                                    ->weight('bold')
                                    ->size('lg')
                                    ->copyable()
                                    ->copyMessage('Nomor pinjaman disalin!'),

                                TextEntry::make('produkPinjaman.nama_produk')
                                    ->label('Produk Pinjaman')
                                    ->badge()
                                    ->color('info'),

                                TextEntry::make('status_pinjaman')
                                    ->label('Status')
                                    ->badge()
                                    ->formatStateUsing(fn (string $state): string => match (strtolower($state)) {
                                        'pending' => 'Menunggu',
                                        'approved' => 'Disetujui',
                                        'rejected' => 'Ditolak',
                                        'completed' => 'Lunas',
                                        default => $state,
                                    })
                                    ->color(fn (string $state): string => match (strtolower($state)) {
                                        'approved' => 'success',
                                        'pending' => 'warning',
                                        'completed' => 'info',
                                        'rejected' => 'danger',
                                        default => 'gray',
                                    }),
                            ]),

                        Grid::make(2)
                            ->schema([
                                TextEntry::make('jumlah_pinjaman')
                                    ->label('Jumlah Pinjaman')
                                    ->money('IDR', locale: 'id')
                                    ->size('lg')
                                    ->weight('bold')
                                    ->color('danger'),

                                TextEntry::make('biayaBungaPinjaman.persentase_bunga')
                                    ->label('Suku Bunga')
                                    ->suffix('% / bulan')
                                    ->placeholder('-'),
                            ]),

                        Grid::make(4)
                            ->schema([
                                TextEntry::make('tanggal_pinjaman')
                                    ->label('Tanggal Pinjaman')
                                    ->date('d F Y'),

                                TextEntry::make('jangka_waktu')
                                    ->label('Jangka Waktu')
                                    ->formatStateUsing(fn ($state, $record) => $state . ' ' . $record->jangka_waktu_satuan),

                                TextEntry::make('tanggal_jatuh_tempo')
                                    ->label('Jatuh Tempo')
                                    ->date('d F Y')
                                    ->color(fn ($state) => Carbon::parse($state)->isPast() ? 'danger' : 'success'),

                                TextEntry::make('sisa_hari')
                                    ->label('Sisa Waktu')
                                    ->state(function ($record) {
                                        $jatuhTempo = Carbon::parse($record->tanggal_jatuh_tempo);
                                        $now = Carbon::now();
                                        
                                        if ($jatuhTempo->isPast()) {
                                            return 'Lewat ' . $now->diffInDays($jatuhTempo) . ' hari';
                                        }
                                        
                                        return $now->diffInDays($jatuhTempo) . ' hari lagi';
                                    })
                                    ->badge()
                                    ->color(fn ($state) => str_contains($state, 'Lewat') ? 'danger' : 'success'),
                            ]),
                    ]),

                Section::make('Informasi Biaya')
                    ->icon('heroicon-o-calculator')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('biayaBungaPinjaman.biaya_administrasi')
                                    ->label('Biaya Administrasi')
                                    ->money('IDR', locale: 'id')
                                    ->placeholder('-'),

                                TextEntry::make('denda.persentase_denda')
                                    ->label('Denda Keterlambatan')
                                    ->suffix('%')
                                    ->placeholder('-'),

                                TextEntry::make('total_sudah_dibayar')
                                    ->label('Total Sudah Dibayar')
                                    ->state(function ($record) {
                                        $total = $record->transaksiPinjaman->sum('total_pembayaran');
                                        return $total;
                                    })
                                    ->money('IDR', locale: 'id')
                                    ->color('success'),
                            ]),

                        Grid::make(2)
                            ->schema([
                                TextEntry::make('sisa_pinjaman')
                                    ->label('Sisa Pinjaman')
                                    ->state(function ($record) {
                                        $lastTransaksi = $record->transaksiPinjaman->sortByDesc('id')->first();
                                        return $lastTransaksi ? $lastTransaksi->sisa_pinjaman : $record->jumlah_pinjaman;
                                    })
                                    ->money('IDR', locale: 'id')
                                    ->weight('bold')
                                    ->color('danger'),

                                TextEntry::make('angsuran_ke')
                                    ->label('Angsuran Terakhir')
                                    ->state(function ($record) {
                                        $lastTransaksi = $record->transaksiPinjaman->sortByDesc('id')->first();
                                        return $lastTransaksi ? 'Ke-' . $lastTransaksi->angsuran_ke : 'Belum ada pembayaran';
                                    })
                                    ->badge()
                                    ->color('info'),
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Riwayat Pembayaran')
                    ->icon('heroicon-o-clock')
                    ->schema([
                        RepeatableEntry::make('transaksiPinjaman')
                            ->label('')
                            ->schema([
                                Grid::make(6)
                                    ->schema([
                                        TextEntry::make('angsuran_ke')
                                            ->label('Angsuran')
                                            ->formatStateUsing(fn ($state) => 'Ke-' . $state)
                                            ->badge()
                                            ->color('primary'),

                                        TextEntry::make('tanggal_pembayaran')
                                            ->label('Tanggal')
                                            ->date('d/m/Y'),

                                        TextEntry::make('angsuran_pokok')
                                            ->label('Pokok')
                                            ->money('IDR', locale: 'id'),

                                        TextEntry::make('angsuran_bunga')
                                            ->label('Bunga')
                                            ->money('IDR', locale: 'id'),

                                        TextEntry::make('denda')
                                            ->label('Denda')
                                            ->money('IDR', locale: 'id')
                                            ->color(fn ($state) => $state > 0 ? 'danger' : 'gray'),

                                        TextEntry::make('total_pembayaran')
                                            ->label('Total')
                                            ->money('IDR', locale: 'id')
                                            ->weight('bold'),
                                    ]),
                            ])
                            ->contained(false)
                    ])
                    ->collapsible(),
            ]);
    }

    public function closeDetail(): void
    {
        $this->selectedPinjaman = null;
    }

    public function getTotalPinjaman(): string
    {
        $profile = Auth::user()->profile;
        
        if (!$profile) {
            return format_rupiah(0);
        }

        $total = Pinjaman::where('profile_id', $profile->id_user)
            ->where('status_pinjaman', 'approved')
            ->sum('jumlah_pinjaman');

        return format_rupiah($total);
    }

    public function getSisaPinjaman(): string
    {
        $profile = Auth::user()->profile;
        
        if (!$profile) {
            return format_rupiah(0);
        }

        $pinjamans = Pinjaman::where('profile_id', $profile->id_user)
            ->where('status_pinjaman', 'approved')
            ->with('transaksiPinjaman')
            ->get();

        $totalSisa = 0;
        foreach ($pinjamans as $pinjaman) {
            $lastTransaksi = $pinjaman->transaksiPinjaman->sortByDesc('id')->first();
            $totalSisa += $lastTransaksi ? $lastTransaksi->sisa_pinjaman : $pinjaman->jumlah_pinjaman;
        }

        return format_rupiah($totalSisa);
    }

    public function getJumlahPinjaman(): int
    {
        $profile = Auth::user()->profile;
        
        if (!$profile) {
            return 0;
        }

        return Pinjaman::where('profile_id', $profile->id_user)->count();
    }

    public function getPinjamanAktif(): int
    {
        $profile = Auth::user()->profile;
        
        if (!$profile) {
            return 0;
        }

        return Pinjaman::where('profile_id', $profile->id_user)
            ->where('status_pinjaman', 'approved')
            ->count();
    }
}
