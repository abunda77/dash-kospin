<?php

namespace App\Filament\Widgets;

use App\Models\Deposito;
use Filament\Widgets\ChartWidget;

class DepositoChartWidget extends ChartWidget
{
    protected static ?string $heading = 'Trend Deposito Bulanan';
    protected static ?int $sort = 2;

    protected function getData(): array
    {
        $data = Deposito::query()
            ->selectRaw('MONTH(tanggal_pembukaan) as month, COUNT(*) as count, SUM(nominal_penempatan) as total')
            ->whereYear('tanggal_pembukaan', now()->year)
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $months = [
            1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr',
            5 => 'Mei', 6 => 'Jun', 7 => 'Jul', 8 => 'Agu',
            9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des'
        ];

        $chartData = [];
        $chartLabels = [];
        $nominalData = [];

        foreach (range(1, 12) as $month) {
            $monthData = $data->firstWhere('month', $month);
            $chartLabels[] = $months[$month];
            $chartData[] = $monthData ? $monthData->count : 0;
            $nominalData[] = $monthData ? ($monthData->total / 1000000) : 0; // Convert to millions
        }

        return [
            'datasets' => [
                [
                    'label' => 'Jumlah Deposito',
                    'data' => $chartData,
                    'borderColor' => 'rgb(59, 130, 246)',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                ],
                [
                    'label' => 'Nominal (Juta Rupiah)',
                    'data' => $nominalData,
                    'borderColor' => 'rgb(34, 197, 94)',
                    'backgroundColor' => 'rgba(34, 197, 94, 0.1)',
                    'yAxisID' => 'y1',
                ],
            ],
            'labels' => $chartLabels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'y' => [
                    'type' => 'linear',
                    'display' => true,
                    'position' => 'left',
                ],
                'y1' => [
                    'type' => 'linear',
                    'display' => true,
                    'position' => 'right',
                    'grid' => [
                        'drawOnChartArea' => false,
                    ],
                ],
            ],
        ];
    }
}