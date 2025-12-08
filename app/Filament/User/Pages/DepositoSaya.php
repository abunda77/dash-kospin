<?php

namespace App\Filament\User\Pages;

use Filament\Pages\Page;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Infolists\Concerns\InteractsWithInfolists;
use Filament\Infolists\Contracts\HasInfolists;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\IconEntry;
use App\Models\Deposito;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class DepositoSaya extends Page implements HasTable, HasInfolists
{
    use InteractsWithTable, InteractsWithInfolists;

    protected static ?string $navigationIcon = 'heroicon-o-building-library';
    
    protected static ?string $navigationLabel = 'Deposito Saya';
    
    protected static ?string $title = 'Deposito Saya';

    protected static string $view = 'filament.user.pages.deposito-saya';
    
    protected static ?int $navigationSort = 30;

    public ?Deposito $selectedDeposito = null;

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())
            ->columns([
                TextColumn::make('nomor_rekening')
                    ->label('No. Rekening')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('Nomor rekening disalin!')
                    ->weight('bold')
                    ->color('primary'),

                TextColumn::make('nominal_penempatan')
                    ->label('Nominal')
                    ->money('IDR', locale: 'id')
                    ->weight('bold')
                    ->alignEnd(),

                TextColumn::make('jangka_waktu')
                    ->label('Tenor')
                    ->formatStateUsing(fn ($state) => $state . ' Bulan')
                    ->badge()
                    ->color('info'),

                TextColumn::make('rate_bunga')
                    ->label('Bunga')
                    ->formatStateUsing(fn ($state) => $state . '%')
                    ->badge()
                    ->color('warning'),

                TextColumn::make('tanggal_jatuh_tempo')
                    ->label('Jatuh Tempo')
                    ->date('d M Y')
                    ->sortable()
                    ->color(fn ($state) => Carbon::parse($state)->isPast() ? 'danger' : 'success'),

                IconColumn::make('perpanjangan_otomatis')
                    ->label('ARO')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match (strtolower($state)) {
                        'aktif', 'active' => 'success',
                        'jatuh tempo', 'matured' => 'warning',
                        'dicairkan', 'closed' => 'gray',
                        default => 'gray',
                    }),
            ])
            ->defaultSort('tanggal_jatuh_tempo', 'asc')
            ->striped()
            ->actions([
                \Filament\Tables\Actions\Action::make('detail')
                    ->label('Lihat Detail')
                    ->icon('heroicon-o-eye')
                    ->color('primary')
                    ->action(fn (Deposito $record) => $this->selectedDeposito = $record),
            ])
            ->emptyStateHeading('Belum Ada Deposito')
            ->emptyStateDescription('Anda belum memiliki deposito.')
            ->emptyStateIcon('heroicon-o-building-library');
    }

    protected function getTableQuery(): Builder
    {
        $profile = Auth::user()->profile;
        
        if (!$profile) {
            return Deposito::query()->whereRaw('1 = 0');
        }

        return Deposito::query()
            ->where('id_user', $profile->id_user)
            ->with(['profile']);
    }

    public function depositoInfolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->record($this->selectedDeposito)
            ->schema([
                Section::make('Informasi Deposito')
                    ->icon('heroicon-o-building-library')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('nomor_rekening')
                                    ->label('Nomor Rekening')
                                    ->weight('bold')
                                    ->size('lg')
                                    ->copyable()
                                    ->copyMessage('Nomor rekening disalin!'),

                                TextEntry::make('status')
                                    ->label('Status')
                                    ->badge()
                                    ->color(fn (string $state): string => match (strtolower($state)) {
                                        'aktif', 'active' => 'success',
                                        'jatuh tempo', 'matured' => 'warning',
                                        'dicairkan', 'closed' => 'gray',
                                        default => 'gray',
                                    }),

                                IconEntry::make('perpanjangan_otomatis')
                                    ->label('Perpanjangan Otomatis (ARO)')
                                    ->boolean()
                                    ->trueIcon('heroicon-o-check-circle')
                                    ->falseIcon('heroicon-o-x-circle')
                                    ->trueColor('success')
                                    ->falseColor('danger'),
                            ]),

                        Grid::make(2)
                            ->schema([
                                TextEntry::make('nominal_penempatan')
                                    ->label('Nominal Penempatan')
                                    ->money('IDR', locale: 'id')
                                    ->size('lg')
                                    ->weight('bold')
                                    ->color('primary'),

                                TextEntry::make('jangka_waktu')
                                    ->label('Jangka Waktu')
                                    ->formatStateUsing(fn ($state) => $state . ' Bulan')
                                    ->badge()
                                    ->color('info'),
                            ]),
                    ]),

                Section::make('Informasi Bunga')
                    ->icon('heroicon-o-chart-bar')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('rate_bunga')
                                    ->label('Suku Bunga')
                                    ->formatStateUsing(fn ($state) => $state . '% per tahun')
                                    ->badge()
                                    ->color('warning'),

                                TextEntry::make('nominal_bunga')
                                    ->label('Nominal Bunga')
                                    ->money('IDR', locale: 'id')
                                    ->weight('bold')
                                    ->color('success'),

                                TextEntry::make('total_saat_jatuh_tempo')
                                    ->label('Total Saat Jatuh Tempo')
                                    ->state(fn ($record) => $record->nominal_penempatan + $record->nominal_bunga)
                                    ->money('IDR', locale: 'id')
                                    ->weight('bold')
                                    ->size('lg')
                                    ->color('success'),
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Jadwal')
                    ->icon('heroicon-o-calendar')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('tanggal_pembukaan')
                                    ->label('Tanggal Pembukaan')
                                    ->date('d F Y'),

                                TextEntry::make('tanggal_jatuh_tempo')
                                    ->label('Tanggal Jatuh Tempo')
                                    ->date('d F Y')
                                    ->color(fn ($state) => Carbon::parse($state)->isPast() ? 'danger' : 'success'),

                                TextEntry::make('sisa_waktu')
                                    ->label('Sisa Waktu')
                                    ->state(function ($record) {
                                        $jatuhTempo = Carbon::parse($record->tanggal_jatuh_tempo);
                                        $now = Carbon::now();
                                        
                                        if ($jatuhTempo->isPast()) {
                                            return 'Sudah jatuh tempo ' . $now->diffInDays($jatuhTempo) . ' hari lalu';
                                        }
                                        
                                        $days = $now->diffInDays($jatuhTempo);
                                        if ($days > 30) {
                                            return floor($days / 30) . ' bulan ' . ($days % 30) . ' hari lagi';
                                        }
                                        return $days . ' hari lagi';
                                    })
                                    ->badge()
                                    ->color(fn ($state) => str_contains($state, 'Sudah') ? 'danger' : 'success'),
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Informasi Pencairan')
                    ->icon('heroicon-o-banknotes')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('nama_bank')
                                    ->label('Nama Bank')
                                    ->placeholder('Belum diisi'),

                                TextEntry::make('nomor_rekening_bank')
                                    ->label('Nomor Rekening Bank')
                                    ->placeholder('Belum diisi')
                                    ->copyable()
                                    ->copyMessage('Nomor rekening bank disalin!'),

                                TextEntry::make('nama_pemilik_rekening_bank')
                                    ->label('Nama Pemilik Rekening')
                                    ->placeholder('Belum diisi'),
                            ]),
                    ])
                    ->collapsible()
                    ->collapsed(),

                Section::make('Catatan')
                    ->icon('heroicon-o-document-text')
                    ->schema([
                        TextEntry::make('notes')
                            ->label('')
                            ->placeholder('Tidak ada catatan')
                            ->markdown(),
                    ])
                    ->collapsible()
                    ->collapsed(),
            ]);
    }

    public function closeDetail(): void
    {
        $this->selectedDeposito = null;
    }

    public function getTotalDeposito(): string
    {
        $profile = Auth::user()->profile;
        
        if (!$profile) {
            return format_rupiah(0);
        }

        $total = Deposito::where('id_user', $profile->id_user)
            ->whereIn('status', ['aktif', 'Aktif', 'active', 'Active'])
            ->sum('nominal_penempatan');

        return format_rupiah($total);
    }

    public function getTotalBunga(): string
    {
        $profile = Auth::user()->profile;
        
        if (!$profile) {
            return format_rupiah(0);
        }

        $total = Deposito::where('id_user', $profile->id_user)
            ->whereIn('status', ['aktif', 'Aktif', 'active', 'Active'])
            ->sum('nominal_bunga');

        return format_rupiah($total);
    }

    public function getJumlahDeposito(): int
    {
        $profile = Auth::user()->profile;
        
        if (!$profile) {
            return 0;
        }

        return Deposito::where('id_user', $profile->id_user)->count();
    }

    public function getDepositoAktif(): int
    {
        $profile = Auth::user()->profile;
        
        if (!$profile) {
            return 0;
        }

        return Deposito::where('id_user', $profile->id_user)
            ->whereIn('status', ['aktif', 'Aktif', 'active', 'Active'])
            ->count();
    }

    public function getDepositoJatuhTempoBulanIni(): int
    {
        $profile = Auth::user()->profile;
        
        if (!$profile) {
            return 0;
        }

        return Deposito::where('id_user', $profile->id_user)
            ->whereMonth('tanggal_jatuh_tempo', Carbon::now()->month)
            ->whereYear('tanggal_jatuh_tempo', Carbon::now()->year)
            ->count();
    }
}
