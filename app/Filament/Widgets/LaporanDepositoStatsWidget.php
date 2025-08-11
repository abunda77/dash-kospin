<?php

namespace App\Filament\Widgets;

use App\Models\Deposito;
use Filament\Widgets\StatsOverviewWidget as BaseStatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class LaporanDepositoStatsWidget extends BaseStatsOverviewWidget
{
    protected function getStats(): array
    {
        // Get filters from the page if available
        $page = $this->getPage();
        
        if ($page && method_exists($page, 'getBaseQuery')) {
            $query = $page->getBaseQuery();
        } else {
            // Fallback to default query
            $query = Deposito::query();
        }
        
        $totalDeposito = $query->count();
        $totalNominal = $query->sum('nominal_penempatan') ?: 0;
        $totalBunga = $query->sum('nominal_bunga') ?: 0;
        $rataRataNominal = $query->avg('nominal_penempatan') ?: 0;

        return [
            Stat::make('Total Deposito', number_format($totalDeposito))
                ->description('Jumlah deposito')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('primary'),
            
            Stat::make('Total Nominal', 'Rp ' . number_format($totalNominal, 0, ',', '.'))
                ->description('Total penempatan')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('success'),
            
            Stat::make('Total Bunga', 'Rp ' . number_format($totalBunga, 0, ',', '.'))
                ->description('Total bunga yang akan diterima')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color('warning'),
            
            Stat::make('Rata-rata Nominal', 'Rp ' . number_format($rataRataNominal, 0, ',', '.'))
                ->description('Rata-rata penempatan per deposito')
                ->descriptionIcon('heroicon-m-calculator')
                ->color('info'),
        ];
    }

    protected function getPage()
    {
        return $this->livewire ?? null;
    }
}