<?php

namespace App\Filament\Resources\MakanBergizisGratisResource\Widgets;

use App\Models\MakanBergizisGratis;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class MakanBergizisGratisStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        // Total hari ini
        $totalHariIni = MakanBergizisGratis::whereDate('tanggal_pemberian', today())->count();
        
        // Total minggu ini
        $totalMingguIni = MakanBergizisGratis::whereBetween('tanggal_pemberian', [
            now()->startOfWeek(),
            now()->endOfWeek()
        ])->count();
        
        // Total bulan ini
        $totalBulanIni = MakanBergizisGratis::whereMonth('tanggal_pemberian', now()->month)
            ->whereYear('tanggal_pemberian', now()->year)
            ->count();
        
        // Total keseluruhan
        $totalKeseluruhan = MakanBergizisGratis::count();
        
        // Perbandingan dengan kemarin
        $totalKemarin = MakanBergizisGratis::whereDate('tanggal_pemberian', today()->subDay())->count();
        $perubahanHarian = $totalKemarin > 0 
            ? (($totalHariIni - $totalKemarin) / $totalKemarin) * 100 
            : 0;

        return [
            Stat::make('Hari Ini', $totalHariIni)
                ->description($perubahanHarian >= 0 
                    ? '+' . number_format($perubahanHarian, 1) . '% dari kemarin'
                    : number_format($perubahanHarian, 1) . '% dari kemarin'
                )
                ->descriptionIcon($perubahanHarian >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($perubahanHarian >= 0 ? 'success' : 'danger')
                ->chart([
                    MakanBergizisGratis::whereDate('tanggal_pemberian', today()->subDays(6))->count(),
                    MakanBergizisGratis::whereDate('tanggal_pemberian', today()->subDays(5))->count(),
                    MakanBergizisGratis::whereDate('tanggal_pemberian', today()->subDays(4))->count(),
                    MakanBergizisGratis::whereDate('tanggal_pemberian', today()->subDays(3))->count(),
                    MakanBergizisGratis::whereDate('tanggal_pemberian', today()->subDays(2))->count(),
                    MakanBergizisGratis::whereDate('tanggal_pemberian', today()->subDays(1))->count(),
                    $totalHariIni,
                ]),
            
            Stat::make('Minggu Ini', $totalMingguIni)
                ->description('Total pemberian minggu ini')
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('info'),
            
            Stat::make('Bulan Ini', $totalBulanIni)
                ->description('Total pemberian bulan ' . now()->format('F'))
                ->descriptionIcon('heroicon-m-calendar')
                ->color('warning'),
            
            Stat::make('Total Keseluruhan', $totalKeseluruhan)
                ->description('Total pemberian sejak awal')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color('success'),
        ];
    }
}
