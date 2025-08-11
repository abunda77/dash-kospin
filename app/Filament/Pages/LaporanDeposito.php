<?php

namespace App\Filament\Pages;

use App\Models\Deposito;
use App\Filament\Widgets\LaporanDepositoStatsWidget;
use App\Filament\Widgets\DepositoChartWidget;
use App\Filament\Widgets\DepositoJangkaWaktuWidget;
use Filament\Pages\Page;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class LaporanDeposito extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static string $view = 'filament.pages.laporan-deposito';
    protected static ?string $navigationGroup = 'Laporan';
    protected static ?string $title = 'Laporan Deposito';
    protected static ?string $navigationLabel = 'Laporan Deposito';

    public ?array $data = [];
    public $tanggal_mulai;
    public $tanggal_akhir;
    public $status = 'all';
    public $jangka_waktu = 'all';

    public function mount(): void
    {
        $this->tanggal_mulai = now()->startOfMonth()->format('Y-m-d');
        $this->tanggal_akhir = now()->endOfMonth()->format('Y-m-d');

        $this->form->fill([
            'tanggal_mulai' => $this->tanggal_mulai,
            'tanggal_akhir' => $this->tanggal_akhir,
            'status' => $this->status,
            'jangka_waktu' => $this->jangka_waktu,
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
                        'active' => 'Aktif',
                        'ended' => 'Berakhir',
                        'cancelled' => 'Dibatalkan',
                    ])
                    ->default('all')
                    ->live()
                    ->afterStateUpdated(fn($state) => $this->status = $state),

                Select::make('jangka_waktu')
                    ->label('Jangka Waktu')
                    ->options([
                        'all' => 'Semua Jangka Waktu',
                        '1' => '1 Bulan',
                        '3' => '3 Bulan',
                        '6' => '6 Bulan',
                        '12' => '12 Bulan',
                        '24' => '24 Bulan',
                    ])
                    ->default('all')
                    ->live()
                    ->afterStateUpdated(fn($state) => $this->jangka_waktu = $state),
            ])
            ->statePath('data')
            ->columns(4);
    }

    protected function getHeaderWidgets(): array
    {
        return [
            LaporanDepositoStatsWidget::class,
            DepositoChartWidget::class,
            DepositoJangkaWaktuWidget::class,
        ];
    }

    public function getWidgetData(): array
    {
        $query = $this->getBaseQuery();

        return [
            'total_deposito' => $query->count(),
            'total_nominal' => $query->sum('nominal_penempatan'),
            'total_bunga' => $query->sum('nominal_bunga'),
            'rata_rata_nominal' => $query->avg('nominal_penempatan'),
            'tanggal_mulai' => $this->tanggal_mulai,
            'tanggal_akhir' => $this->tanggal_akhir,
            'status' => $this->status,
            'jangka_waktu' => $this->jangka_waktu,
        ];
    }

    public function getBaseQuery()
    {
        $query = Deposito::query()->with('profile');

        if ($this->tanggal_mulai && $this->tanggal_akhir) {
            $query->whereBetween('tanggal_pembukaan', [
                Carbon::parse($this->tanggal_mulai)->startOfDay(),
                Carbon::parse($this->tanggal_akhir)->endOfDay()
            ]);
        }

        if ($this->status !== 'all') {
            $query->where('status', $this->status);
        }

        if ($this->jangka_waktu !== 'all') {
            $query->where('jangka_waktu', $this->jangka_waktu);
        }

        return $query;
    }

    public function cetakPDF()
    {
        $data = $this->getBaseQuery()->get();

        $stats = [
            'total_deposito' => $data->count(),
            'total_nominal' => $data->sum('nominal_penempatan'),
            'total_bunga' => $data->sum('nominal_bunga'),
            'rata_rata_nominal' => $data->avg('nominal_penempatan'),
        ];

        $pdf = Pdf::loadView('reports.laporan-deposito', [
            'data' => $data,
            'stats' => $stats,
            'filters' => [
                'tanggal_mulai' => $this->tanggal_mulai,
                'tanggal_akhir' => $this->tanggal_akhir,
                'status' => $this->status,
                'jangka_waktu' => $this->jangka_waktu,
            ]
        ]);

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, 'laporan-deposito-' . now()->format('Y-m-d') . '.pdf');
    }
}
