<?php

namespace App\Filament\Pages;

use App\Models\CicilanEmas;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\ChartWidget;

class LaporanCicilanEmas extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static string $view = 'filament.pages.laporan-cicilan-emas';
    protected static ?string $navigationGroup = 'Laporan';
    protected static ?string $title = 'Laporan Cicilan Emas';
    protected static ?string $navigationLabel = 'Laporan Cicilan Emas';

    public ?array $data = [];
    public $tanggal_mulai;
    public $tanggal_akhir;
    public $status = 'all';
    public $user_id = 'all';

    public function mount(): void
    {
        $this->tanggal_mulai = now()->startOfMonth()->format('Y-m-d');
        $this->tanggal_akhir = now()->endOfMonth()->format('Y-m-d');

        $this->form->fill([
            'tanggal_mulai' => $this->tanggal_mulai,
            'tanggal_akhir' => $this->tanggal_akhir,
            'status' => $this->status,
            'user_id' => $this->user_id,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                DatePicker::make('tanggal_mulai')
                    ->label('Tanggal Mulai')
                    ->default(now()->startOfMonth())
                    ->live()
                    ->afterStateUpdated(fn($state) => $this->tanggal_mulai = $state),

                DatePicker::make('tanggal_akhir')
                    ->label('Tanggal Akhir')
                    ->default(now()->endOfMonth())
                    ->live()
                    ->afterStateUpdated(fn($state) => $this->tanggal_akhir = $state),

                Select::make('status')
                    ->label('Status')
                    ->options([
                        'all' => 'Semua Status',
                        'aktif' => 'Aktif',
                        'lunas' => 'Lunas',
                        'gagal_bayar' => 'Gagal Bayar',
                        'dibatalkan' => 'Dibatalkan',
                    ])
                    ->default('all')
                    ->live()
                    ->afterStateUpdated(fn($state) => $this->status = $state),

                Select::make('user_id')
                    ->label('Anggota')
                    ->options(function () {
                        return \App\Models\User::whereHas('profiles.pinjamans.cicilanEmas')
                            ->pluck('name', 'id')
                            ->prepend('Semua Anggota', 'all');
                    })
                    ->default('all')
                    ->live()
                    ->searchable()
                    ->afterStateUpdated(fn($state) => $this->user_id = $state),
            ])
            ->statePath('data')
            ->columns(4);
    }

    public function getStatsData(): array
    {
        $data = $this->getBaseQuery()->get();

        return [
            'total_cicilan' => $data->count(),
            'total_harga' => $data->sum('total_harga'),
            'total_setoran_awal' => $data->sum('setoran_awal'),
            'total_biaya_admin' => $data->sum('biaya_admin'),
            'rata_rata_berat' => $data->avg('berat_emas'),
            'aktif_count' => $data->where('status', 'aktif')->count(),
            'lunas_count' => $data->where('status', 'lunas')->count(),
            'gagal_bayar_count' => $data->where('status', 'gagal_bayar')->count(),
            'dibatalkan_count' => $data->where('status', 'dibatalkan')->count(),
        ];
    }

    public function getStatsWidgets(): array
    {
        $stats = $this->getStatsData();

        return [
            Stat::make('Total Cicilan', number_format($stats['total_cicilan']))
                ->description('Total cicilan emas')
                ->descriptionIcon('heroicon-m-sparkles')
                ->color('success'),

            Stat::make('Total Harga', 'Rp ' . number_format($stats['total_harga'], 0, ',', '.'))
                ->description('Total nilai cicilan')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('primary'),

            Stat::make('Total Setoran Awal', 'Rp ' . number_format($stats['total_setoran_awal'], 0, ',', '.'))
                ->description('Total setoran awal')
                ->descriptionIcon('heroicon-m-arrow-down-circle')
                ->color('info'),

            Stat::make('Total Biaya Admin', 'Rp ' . number_format($stats['total_biaya_admin'], 0, ',', '.'))
                ->description('Total biaya administrasi')
                ->descriptionIcon('heroicon-m-calculator')
                ->color('warning'),

            Stat::make('Rata-rata Berat', number_format($stats['rata_rata_berat'], 2) . ' gram')
                ->description('Berat emas rata-rata')
                ->descriptionIcon('heroicon-m-scale')
                ->color('gray'),

            Stat::make('Status Aktif', number_format($stats['aktif_count']))
                ->description('Cicilan aktif')
                ->descriptionIcon('heroicon-m-play-circle')
                ->color('green'),

            Stat::make('Status Lunas', number_format($stats['lunas_count']))
                ->description('Cicilan lunas')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make('Gagal Bayar', number_format($stats['gagal_bayar_count']))
                ->description('Cicilan gagal bayar')
                ->descriptionIcon('heroicon-m-x-circle')
                ->color('danger'),
        ];
    }

    public function getStatusDistribution(): array
    {
        $data = $this->getBaseQuery()->get();

        return [
            [
                'status' => 'Aktif',
                'count' => $data->where('status', 'aktif')->count(),
                'color' => 'green'
            ],
            [
                'status' => 'Lunas',
                'count' => $data->where('status', 'lunas')->count(),
                'color' => 'blue'
            ],
            [
                'status' => 'Gagal Bayar',
                'count' => $data->where('status', 'gagal_bayar')->count(),
                'color' => 'red'
            ],
            [
                'status' => 'Dibatalkan',
                'count' => $data->where('status', 'dibatalkan')->count(),
                'color' => 'gray'
            ]
        ];
    }

    public function getMonthlyTrends(): array
    {
        $query = CicilanEmas::query();

        if ($this->user_id !== 'all') {
            $query->whereHas('pinjaman.profile', function ($q) {
                $q->where('id_user', $this->user_id);
            });
        }

        $monthlyData = [];
        $startDate = Carbon::parse($this->tanggal_mulai)->startOfMonth();
        $endDate = Carbon::parse($this->tanggal_akhir)->endOfMonth();

        $current = $startDate->copy();

        while ($current <= $endDate) {
            $monthStart = $current->copy()->startOfMonth();
            $monthEnd = $current->copy()->endOfMonth();

            $monthlyCount = $query->clone()
                ->whereBetween('created_at', [$monthStart, $monthEnd])
                ->count();

            $monthlyValue = $query->clone()
                ->whereBetween('created_at', [$monthStart, $monthEnd])
                ->sum('total_harga');

            $monthlyData[] = [
                'month' => $current->format('Y-m'),
                'count' => $monthlyCount,
                'value' => $monthlyValue
            ];

            $current->addMonth();
        }

        return $monthlyData;
    }

    public function getChartData(): array
    {
        $data = [];
        $labels = [];
        
        // Get data for last 12 months or filtered period
        $startDate = $this->tanggal_mulai ? Carbon::parse($this->tanggal_mulai) : now()->subMonths(11);
        $endDate = $this->tanggal_akhir ? Carbon::parse($this->tanggal_akhir) : now();
        
        $current = $startDate->copy()->startOfMonth();
        
        while ($current <= $endDate->endOfMonth()) {
            $labels[] = $current->format('M Y');
            
            $query = CicilanEmas::query()
                ->whereYear('created_at', $current->year)
                ->whereMonth('created_at', $current->month);
                
            // Apply filters
            if ($this->status !== 'all') {
                $query->where('status', $this->status);
            }
            
            if ($this->user_id !== 'all') {
                $query->whereHas('pinjaman.profile', function ($q) {
                    $q->where('id_user', $this->user_id);
                });
            }
            
            $count = $query->count();
            $data[] = $count;
            
            $current->addMonth();
        }

        return [
            'datasets' => [
                [
                    'label' => 'Jumlah Cicilan Emas',
                    'data' => $data,
                    'backgroundColor' => 'rgba(251, 191, 36, 0.1)',
                    'borderColor' => 'rgb(251, 191, 36)',
                    'borderWidth' => 2,
                    'fill' => true,
                ],
            ],
            'labels' => $labels,
        ];
    }

    public function getStatusChartData(): array
    {
        $statusCounts = $this->getBaseQuery()
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $labels = [];
        $data = [];
        $colors = [
            'aktif' => '#10B981',
            'lunas' => '#3B82F6', 
            'gagal_bayar' => '#EF4444',
            'dibatalkan' => '#6B7280',
        ];
        
        $backgroundColors = [];

        foreach ($statusCounts as $status => $count) {
            $labels[] = ucfirst(str_replace('_', ' ', $status));
            $data[] = $count;
            $backgroundColors[] = $colors[$status] ?? '#6B7280';
        }

        return [
            'datasets' => [
                [
                    'data' => $data,
                    'backgroundColor' => $backgroundColors,
                ],
            ],
            'labels' => $labels,
        ];
    }

    public function getTotalBeratEmas(): float
    {
        return $this->getBaseQuery()->sum('berat_emas');
    }

    public function getCicilanAktifVsLunas(): array
    {
        $data = $this->getBaseQuery()->get();
        
        return [
            'aktif' => $data->where('status', 'aktif')->count(),
            'lunas' => $data->where('status', 'lunas')->count(),
        ];
    }

    public function getBaseQuery()
    {
        $query = CicilanEmas::query()->with(['pinjaman.profile']);

        if ($this->tanggal_mulai && $this->tanggal_akhir) {
            $query->whereBetween('created_at', [
                Carbon::parse($this->tanggal_mulai)->startOfDay(),
                Carbon::parse($this->tanggal_akhir)->endOfDay()
            ]);
        }

        if ($this->status !== 'all') {
            $query->where('status', $this->status);
        }

        if ($this->user_id !== 'all') {
            $query->whereHas('pinjaman.profile', function ($q) {
                $q->where('id_user', $this->user_id);
            });
        }

        return $query;
    }

    public function cetakPDF()
    {
        $data = $this->getBaseQuery()->get();

        $stats = [
            'total_cicilan' => $data->count(),
            'total_harga' => $data->sum('total_harga'),
            'total_setoran_awal' => $data->sum('setoran_awal'),
            'total_biaya_admin' => $data->sum('biaya_admin'),
            'rata_rata_berat' => $data->avg('berat_emas'),
        ];

        $pdf = Pdf::loadView('reports.laporan-cicilan-emas', [
            'data' => $data,
            'stats' => $stats,
            'filters' => [
                'tanggal_mulai' => $this->tanggal_mulai,
                'tanggal_akhir' => $this->tanggal_akhir,
                'status' => $this->status,
                'user_id' => $this->user_id,
            ]
        ]);

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, 'laporan-cicilan-emas-' . now()->format('Y-m-d') . '.pdf');
    }
}
