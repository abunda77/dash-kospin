<?php

namespace App\Filament\Pages;

use App\Models\Gadai;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Widgets\StatsOverviewWidget\Stat;

class LaporanGadai extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static string $view = 'filament.pages.laporan-gadai';
    protected static ?string $navigationGroup = 'Laporan';
    protected static ?string $title = 'Laporan Gadai';
    protected static ?string $navigationLabel = 'Laporan Gadai';

    public ?array $data = [];
    public $tanggal_mulai;
    public $tanggal_akhir;
    public $status_gadai = 'all';
    public $jenis_barang = 'all';

    public function mount(): void
    {
        $this->tanggal_mulai = now()->startOfMonth()->format('Y-m-d');
        $this->tanggal_akhir = now()->endOfMonth()->format('Y-m-d');

        $this->form->fill([
            'tanggal_mulai' => $this->tanggal_mulai,
            'tanggal_akhir' => $this->tanggal_akhir,
            'status_gadai' => $this->status_gadai,
            'jenis_barang' => $this->jenis_barang,
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

                Select::make('status_gadai')
                    ->label('Status Gadai')
                    ->options([
                        'all' => 'Semua Status',
                        'aktif' => 'Aktif',
                        'lunas' => 'Lunas',
                        'lelang' => 'Lelang',
                        'macet' => 'Macet',
                    ])
                    ->default('all')
                    ->live()
                    ->afterStateUpdated(fn($state) => $this->status_gadai = $state),

                Select::make('jenis_barang')
                    ->label('Jenis Barang')
                    ->options([
                        'all' => 'Semua Barang',
                        'emas' => 'Emas',
                        'laptop' => 'Laptop',
                        'hp' => 'HP',
                        'kendaraan' => 'Kendaraan',
                        'lainnya' => 'Lainnya',
                    ])
                    ->default('all')
                    ->live()
                    ->afterStateUpdated(fn($state) => $this->jenis_barang = $state),
            ]);
    }

    public function getBaseQuery()
    {
        $query = Gadai::query()->with(['pinjaman.profile']);

        if ($this->tanggal_mulai && $this->tanggal_akhir) {
            $query->whereBetween('created_at', [
                Carbon::parse($this->tanggal_mulai)->startOfDay(),
                Carbon::parse($this->tanggal_akhir)->endOfDay(),
            ]);
        }

        if ($this->status_gadai !== 'all') {
            $query->where('status_gadai', $this->status_gadai);
        }

        if ($this->jenis_barang !== 'all') {
            $query->where('jenis_barang', $this->jenis_barang);
        }

        return $query;
    }

    public function getHeaderWidgets(): array
    {
        // Header widgets are rendered inline in the Blade view to avoid
        // attempting to render Stat objects as Filament widget components.
        return [];
    }

    public function updated($property): void
    {
        if (in_array($property, ['tanggal_mulai', 'tanggal_akhir', 'status_gadai', 'jenis_barang'])) {
            $this->dispatch('refreshHeaderWidgets');
        }
    }

    public function exportPdf()
    {
        $query = Gadai::query()
            ->with(['pinjaman.profile']);

        if ($this->tanggal_mulai && $this->tanggal_akhir) {
            $query->whereBetween('created_at', [$this->tanggal_mulai, $this->tanggal_akhir]);
        }

        if ($this->status_gadai !== 'all') {
            $query->where('status_gadai', $this->status_gadai);
        }

        if ($this->jenis_barang !== 'all') {
            $query->where('jenis_barang', $this->jenis_barang);
        }

        $data = $query->get();
 
        $stats = [
            'total_gadai' => $data->count(),
            'total_harga_barang' => $data->sum('harga_barang'),
            'total_nilai_taksasi' => $data->sum('nilai_taksasi'),
            'total_nilai_hutang' => $data->sum('nilai_hutang'),
            'rata_rata_taksasi' => $data->avg('nilai_taksasi'),
        ];
 
        $filters = [
            'tanggal_mulai' => $this->tanggal_mulai,
            'tanggal_akhir' => $this->tanggal_akhir,
            'status_gadai' => $this->status_gadai,
            'jenis_barang' => $this->jenis_barang,
        ];
 
        $pdf = PDF::loadView('reports.laporan-gadai', [
            'data' => $data,
            'stats' => $stats,
            'filters' => $filters,
        ]);
 
        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, 'laporan-gadai.pdf');
    }

    public function cetakPDF()
    {
        return $this->exportPdf();
    }
}
