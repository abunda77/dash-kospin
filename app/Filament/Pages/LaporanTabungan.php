<?php

namespace App\Filament\Pages;

use App\Models\Tabungan;
use App\Models\TransaksiTabungan;
use App\Models\ProdukTabungan;
use App\Models\SaldoTabungan;
use App\Services\SavingsReportService;
use App\Services\SavingsReportExportService;
use App\Helpers\SafeNotificationHelper;
use Filament\Pages\Page;
use Filament\Tables\Table;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Form;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Actions\Action;
use Filament\Support\Colors\Color;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\App;
use Carbon\Carbon;

class LaporanTabungan extends Page implements HasTable, HasForms
{
    use InteractsWithTable;
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static string $view = 'filament.pages.laporan-tabungan';
    protected static ?string $navigationGroup = 'Laporan';
    protected static ?string $navigationLabel = 'Laporan Tabungan';
    protected static ?string $title = 'Dashboard Tabungan';

    public $period = 'year';
    public $productFilter = null;
    public $dateRange = [];

    public $data = [];

    private SavingsReportService $savingsReportService;
    private SavingsReportExportService $savingsReportExportService;

    public function boot(): void
    {
        $this->savingsReportService = App::make(SavingsReportService::class);
        $this->savingsReportExportService = App::make(SavingsReportExportService::class);
    }

    public function table(Table $table): Table
    {
        return $this->getTable();
    }

    public function getTableQuery()
    {
        $productFilter = $this->data['productFilter'] ?? $this->productFilter;
        $dateRange = $this->getDateRange();

        $query = Tabungan::query()
            ->with(['profile.user', 'produkTabungan', 'transaksi'])
            ->where('status_rekening', 'aktif');

        if ($productFilter) {
            $query->where('produk_tabungan', $productFilter);
        }

        if (!empty($dateRange['start_date']) && !empty($dateRange['end_date'])) {
            $query->whereBetween('tanggal_buka_rekening', [
                $dateRange['start_date'],
                $dateRange['end_date']
            ]);
        }

        return $query;
    }

    public function getTable(): Table
    {
        return Table::make($this)
            ->query($this->getTableQuery())
            ->columns($this->getTableColumns())
            ->filters($this->getTableFilters())
            ->actions($this->getTableActions())
            ->bulkActions($this->getTableBulkActions())
            ->defaultSort('tanggal_buka_rekening', 'desc')
            ->persistSortInSession()
            ->persistSearchInSession()
            ->persistFiltersInSession()
            ->striped()
            ->paginated([10, 25, 50, 100])
            ->deferLoading()
            ->poll('60s');
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
                    ])
                    ->default('year')
                    ->live()
                    ->afterStateUpdated(function ($state) {
                        $this->period = $state;
                        $this->data['period'] = $state;
                        $this->resetTable();
                    }),

                Select::make('productFilter')
                    ->label('Filter Produk')
                    ->options(fn() => ProdukTabungan::pluck('nama_produk', 'id')->toArray())
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
            ->statePath('data');
    }

    protected function getDateRange(): array
    {
        $period = $this->data['period'] ?? $this->period;
        $dateRange = $this->data['dateRange'] ?? $this->dateRange;

        return match ($period) {
            'today' => [
                'start_date' => now()->startOfDay(),
                'end_date' => now()->endOfDay(),
            ],
            'week' => [
                'start_date' => now()->startOfWeek(),
                'end_date' => now()->endOfWeek(),
            ],
            'month' => [
                'start_date' => now()->startOfMonth(),
                'end_date' => now()->endOfMonth(),
            ],
            'year' => [
                'start_date' => now()->startOfYear(),
                'end_date' => now()->endOfYear(),
            ],
            'custom' => [
                'start_date' => $dateRange['start_date'] ?? now()->startOfMonth(),
                'end_date' => $dateRange['end_date'] ?? now(),
            ],
            default => [
                'start_date' => now()->startOfYear(),
                'end_date' => now()->endOfYear(),
            ],
        };
    }

    public function getStatsData(): array
    {
        $productFilter = $this->data['productFilter'] ?? $this->productFilter;
        $dateRange = $this->getDateRange();
        
        $cacheKey = 'savings_stats_' . md5(serialize([$productFilter, $dateRange]));
        
        return \Illuminate\Support\Facades\Cache::remember(
            $cacheKey,
            now()->addMinutes(5),
            function () use ($productFilter, $dateRange) {
                $service = new SavingsReportService($productFilter, $dateRange);
                return $service->getSavingsStats();
            }
        );
    }

    public function getStatsWidgets(): array
    {
        $stats = $this->getStatsData();

        return [
            Stat::make('Total Rekening', number_format($stats['total_accounts']))
                ->description('Rekening tabungan aktif')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('success'),

            Stat::make('Total Saldo', 'Rp ' . number_format($stats['total_balance'], 0, ',', '.'))
                ->description('Total saldo semua rekening')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('primary'),

            Stat::make('Rata-rata Saldo', 'Rp ' . number_format($stats['avg_balance'], 0, ',', '.'))
                ->description('Saldo rata-rata per rekening')
                ->descriptionIcon('heroicon-m-calculator')
                ->color('info'),

            Stat::make('Total Setoran', 'Rp ' . number_format($stats['total_deposits'], 0, ',', '.'))
                ->description('Total setoran dalam periode')
                ->descriptionIcon('heroicon-m-arrow-down-circle')
                ->color('success'),

            Stat::make('Total Penarikan', 'Rp ' . number_format($stats['total_withdrawals'], 0, ',', '.'))
                ->description('Total penarikan dalam periode')
                ->descriptionIcon('heroicon-m-arrow-up-circle')
                ->color('warning'),

            Stat::make('Jumlah Transaksi', number_format($stats['transaction_count']))
                ->description('Total transaksi dalam periode')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('gray'),

            Stat::make('Transaksi Setoran', number_format($stats['deposit_count']))
                ->description('Jumlah transaksi setoran')
                ->descriptionIcon('heroicon-m-plus-circle')
                ->color('green'),

            Stat::make('Transaksi Penarikan', number_format($stats['withdrawal_count']))
                ->description('Jumlah transaksi penarikan')
                ->descriptionIcon('heroicon-m-minus-circle')
                ->color('orange'),
        ];
    }

    public function getProductDistribution(): array
    {
        $productFilter = $this->data['productFilter'] ?? $this->productFilter;

        // Create a new service instance with current filters
        $service = new SavingsReportService($productFilter, $this->getDateRange());

        return $service->getProductDistribution();
    }    
    
    public function getMonthlyTrends(): array
    {
        $productFilter = $this->data['productFilter'] ?? $this->productFilter;

        // Create a new service instance with current filters
        $service = new SavingsReportService($productFilter, $this->getDateRange());

        return $service->getMonthlySavingsTrends();
    }

    public function getMonthlySavingsTrends(): array
    {
        $productFilter = $this->data['productFilter'] ?? $this->productFilter;

        // Create a new service instance with current filters
        $service = new SavingsReportService($productFilter, $this->getDateRange());

        return $service->getMonthlySavingsTrends();
    }

    public function getTopSavers(int $limit = 10): array
    {
        $productFilter = $this->data['productFilter'] ?? $this->productFilter;

        // Create a new service instance with current filters
        $service = new SavingsReportService($productFilter, $this->getDateRange());

        return $service->getTopSavers($limit);
    }

    public function getTransactionTrends(): array
    {
        $productFilter = $this->data['productFilter'] ?? $this->productFilter;

        // Create a new service instance with current filters
        $service = new SavingsReportService($productFilter, $this->getDateRange());

        return $service->getTransactionTrends();
    }

    public function getAccountGrowthTrend(): array
    {
        $productFilter = $this->data['productFilter'] ?? $this->productFilter;

        // Create a new service instance with current filters
        $service = new SavingsReportService($productFilter, $this->getDateRange());

        return $service->getAccountGrowthTrend();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('export_savings')
                ->label('Cetak Laporan Tabungan')
                ->icon('heroicon-o-printer')
                ->action(function () {
                    try {
                        $productFilter = $this->data['productFilter'] ?? $this->productFilter;
                        $dateRange = $this->getDateRange();

                        $filename = 'laporan-tabungan-' . \Carbon\Carbon::now()->format('Y-m-d-H-i-s') . '.pdf';
                        
                        $filepath = $this->savingsReportExportService->generateSavingsReportFile($productFilter, $dateRange, $filename);

                        // Gunakan response download langsung tanpa redirect
                        return response()->download($filepath, $filename, [
                            'Content-Type' => 'application/pdf',
                            'Cache-Control' => 'no-cache, no-store, must-revalidate',
                            'Pragma' => 'no-cache',
                            'Expires' => '0'
                        ])->deleteFileAfterSend(true);

                    } catch (\Exception $e) {
                        \Illuminate\Support\Facades\Log::error('PDF Export Error: ' . $e->getMessage());

                        \Filament\Notifications\Notification::make()
                            ->title('Error generating PDF')
                            ->body('There was an error generating the PDF report. Please try again.')
                            ->danger()
                            ->send();
                    }
                }),

            Action::make('export_transactions')
                ->label('Cetak Laporan Transaksi')
                ->icon('heroicon-o-document-chart-bar')
                ->action(function () {
                    try {
                        $productFilter = $this->data['productFilter'] ?? $this->productFilter;
                        $dateRange = $this->getDateRange();

                        $filename = 'laporan-transaksi-tabungan-' . \Carbon\Carbon::now()->format('Y-m-d-H-i-s') . '.pdf';
                        
                        $filepath = $this->savingsReportExportService->generateTransactionReportFile($productFilter, $dateRange, $filename);

                        // Gunakan response download langsung tanpa redirect
                        return response()->download($filepath, $filename, [
                            'Content-Type' => 'application/pdf',
                            'Cache-Control' => 'no-cache, no-store, must-revalidate',
                            'Pragma' => 'no-cache',
                            'Expires' => '0'
                        ])->deleteFileAfterSend(true);

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

    public function getBalanceDistribution(): array
    {
        $productFilter = $this->data['productFilter'] ?? $this->productFilter;
        $dateRange = $this->getDateRange();
        
        $cacheKey = 'balance_distribution_' . md5(serialize([$productFilter, $dateRange]));
        
        return \Illuminate\Support\Facades\Cache::remember(
            $cacheKey,
            now()->addMinutes(5),
            function () use ($productFilter, $dateRange) {
                $query = Tabungan::query()
                    ->where('status_rekening', 'aktif');

                if ($productFilter) {
                    $query->where('produk_tabungan', $productFilter);
                }

                if (!empty($dateRange['start_date']) && !empty($dateRange['end_date'])) {
                    $query->whereBetween('tanggal_buka_rekening', [
                        $dateRange['start_date'],
                        $dateRange['end_date']
                    ]);
                }

                return [
                    [
                        'range' => '< Rp 1 Juta',
                        'count' => $query->clone()->where('saldo', '<', 1000000)->count(),
                        'min' => 0,
                        'max' => 1000000
                    ],
                    [
                        'range' => 'Rp 1-5 Juta',
                        'count' => $query->clone()->whereBetween('saldo', [1000000, 5000000])->count(),
                        'min' => 1000000,
                        'max' => 5000000
                    ],
                    [
                        'range' => 'Rp 5-10 Juta',
                        'count' => $query->clone()->whereBetween('saldo', [5000000, 10000000])->count(),
                        'min' => 5000000,
                        'max' => 10000000
                    ],
                    [
                        'range' => 'Rp 10-50 Juta',
                        'count' => $query->clone()->whereBetween('saldo', [10000000, 50000000])->count(),
                        'min' => 10000000,
                        'max' => 50000000
                    ],
                    [
                        'range' => '> Rp 50 Juta',
                        'count' => $query->clone()->where('saldo', '>', 50000000)->count(),
                        'min' => 50000000,
                        'max' => null
                    ]
                ];
            }
        );
    }

    public function getAccountGrowth(): array
    {
        $productFilter = $this->data['productFilter'] ?? $this->productFilter;
        $dateRange = $this->getDateRange();
        
        $cacheKey = 'account_growth_' . md5(serialize([$productFilter, $dateRange]));
        
        return \Illuminate\Support\Facades\Cache::remember(
            $cacheKey,
            now()->addMinutes(5),
            function () use ($productFilter, $dateRange) {
                $growthData = [];
                $startDate = \Carbon\Carbon::parse($dateRange['start_date']);
                $endDate = \Carbon\Carbon::parse($dateRange['end_date']);
                
                $current = $startDate->copy()->startOfMonth();
                
                while ($current <= $endDate) {
                    $monthStart = $current->copy()->startOfMonth();
                    $monthEnd = $current->copy()->endOfMonth();
                    
                    $query = Tabungan::query();
                    
                    if ($productFilter) {
                        $query->where('produk_tabungan', $productFilter);
                    }
                    
                    $newAccounts = $query->clone()
                        ->whereBetween('tanggal_buka_rekening', [$monthStart, $monthEnd])
                        ->count();
                    
                    $closedAccounts = $query->clone()
                        ->where('status_rekening', 'tutup')
                        ->whereBetween('updated_at', [$monthStart, $monthEnd])
                        ->count();
                    
                    $growthData[] = [
                        'month' => $current->format('Y-m'),
                        'new_accounts' => $newAccounts,
                        'closed_accounts' => $closedAccounts,
                        'net_growth' => $newAccounts - $closedAccounts
                    ];
                    
                    $current->addMonth();
                }
                
                return $growthData;
            }
        );
    }

    public function getTransactionTypeDistribution(): array
    {
        $productFilter = $this->data['productFilter'] ?? $this->productFilter;
        $dateRange = $this->getDateRange();
        
        $cacheKey = 'transaction_type_distribution_' . md5(serialize([$productFilter, $dateRange]));
        
        return \Illuminate\Support\Facades\Cache::remember(
            $cacheKey,
            now()->addMinutes(5),
            function () use ($productFilter, $dateRange) {
                $service = new SavingsReportService($productFilter, $dateRange);
                $stats = $service->getSavingsStats();
                
                return [
                    [
                        'type' => 'Setoran',
                        'count' => $stats['deposit_count'],
                        'amount' => $stats['total_deposits']
                    ],
                    [
                        'type' => 'Penarikan',
                        'count' => $stats['withdrawal_count'],
                        'amount' => $stats['total_withdrawals']
                    ]
                ];
            }
        );
    }

    private function getTableColumns(): array
    {
        return [
            TextColumn::make('no_tabungan')
                ->label('No. Tabungan')
                ->searchable()
                ->sortable()
                ->copyable()
                ->copyMessage('Nomor tabungan telah disalin!')
                ->copyMessageDuration(1500),

            TextColumn::make('profile.user.name')
                ->label('Nama Nasabah')
                ->searchable(['users.name'])
                ->sortable()
                ->description(fn($record) => $record->profile?->user?->email ?? ''),

            TextColumn::make('produkTabungan.nama_produk')
                ->label('Produk Tabungan')
                ->sortable()
                ->badge()
                ->color('primary'),

            TextColumn::make('saldo')
                ->label('Saldo')
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

            TextColumn::make('tanggal_buka_rekening')
                ->label('Tanggal Buka')
                ->date('d M Y')
                ->sortable()
                ->toggleable(),

            TextColumn::make('status_rekening')
                ->label('Status')
                ->badge()
                ->color(fn(string $state): string => match ($state) {
                    'aktif' => 'green',
                    'nonaktif' => 'red',
                    'tutup' => 'gray',
                    default => 'gray',
                })
                ->sortable(),

            TextColumn::make('transaksi_count')
                ->label('Jumlah Transaksi')
                ->counts('transaksi')
                ->sortable()
                ->toggleable(),
        ];
    }

    private function getTableFilters(): array
    {
        return [
            SelectFilter::make('produk_tabungan')
                ->relationship('produkTabungan', 'nama_produk')
                ->label('Produk Tabungan')
                ->preload(),

            SelectFilter::make('status_rekening')
                ->label('Status Rekening')
                ->options([
                    'aktif' => 'Aktif',
                    'nonaktif' => 'Non-aktif',
                    'tutup' => 'Tutup',
                ])
                ->default('aktif'),

            Filter::make('saldo_range')
                ->form([
                    \Filament\Forms\Components\TextInput::make('saldo_min')
                        ->label('Saldo Minimum')
                        ->numeric()
                        ->prefix('Rp'),
                    \Filament\Forms\Components\TextInput::make('saldo_max')
                        ->label('Saldo Maksimum')
                        ->numeric()
                        ->prefix('Rp'),
                ])
                ->query(function ($query, array $data) {
                    return $query
                        ->when(
                            $data['saldo_min'],
                            fn($query, $amount) => $query->where('saldo', '>=', $amount),
                        )
                        ->when(
                            $data['saldo_max'],
                            fn($query, $amount) => $query->where('saldo', '<=', $amount),
                        );
                })
                ->indicateUsing(function (array $data): array {
                    $indicators = [];
                    if ($data['saldo_min'] ?? null) {
                        $indicators[] = 'Saldo Min: Rp ' . number_format($data['saldo_min'], 0, ',', '.');
                    }
                    if ($data['saldo_max'] ?? null) {
                        $indicators[] = 'Saldo Max: Rp ' . number_format($data['saldo_max'], 0, ',', '.');
                    }
                    return $indicators;
                }),

            Filter::make('tanggal_buka')
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
                            fn($query, $date) => $query->whereDate('tanggal_buka_rekening', '>=', $date),
                        )
                        ->when(
                            $data['tanggal_akhir'],
                            fn($query, $date) => $query->whereDate('tanggal_buka_rekening', '<=', $date),
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
                    try {
                        $filename = 'bulk-savings-report-' . \Carbon\Carbon::now()->format('Y-m-d-H-i-s') . '.pdf';
                        $filepath = $this->savingsReportExportService->exportBulkSavingsReport($records);

                        $this->dispatch('download-file', [
                            'url' => route('download-temp-file', ['filename' => basename($filepath)]),
                            'filename' => $filename
                        ]);

                        SafeNotificationHelper::success(
                            'PDF Generated Successfully',
                            'The bulk savings report has been generated and will be downloaded shortly.'
                        );
                    } catch (\Exception $e) {
                        \Illuminate\Support\Facades\Log::error('Bulk PDF Export Error: ' . $e->getMessage());

                        \Filament\Notifications\Notification::make()
                            ->title('Error generating PDF')
                            ->body('There was an error generating the bulk PDF report. Please try again.')
                            ->danger()
                            ->send();
                    }
                })
        ];
    }
}
