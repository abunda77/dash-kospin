<?php

namespace App\Filament\Pages;

use Carbon\Carbon;
use App\Models\Pinjaman;
use Filament\Pages\Page;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action as TableAction;
use Filament\Actions\Action;
use Illuminate\Support\Facades\Log;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Notifications\Notification;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Concerns\InteractsWithTable;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Illuminate\Support\Facades\Http;
use Dompdf\Dompdf;
use Dompdf\Options;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Support\Colors\Color;

class NPLReport extends Page implements HasTable, HasForms
{
    use InteractsWithTable;
    use InteractsWithForms;
    use HasPageShield;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static ?string $navigationLabel = 'Laporan NPL';
    protected static ?string $title = 'Laporan Non Performing Loan';
    protected static string $view = 'filament.pages.npl-report';

    protected static ?string $navigationGroup = 'Laporan';

    public static function getNavigationBadge(): ?string
    {
        $today = Carbon::today();

        return Pinjaman::query()
            ->where('status_pinjaman', 'approved')
            ->where(function ($query) use ($today) {
                $query->whereHas('transaksiPinjaman', function ($q) use ($today) {
                    $q->whereRaw('DATEDIFF(?, tanggal_jatuh_tempo) > 90', [$today]);
                })
                ->orWhere(function ($q) use ($today) {
                    $q->whereDoesntHave('transaksiPinjaman')
                      ->whereRaw('DATEDIFF(?, tanggal_pinjaman) > 90', [$today]);
                });
            })
            ->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }

    private function getBaseQuery()
    {
        $today = Carbon::today();

        return Pinjaman::query()
            ->with(['profile', 'produkPinjaman', 'biayaBungaPinjaman', 'denda', 'transaksiPinjaman'])
            ->where('status_pinjaman', 'approved')
            ->where(function ($query) use ($today) {
                $query->whereHas('transaksiPinjaman', function ($q) use ($today) {
                    $q->whereRaw('DATEDIFF(?, tanggal_jatuh_tempo) > 90', [$today]);
                })
                ->orWhere(function ($q) use ($today) {
                    $q->whereDoesntHave('transaksiPinjaman')
                      ->whereRaw('DATEDIFF(?, tanggal_pinjaman) > 90', [$today]);
                });
            });
    }

    public function getData()
    {
        return $this->getBaseQuery()->get();
    }

    public function getStatsData(): array
    {
        $data = $this->getData();
        $today = Carbon::today();
        
        $totalPinjaman = $data->count();
        $totalNominal = abs($data->sum('jumlah_pinjaman'));
        $totalOutstanding = abs($data->sum('jumlah_pinjaman'));
          $totalDenda = abs($data->sum(function ($record) use ($today) {
            $angsuranPokok = $this->calculateAngsuranPokok($record);
            $hariTerlambat = $this->calculateHariTerlambat($record, $today);
            return $this->calculateDenda($record, $angsuranPokok, $hariTerlambat);
        }));
        
        $rataRataHariTerlambat = abs($data->avg(function ($record) use ($today) {
            return abs($this->calculateHariTerlambat($record, $today));
        }));        return [
            'total_pinjaman' => $totalPinjaman,
            'total_nominal' => $totalNominal,
            'total_outstanding' => $totalOutstanding,
            'total_denda' => $totalDenda,
            'rata_rata_hari_terlambat' => $rataRataHariTerlambat,
            'rasio_npl' => $this->calculateRasioNPL($totalNominal),
        ];
    }

    public function getStatsWidgets(): array
    {
        $stats = $this->getStatsData();
        
        return [            Stat::make('Rasio NPL', number_format($stats['rasio_npl'], 2, ',', '.') . '%')
                ->description('Pinjaman Bermasalah / Total Pinjaman Dicairkan')
                ->descriptionIcon('heroicon-m-chart-pie')
                ->color(Color::Red),
                
            Stat::make('Total Akun NPL', number_format($stats['total_pinjaman']))
                ->description('Pinjaman dengan keterlambatan > 90 hari')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color(Color::Red),
                
            Stat::make('Total Nominal NPL', 'Rp ' . number_format($stats['total_nominal'], 0, ',', '.'))
                ->description('Nilai total pinjaman bermasalah')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color(Color::Orange),
                
            Stat::make('Total Denda', 'Rp ' . number_format($stats['total_denda'], 0, ',', '.'))
                ->description('Akumulasi denda keterlambatan')
                ->descriptionIcon('heroicon-m-minus-circle')
                ->color(Color::Rose),
                
            Stat::make('Rata-rata Keterlambatan', number_format($stats['rata_rata_hari_terlambat'], 0) . ' hari')
                ->description('Rata-rata hari keterlambatan')
                ->descriptionIcon('heroicon-m-clock')
                ->color(Color::Amber),
        ];
    }

    private function calculateAngsuranPokok($record)
    {
        return $record->jumlah_pinjaman / $record->jangka_waktu;
    }    private function calculateHariTerlambat($record, $today)
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
    }    private function calculateBungaPerBulan($record)
    {
        $pokok = $record->jumlah_pinjaman;
        $bungaPerTahun = $record->biayaBungaPinjaman->persentase_bunga;
        $jangkaWaktu = $record->jangka_waktu;

        // Hitung bunga per bulan (total bunga setahun dibagi jangka waktu)
        return ($pokok * ($bungaPerTahun/100)) / $jangkaWaktu;
    }

    private function calculateDenda($record, $angsuranPokok, $hariTerlambat)
    {
        // Hitung angsuran total per bulan (pokok + bunga)
        $angsuranBunga = $this->calculateBungaPerBulan($record);
        $angsuranTotal = $angsuranPokok + $angsuranBunga;

        // Hitung denda per hari (5% x Angsuran Total / 30)
        $dendaPerHari = (0.05 * $angsuranTotal) / 30;

        // Total denda = denda per hari x jumlah hari terlambat
        return $dendaPerHari * $hariTerlambat;
    }

    private function calculateRasioNPL($totalNominalNPL)
    {
        $totalPinjamanDicairkan = Pinjaman::where('status_pinjaman', 'approved')->sum('jumlah_pinjaman');

        if ($totalPinjamanDicairkan > 0) {
            return ($totalNominalNPL / $totalPinjamanDicairkan) * 100;
        }

        return 0;
    }

    public function table(Table $table): Table
    {
        $today = Carbon::today();

        return $table
            ->query($this->getBaseQuery())
            ->columns([
                TextColumn::make('no')
                    ->label('No')
                    ->rowIndex(),

                TextColumn::make('profile.first_name')
                    ->label('Nama')
                    ->formatStateUsing(function ($record) {
                        return trim($record->profile->first_name . ' ' . $record->profile->last_name);
                    })
                    ->searchable()
                    ->sortable(),

                TextColumn::make('no_pinjaman')
                    ->label('No Pinjaman')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('produkPinjaman.nama_produk')
                    ->label('Produk')
                    ->sortable(),

                TextColumn::make('jumlah_pinjaman')
                    ->label('Nominal Pinjaman')
                    ->money('IDR')
                    ->sortable(),                TextColumn::make('jumlah_pinjaman')
                    ->label('Angsuran Pokok')
                    ->formatStateUsing(function ($record) {
                        $angsuranPokok = $this->calculateAngsuranPokok($record);
                        return 'Rp.' . number_format($angsuranPokok, 2, ',', '.');
                    }),

                TextColumn::make('biayaBungaPinjaman.persentase_bunga')
                    ->label('Bunga')
                    ->formatStateUsing(function ($record) {
                        $bunga = $this->calculateBungaPerBulan($record);
                        return 'Rp.' . number_format($bunga, 2, ',', '.');
                    }),

                TextColumn::make('denda_id')
                    ->label('Denda')
                    ->formatStateUsing(function ($record) use ($today) {
                        try {
                            $angsuranPokok = $this->calculateAngsuranPokok($record);
                            $hariTerlambat = $this->calculateHariTerlambat($record, $today);
                            $denda = abs($this->calculateDenda($record, $angsuranPokok, $hariTerlambat));
                            return 'Rp.' . number_format($denda, 2, ',', '.');
                        } catch (\Exception $e) {
                            Log::error('Error calculating denda: ' . $e->getMessage());
                            return 'Rp.0,00';
                        }
                    }),

                TextColumn::make('tanggal_pinjaman')
                    ->label('Tanggal Pinjaman')
                    ->date()
                    ->sortable(),                TextColumn::make('tanggal_jatuh_tempo')
                    ->label('Hari Terlambat')
                    ->formatStateUsing(function ($record) use ($today) {
                        $hariTerlambat = $this->calculateHariTerlambat($record, $today);
                        return abs($hariTerlambat) . ' hari';
                    })
                    ->badge()
                    ->color(function ($record) use ($today) {
                        $hariTerlambat = abs($this->calculateHariTerlambat($record, $today));
                        if ($hariTerlambat >= 180) return Color::Red;
                        if ($hariTerlambat >= 120) return Color::Orange;
                        return Color::Yellow;
                    })
                    ->sortable(),                TextColumn::make('jangka_waktu')
                    ->label('Status')
                    ->formatStateUsing(function ($record) use ($today) {
                        $hariTerlambat = abs($this->calculateHariTerlambat($record, $today));
                        if ($hariTerlambat >= 180) return 'Kritis';
                        if ($hariTerlambat >= 120) return 'Bermasalah';
                        return 'NPL';
                    })
                    ->badge()
                    ->color(function ($record) use ($today) {
                        $hariTerlambat = abs($this->calculateHariTerlambat($record, $today));
                        if ($hariTerlambat >= 180) return Color::Red;
                        if ($hariTerlambat >= 120) return Color::Orange;
                        return Color::Blue;
                    }),
            ])
            ->defaultSort('tanggal_pinjaman', 'desc')
            ->filters([
                //
            ])
            ->actions([
                TableAction::make('detail')
                    ->label('Detail')
                    ->icon('heroicon-m-eye')
                    ->url(fn (Pinjaman $record): string => route('filament.admin.resources.pinjamen.view', ['record' => $record])),
            ])
            ->bulkActions([
                //
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('exportPdf')
                ->label('Export PDF')
                ->icon('heroicon-m-document-arrow-down')
                ->color('success')
                ->action('exportToPdf'),
        ];
    }    public function exportToPdf()
    {
        $data = $this->getData();
        $stats = $this->getStatsData();
        $today = Carbon::today();

        // Calculate values for each record to avoid using $this in Blade template
        $calculatedData = $data->map(function ($record) use ($today) {
            $hariTerlambat = abs($this->calculateHariTerlambat($record, $today));
            $angsuranPokok = $this->calculateAngsuranPokok($record);
            $denda = abs($this->calculateDenda($record, $angsuranPokok, $hariTerlambat));
            
            // Add calculated values as properties to the existing record
            $record->calculated_hari_terlambat = $hariTerlambat;
            $record->calculated_denda = $denda;
            
            return $record;
        });

        $html = view('pdf.npl-report', [
            'data' => $calculatedData,
            'stats' => $stats,
            'today' => $today,
        ])->render();

        $dompdf = new Dompdf();
        $options = new Options();
        $options->set('defaultFont', 'Arial');
        $dompdf->setOptions($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        return response()->streamDownload(function () use ($dompdf) {
            echo $dompdf->output();
        }, 'laporan-npl-' . now()->format('Y-m-d') . '.pdf');
    }
}