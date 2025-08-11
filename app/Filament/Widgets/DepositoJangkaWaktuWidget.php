<?php

namespace App\Filament\Widgets;

use App\Models\Deposito;
use Filament\Widgets\ChartWidget;

class DepositoJangkaWaktuWidget extends ChartWidget
{
    protected static ?string $heading = 'Distribusi Jangka Waktu Deposito';
    protected static ?int $sort = 3;

    protected function getData(): array
    {
        $data = Deposito::query()
            ->selectRaw('jangka_waktu, COUNT(*) as count')
            ->groupBy('jangka_waktu')
            ->orderBy('jangka_waktu')
            ->get();

        $labels = [];
        $chartData = [];
        $colors = [
            'rgba(59, 130, 246, 0.8)',   // Blue
            'rgba(34, 197, 94, 0.8)',    // Green
            'rgba(251, 191, 36, 0.8)',   // Yellow
            'rgba(239, 68, 68, 0.8)',    // Red
            'rgba(168, 85, 247, 0.8)',   // Purple
        ];

        foreach ($data as $index => $item) {
            $labels[] = $item->jangka_waktu . ' Bulan';
            $chartData[] = $item->count;
        }

        return [
            'datasets' => [
                [
                    'data' => $chartData,
                    'backgroundColor' => array_slice($colors, 0, count($chartData)),
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}