<?php

namespace App\Filament\Pages;

use App\Services\LoanReportService;
use App\Services\LoanReportExportService;
use App\Helpers\SafeNotificationHelper;
use App\Models\Pinjaman;
use App\Models\TransaksiPinjaman;
use App\Models\ProdukPinjaman;
use Carbon\Carbon;
use Filament\Pages\Page;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Actions\Action;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Support\Colors\Color;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\App;

class LaporanPinjaman extends Page implements HasTable, HasForms
{
    use InteractsWithTable;
    use InteractsWithForms;
    use HasPageShield;

    protected static ?string $navigationIcon = 'heroicon-o-presentation-chart-line';
    protected static string $view = 'filament.pages.laporan-pinjaman';
    protected static ?string $navigationGroup = 'Laporan';
    protected static ?string $navigationLabel = 'Laporan Pinjaman';
    protected static ?string $title = 'Dashboard Pinjaman';
      public $period = 'year';
    public $productFilter = null;
    public $dateRange = [];
    
    public $data = [];

    private LoanReportService $loanReportService;
    private LoanReportExportService $loanReportExportService;

    public function boot(): void
    {
        $this->loanReportService = App::make(LoanReportService::class);
        $this->loanReportExportService = App::make(LoanReportExportService::class);
    }

    public function table(Table $table): Table
    {
        return $this->getTable();
    }    public function getTableQuery()
    {
        // Ensure we have the latest data from the form
        $productFilter = $this->data['productFilter'] ?? $this->productFilter;
        
        // Create a new service instance with current filters
        $service = new LoanReportService($productFilter, $this->getDateRange());
        
        return $service->getApprovedLoansQuery();
    }

    public function getTable(): Table
    {
        return Table::make($this)
            ->query($this->getTableQuery())
            ->columns($this->getTableColumns())
            ->filters($this->getTableFilters())
            ->actions($this->getTableActions())
            ->bulkActions($this->getTableBulkActions())
            ->defaultSort('tanggal_pinjaman', 'desc')
            ->persistSortInSession()
            ->persistSearchInSession()
            ->persistFiltersInSession()
            ->striped()
            ->paginated([10, 25, 50, 100])
            ->deferLoading()            ->poll('30s');
    }

    public function mount(): void
    {
        $this->data = [
            'period' => $this->period,
            'productFilter' => $this->productFilter,
            'dateRange' => $this->dateRange,
        ];
        $this->form->fill($this->data);
    }
    
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('period')
                    ->label('Periode')
                    ->options([
                        'today' => 'Hari Ini',
                        'week' => 'Minggu Ini',
                        'month' => 'Bulan Ini',
                        'year' => 'Tahun Ini',
                        'custom' => 'Kustom',
                    ])                    ->default('year')
                    ->live()
                    ->afterStateUpdated(function ($state) {
                        $this->period = $state;
                        $this->data['period'] = $state;
                        $this->resetTable();
                    }),
                
                Select::make('productFilter')
                    ->label('Filter Produk')
                    ->options(fn () => ProdukPinjaman::pluck('nama_produk', 'id')->toArray())
                    ->placeholder('Semua Produk')
                    ->live()
                    ->afterStateUpdated(function ($state) {
                        $this->productFilter = $state;
                        $this->data['productFilter'] = $state;
                        $this->resetTable();
                    }),
                
                DatePicker::make('dateRange.start_date')
                    ->label('Dari Tanggal')
                    ->default(now()->startOfMonth())
                    ->live()
                    ->visible(fn($get) => $get('period') === 'custom')
                    ->afterStateUpdated(function ($state) {
                        $this->dateRange['start_date'] = $state;
                        $this->data['dateRange']['start_date'] = $state;
                        $this->resetTable();
                    }),

                DatePicker::make('dateRange.end_date')
                    ->label('Sampai Tanggal')
                    ->default(now())
                    ->live()
                    ->visible(fn($get) => $get('period') === 'custom')
                    ->afterStateUpdated(function ($state) {
                        $this->dateRange['end_date'] = $state;
                        $this->data['dateRange']['end_date'] = $state;
                        $this->resetTable();
                    }),
            ])
            ->columns(4)
            ->statePath('data');    }    protected function getDateRange(): array
    {
        // Ensure we have the latest data from the form
        $period = $this->data['period'] ?? $this->period;
        $dateRange = $this->data['dateRange'] ?? $this->dateRange;
        
        return $this->loanReportService->getDateRange($period, $dateRange);
    }public function getStatsData(): array
    {
        // Ensure we have the latest data from the form
        $productFilter = $this->data['productFilter'] ?? $this->productFilter;
        
        // Create a new service instance with current filters
        $service = new LoanReportService($productFilter, $this->getDateRange());
        
        return $service->getLoanStats();
    }    public function getStatsWidgets(): array
    {
        $stats = $this->getStatsData();
        $critical90DaysStats = $this->getCritical90DaysStats();
        
        return [
            Stat::make('Pinjaman Aktif', number_format($stats['active_loans']))
                ->description('Total pinjaman yang disetujui')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),
                
            Stat::make('Total Pinjaman', 'Rp ' . number_format($stats['total_loan_amount'], 0, ',', '.'))
                ->description('Nilai total pinjaman')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('primary'),
                
            Stat::make('Rata-rata Pinjaman', 'Rp ' . number_format($stats['avg_loan_amount'], 0, ',', '.'))
                ->description('Nilai rata-rata per pinjaman')
                ->descriptionIcon('heroicon-m-calculator')
                ->color('info'),
                
            Stat::make('Pinjaman Jatuh Tempo', number_format($stats['overdue_loans']))
                ->description('Pinjaman yang sudah jatuh tempo')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color(Color::Red),
                  Stat::make('Total Pembayaran', 'Rp ' . number_format($stats['total_payments'], 0, ',', '.'))
                ->description('Total pembayaran diterima')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('success'),
                
            Stat::make('Jumlah Transaksi', number_format($stats['payment_count']))
                ->description('Total transaksi pembayaran')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('warning'),
                  // Widget untuk keterlambatan kritis 90+ hari - sesuai referensi ListKeterlambatan90Hari
                  Stat::make('Total Akun Bermasalah', number_format($critical90DaysStats['total_accounts']))
                      ->description('Pinjaman dengan keterlambatan > 90 hari')
                      ->descriptionIcon('heroicon-m-exclamation-triangle')
                      ->color(Color::Red),
                      //->url('/admin/list-keterlambatan90-hari'),
                       
                  Stat::make('Total Tunggakan', 'Rp ' . number_format($critical90DaysStats['total_overdue'], 0, ',', '.'))
                      ->description('Total pokok + bunga + denda > 90 hari')
                      ->descriptionIcon('heroicon-m-minus-circle')
                      ->color(Color::Rose)
                      //->url('/admin/list-keterlambatan90-hari'),
        ];
    }

    public function getCritical90DaysStats(): array
    {
        $today = \Carbon\Carbon::today();
        
        $critical90DaysQuery = Pinjaman::query()
            ->with(['profile', 'biayaBungaPinjaman', 'denda', 'transaksiPinjaman'])
            ->where('status_pinjaman', 'approved')
            ->whereDoesntHave('transaksiPinjaman', function ($q) use ($today) {
                $q->whereMonth('tanggal_pembayaran', $today->month)
                  ->whereYear('tanggal_pembayaran', $today->year);
            })
            ->where(function ($query) use ($today) {
                $query->whereHas('transaksiPinjaman', function ($q) use ($today) {
                    $q->whereRaw('DATEDIFF(?, tanggal_jatuh_tempo) > 90', [$today])
                      ->whereRaw('DATE_FORMAT(tanggal_jatuh_tempo, "%Y-%m") < ?',
                          [$today->format('Y-m')]);
                })
                ->orWhere(function ($q) use ($today) {
                    $q->whereDoesntHave('transaksiPinjaman')
                      ->whereRaw('DATE_FORMAT(tanggal_pinjaman, "%Y-%m") < ?',
                          [$today->format('Y-m')])
                      ->whereRaw('DATEDIFF(?, DATE_ADD(tanggal_pinjaman, INTERVAL 1 MONTH)) > 90',
                          [$today]);
                });
            });
        
        $critical90DaysData = $critical90DaysQuery->get();
        
        $totalAccounts = $critical90DaysData->count();
        $totalOverdue = $critical90DaysData->sum(function ($record) use ($today) {
            $hariTerlambat = abs($this->calculateHariTerlambat($record, $today));
            $jumlahBulanTerlambat = ceil($hariTerlambat / 30);
            
            $angsuranPokok = abs($this->calculateAngsuranPokok($record));
            $bungaPerBulan = abs($this->calculateBungaPerBulan($record));
            
            $totalPokok = $angsuranPokok * $jumlahBulanTerlambat;
            $totalBunga = $bungaPerBulan * $jumlahBulanTerlambat;
            
            $angsuranTotal = $angsuranPokok + $bungaPerBulan;
            $dendaPerHari = (0.05 * $angsuranTotal) / 30;
            $totalDenda = $dendaPerHari * $hariTerlambat;
            
            return $totalPokok + $totalBunga + $totalDenda;
        });
        
        return [
            'total_accounts' => $totalAccounts,
            'total_overdue' => $totalOverdue,
        ];
    }

    // HELPER FUNCTIONS (Copied from ListKeterlambatan90Hari)
    private function calculateAngsuranPokok($record)
    {
        return $record->jumlah_pinjaman / $record->jangka_waktu;
    }

    private function calculateBungaPerBulan($record)
    {
        $pokok = $record->jumlah_pinjaman;
        $bungaPerTahun = $record->biayaBungaPinjaman->persentase_bunga;
        $jangkaWaktu = $record->jangka_waktu;

        // Hitung bunga per bulan (total bunga setahun dibagi jangka waktu)
        return ($pokok * ($bungaPerTahun/100)) / $jangkaWaktu;
    }

    private function calculateHariTerlambat($record, $today)
    {
        // Ambil tanggal jatuh tempo dari transaksi terakhir atau tanggal pinjaman
        $lastTransaction = $record->transaksiPinjaman()
            ->orderBy('angsuran_ke', 'desc')
            ->first();

        if ($lastTransaction) {
            // Jika ada transaksi sebelumnya, gunakan tanggal jatuh tempo berikutnya
            $tanggalJatuhTempo = Carbon::parse($lastTransaction->tanggal_pembayaran)
                ->addMonth()
                ->startOfDay();
        } else {
            // Jika belum ada transaksi, gunakan tanggal pinjaman + 1 bulan
            $tanggalJatuhTempo = Carbon::parse($record->tanggal_pinjaman)
                ->addMonth()
                ->startOfDay();
        }

        // Jika masih dalam bulan yang sama dengan tanggal pinjaman, return 0
        if ($today->format('Y-m') === Carbon::parse($record->tanggal_pinjaman)->format('Y-m')) {
            return 0;
        }

        // Hitung keterlambatan hanya jika sudah melewati tanggal jatuh tempo
        // dan berada di bulan yang berbeda
        if ($today->gt($tanggalJatuhTempo) &&
            $today->format('Y-m') !== $tanggalJatuhTempo->format('Y-m')) {
            return $today->diffInDays($tanggalJatuhTempo);
        }

        return 0;
    }

    public function getProductDistribution(): array
    {
        // Ensure we have the latest data from the form
        $productFilter = $this->data['productFilter'] ?? $this->productFilter;
        
        // Create a new service instance with current filters
        $service = new LoanReportService($productFilter, $this->getDateRange());
        
        return $service->getProductDistribution();
    }

    public function getMonthlyLoanTrends(): array
    {
        // Ensure we have the latest data from the form
        $productFilter = $this->data['productFilter'] ?? $this->productFilter;
        
        // Create a new service instance with current filters
        $service = new LoanReportService($productFilter, $this->getDateRange());
        
        return $service->getMonthlyLoanTrends();
    }

    public function getPaymentTrends(): array
    {
        // Ensure we have the latest data from the form
        $productFilter = $this->data['productFilter'] ?? $this->productFilter;
        
        // Create a new service instance with current filters
        $service = new LoanReportService($productFilter, $this->getDateRange());
        
        return $service->getPaymentTrends();
    }    protected function getHeaderActions(): array
    {        return [            Action::make('export_all')
                ->label('Cetak Laporan Pinjaman')
                ->icon('heroicon-o-printer')
                ->action(function () {
                    try {
                        $productFilter = $this->data['productFilter'] ?? $this->productFilter;
                        $dateRange = $this->getDateRange();
                        
                        $filename = 'laporan-pinjaman-' . \Carbon\Carbon::now()->format('Y-m-d-H-i-s') . '.pdf';
                        $filepath = $this->loanReportExportService->generateLoanReportFile($productFilter, $dateRange, $filename);
                        
                        $this->dispatch('download-file', [
                            'url' => route('download-temp-file', ['filename' => basename($filepath)]),
                            'filename' => $filename
                        ]);                        SafeNotificationHelper::success(
                            'PDF Generated Successfully',
                            'The loan report has been generated and will be downloaded shortly.'
                        );
                        
                    } catch (\Exception $e) {
                        \Illuminate\Support\Facades\Log::error('PDF Export Error: ' . $e->getMessage());
                        
                        \Filament\Notifications\Notification::make()
                            ->title('Error generating PDF')
                            ->body('There was an error generating the PDF report. Please try again.')
                            ->danger()
                            ->send();
                    }
                }),            Action::make('export_transactions')
                ->label('Cetak Laporan Transaksi')
                ->icon('heroicon-o-document-chart-bar')
                ->action(function () {
                    try {
                        $productFilter = $this->data['productFilter'] ?? $this->productFilter;
                        $dateRange = $this->getDateRange();
                        
                        $filename = 'laporan-transaksi-' . \Carbon\Carbon::now()->format('Y-m-d-H-i-s') . '.pdf';
                        $filepath = $this->loanReportExportService->generateTransactionReportFile($productFilter, $dateRange, $filename);
                        
                        $this->dispatch('download-file', [
                            'url' => route('download-temp-file', ['filename' => basename($filepath)]),
                            'filename' => $filename
                        ]);                        SafeNotificationHelper::success(
                            'PDF Generated Successfully',
                            'The transaction report has been generated and will be downloaded shortly.'
                        );
                        
                    } catch (\Exception $e) {
                        \Illuminate\Support\Facades\Log::error('PDF Export Error: ' . $e->getMessage());
                        
                        \Filament\Notifications\Notification::make()
                            ->title('Error generating PDF')
                            ->body('There was an error generating the PDF report. Please try again.')
                            ->danger()
                            ->send();
                    }
                }),
        ];
    }

    private function getTableColumns(): array
    {
        return [
            TextColumn::make('no_pinjaman')
                ->label('No. Pinjaman')
                ->searchable()
                ->sortable()
                ->copyable()
                ->copyMessage('Nomor pinjaman telah disalin!')
                ->copyMessageDuration(1500),
                
            TextColumn::make('profile.user.name')
                ->label('Nama Nasabah')
                ->searchable(['users.name'])
                ->sortable()
                ->description(fn ($record) => $record->profile?->user?->email ?? ''),
                
            TextColumn::make('produkPinjaman.nama_produk')
                ->label('Produk Pinjaman')
                ->sortable()
                ->badge()
                ->color('primary'),
                
            TextColumn::make('jumlah_pinjaman')
                ->label('Jumlah Pinjaman')
                ->money('IDR')
                ->sortable()
                ->summarize([
                    \Filament\Tables\Columns\Summarizers\Sum::make()
                        ->money('IDR')
                        ->label('Total'),
                    \Filament\Tables\Columns\Summarizers\Average::make()
                        ->money('IDR')
                        ->label('Rata-rata'),
                ]),
                
            TextColumn::make('tanggal_pinjaman')
                ->label('Tanggal Pinjaman')
                ->date('d M Y')
                ->sortable()
                ->toggleable(),
                
            TextColumn::make('tanggal_jatuh_tempo')
                ->label('Jatuh Tempo')
                ->date('d M Y')
                ->sortable()
                ->color(fn ($record) => 
                    $record->tanggal_jatuh_tempo->isPast() ? 'red' : 'gray'
                ),
                
            TextColumn::make('status_pinjaman')
                ->label('Status')
                ->badge()
                ->color(fn (string $state): string => match ($state) {
                    'pending' => 'orange',
                    'approved' => 'green',
                    'rejected' => 'red',
                    'completed' => 'blue',
                    default => 'gray',
                })
                ->sortable(),
        ];
    }

    private function getTableFilters(): array
    {
        return [
            SelectFilter::make('produk_pinjaman_id')
                ->relationship('produkPinjaman', 'nama_produk')
                ->label('Produk Pinjaman')
                ->preload(),
                
            SelectFilter::make('status_pinjaman')
                ->label('Status Pinjaman')
                ->options([
                    'pending' => 'Menunggu',
                    'approved' => 'Disetujui',
                    'rejected' => 'Ditolak',
                    'completed' => 'Selesai',
                ])
                ->default('approved'),
                
            Filter::make('created_at')
                ->form([
                    DatePicker::make('tanggal_mulai')
                        ->label('Dari Tanggal')
                        ->default(now()->startOfMonth()),
                    DatePicker::make('tanggal_akhir')
                        ->label('Sampai Tanggal')
                        ->default(now()),
                ])
                ->query(function ($query, array $data) {
                    return $query
                        ->when(
                            $data['tanggal_mulai'],
                            fn ($query, $date) => $query->whereDate('tanggal_pinjaman', '>=', $date),
                        )
                        ->when(
                            $data['tanggal_akhir'],
                            fn ($query, $date) => $query->whereDate('tanggal_pinjaman', '<=', $date),
                        );
                })
                ->indicateUsing(function (array $data): array {
                    $indicators = [];
                    if ($data['tanggal_mulai'] ?? null) {
                        $indicators[] = 'Dari: ' . \Carbon\Carbon::parse($data['tanggal_mulai'])->toFormattedDateString();
                    }
                    if ($data['tanggal_akhir'] ?? null) {
                        $indicators[] = 'Sampai: ' . \Carbon\Carbon::parse($data['tanggal_akhir'])->toFormattedDateString();
                    }
                    return $indicators;
                }),
        ];
    }

    private function getTableActions(): array
    {
        return [
            \Filament\Tables\Actions\ViewAction::make()
                ->color('primary'),
        ];
    }

    private function getTableBulkActions(): array
    {
        return [
            \Filament\Tables\Actions\BulkAction::make('export')
                ->label('Cetak Laporan')
                ->icon('heroicon-o-printer')
                ->color('success')
                ->action(function ($records) {
                    return $this->loanReportExportService->exportBulkLoanReport($records);
                })
        ];
    }
}
