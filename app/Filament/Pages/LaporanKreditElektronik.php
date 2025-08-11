<?php

namespace App\Filament\Pages;

use App\Models\KreditElektronik;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Widgets\StatsOverviewWidget\Stat;

class LaporanKreditElektronik extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static string $view = 'filament.pages.laporan-kredit-elektronik';
    protected static ?string $navigationGroup = 'Laporan';
    protected static ?string $title = 'Laporan Kredit Elektronik';
    protected static ?string $navigationLabel = 'Laporan Kredit Elektronik';

    public ?array $data = [];
    public $tanggal_mulai;
    public $tanggal_akhir;
    public $status_kredit = 'all';
    public $jenis_barang = 'all';

    public function mount(): void
    {
        $this->tanggal_mulai = now()->startOfMonth()->format('Y-m-d');
        $this->tanggal_akhir = now()->endOfMonth()->format('Y-m-d');

        $this->form->fill([
            'tanggal_mulai' => $this->tanggal_mulai,
            'tanggal_akhir' => $this->tanggal_akhir,
            'status_kredit' => $this->status_kredit,
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

                Select::make('status_kredit')
                    ->label('Status Kredit')
                    ->options([
                        'all' => 'Semua Status',
                        'aktif' => 'Aktif',
                        'lunas' => 'Lunas',
                        'macet' => 'Macet',
                    ])
                    ->default('all')
                    ->live()
                    ->afterStateUpdated(fn($state) => $this->status_kredit = $state),

                Select::make('jenis_barang')
                    ->label('Jenis Barang')
                    ->options([
                        'all' => 'Semua Barang',
                        'laptop' => 'Laptop',
                        'hp' => 'HP',
                        'tv' => 'TV',
                        'kamera' => 'Kamera',
                        'lainnya' => 'Lainnya',
                    ])
                    ->default('all')
                    ->live()
                    ->afterStateUpdated(fn($state) => $this->jenis_barang = $state),
            ]);
    }

    public function getBaseQuery()
    {
        $query = KreditElektronik::query()->with(['pinjaman.profile']);
 
        if ($this->tanggal_mulai && $this->tanggal_akhir) {
            $query->whereBetween('created_at', [
                Carbon::parse($this->tanggal_mulai)->startOfDay(),
                Carbon::parse($this->tanggal_akhir)->endOfDay(),
            ]);
        }
 
        if ($this->status_kredit !== 'all') {
            $query->where('status_kredit', $this->status_kredit);
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
        if (in_array($property, ['tanggal_mulai', 'tanggal_akhir', 'status_kredit', 'jenis_barang'])) {
            $this->dispatch('refreshHeaderWidgets');
        }
    }

    public function exportPdf()
    {
        $query = KreditElektronik::query()
            ->with(['pinjaman.profile']);

        if ($this->tanggal_mulai && $this->tanggal_akhir) {
            $query->whereBetween('created_at', [
                Carbon::parse($this->tanggal_mulai)->startOfDay(),
                Carbon::parse($this->tanggal_akhir)->endOfDay(),
            ]);
        }

        if ($this->status_kredit !== 'all') {
            $query->where('status_kredit', $this->status_kredit);
        }

        if ($this->jenis_barang !== 'all') {
            $query->where('jenis_barang', $this->jenis_barang);
        }

        $data = $query->get();

        $stats = [
            'total_kredit' => $data->count(),
            'total_harga_barang' => $data->sum('harga_barang'),
            'total_uang_muka' => $data->sum('uang_muka'),
            'total_nilai_hutang' => $data->sum('nilai_hutang'),
            'rata_rata_harga' => $data->avg('harga_barang'),
        ];

        $filters = [
            'tanggal_mulai' => $this->tanggal_mulai,
            'tanggal_akhir' => $this->tanggal_akhir,
            'status_kredit' => $this->status_kredit,
            'jenis_barang' => $this->jenis_barang,
        ];

        $pdf = PDF::loadView('reports.laporan-kredit-elektronik', [
            'data' => $data,
            'stats' => $stats,
            'filters' => $filters,
        ]);

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, 'laporan-kredit-elektronik.pdf');
    }

    public function cetakPDF()
    {
        return $this->exportPdf();
    }
}
