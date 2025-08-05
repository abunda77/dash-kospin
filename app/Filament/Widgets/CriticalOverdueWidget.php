<?php

namespace App\Filament\Widgets;

use App\Models\Pinjaman;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseStatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Support\Colors\Color;

class CriticalOverdueWidget extends BaseStatsOverviewWidget
{
    protected static ?string $pollingInterval = '30s';
    protected static bool $isLazy = false;
    
    public function getStats(): array
    {
        $stats = $this->getCritical90DaysStats();
        
        return [
            Stat::make('🚨 Keterlambatan Kritis', number_format($stats['total_accounts']))
                ->description('Akun terlambat > 90 hari')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color(Color::Red)
                ->url('/admin/list-keterlambatan90-hari'),
                
            Stat::make('💰 Total Tunggakan Kritis', 'Rp ' . number_format($stats['total_overdue'], 0, ',', '.'))
                ->description('Pokok + Bunga + Denda > 90 hari')
                ->descriptionIcon('heroicon-m-minus-circle')
                ->color(Color::Rose)
                ->url('/admin/list-keterlambatan90-hari'),
                
            Stat::make('📊 Persentase Risiko', number_format($stats['risk_percentage'], 1) . '%')
                ->description('Dari total pinjaman aktif')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color($this->getRiskColor($stats['risk_percentage'])),
                
            Stat::make('⏰ Rata-rata Keterlambatan', number_format($stats['avg_overdue_days'], 0) . ' hari')
                ->description('Rata-rata keterlambatan kritis')
                ->descriptionIcon('heroicon-m-clock')
                ->color(Color::Amber),
        ];
    }

    private function getCritical90DaysStats(): array
    {
        $today = Carbon::today();
        
        // Query untuk pinjaman dengan keterlambatan > 90 hari
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
        
        // Total pinjaman aktif untuk perhitungan persentase
        $totalActiveLoans = Pinjaman::where('status_pinjaman', 'approved')->count();
        
        $totalAccounts = $critical90DaysData->count();
        
        $totalOverdue = $critical90DaysData->sum(function ($record) use ($today) {
            $angsuranPokok = $record->jumlah_pinjaman / $record->jangka_waktu;
            $bungaPerBulan = ($record->jumlah_pinjaman * ($record->biayaBungaPinjaman->persentase_bunga/100)) / $record->jangka_waktu;
            
            // Calculate overdue days
            $lastTransaction = $record->transaksiPinjaman()->orderBy('angsuran_ke', 'desc')->first();
            if ($lastTransaction) {
                $tanggalJatuhTempo = Carbon::parse($lastTransaction->tanggal_pembayaran)->addMonth()->startOfDay();
            } else {
                $tanggalJatuhTempo = Carbon::parse($record->tanggal_pinjaman)->addMonth()->startOfDay();
            }
            
            $hariTerlambat = max(0, $today->diffInDays($tanggalJatuhTempo));
            $jumlahBulanTerlambat = ceil($hariTerlambat / 30);
            
            // Calculate total overdue amount
            $totalPokok = $angsuranPokok * $jumlahBulanTerlambat;
            $totalBunga = $bungaPerBulan * $jumlahBulanTerlambat;
            $angsuranTotal = $angsuranPokok + $bungaPerBulan;
            $dendaPerHari = (0.05 * $angsuranTotal) / 30;
            $totalDenda = $dendaPerHari * $hariTerlambat;
            
            return $totalPokok + $totalBunga + $totalDenda;
        });
        
        $avgOverdueDays = $critical90DaysData->avg(function ($record) use ($today) {
            $lastTransaction = $record->transaksiPinjaman()->orderBy('angsuran_ke', 'desc')->first();
            if ($lastTransaction) {
                $tanggalJatuhTempo = Carbon::parse($lastTransaction->tanggal_pembayaran)->addMonth()->startOfDay();
            } else {
                $tanggalJatuhTempo = Carbon::parse($record->tanggal_pinjaman)->addMonth()->startOfDay();
            }
            
            return max(0, $today->diffInDays($tanggalJatuhTempo));
        }) ?: 0;
        
        $riskPercentage = $totalActiveLoans > 0 ? ($totalAccounts / $totalActiveLoans) * 100 : 0;
        
        return [
            'total_accounts' => $totalAccounts,
            'total_overdue' => $totalOverdue,
            'risk_percentage' => $riskPercentage,
            'avg_overdue_days' => $avgOverdueDays,
        ];
    }
    
    private function getRiskColor(float $percentage): string
    {
        if ($percentage >= 20) return Color::Red[500];
        if ($percentage >= 10) return Color::Orange[500];
        if ($percentage >= 5) return Color::Yellow[500];
        return Color::Green[500];
    }
    
    public static function canView(): bool
    {
        $user = \Filament\Facades\Filament::auth()->user();
        return $user && \Illuminate\Support\Facades\Gate::forUser($user)->allows('page_ListKeterlambatan90Hari');
    }
}
