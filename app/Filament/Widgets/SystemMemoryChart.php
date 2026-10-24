<?php

namespace App\Filament\Widgets;

use App\Support\SystemInfo;
use Filament\Widgets\ChartWidget;

class SystemMemoryChart extends ChartWidget
{
    protected static bool $isDiscovered = false;

    protected static ?string $heading = 'RAM Fisik';

    protected static ?string $description = 'Perbandingan RAM terpakai dan tersedia';

    protected static ?string $maxHeight = '220px';

    protected int|string|array $columnSpan = 1;

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getData(): array
    {
        $systemInfo = app(SystemInfo::class);
        $memory = $systemInfo->memorySnapshot();

        if ($memory['total'] === null) {
            return [
                'datasets' => [
                    [
                        'label' => 'RAM',
                        'data' => [0],
                        'backgroundColor' => ['#9ca3af'],
                    ],
                ],
                'labels' => ['Tidak terdeteksi'],
            ];
        }

        return [
            'datasets' => [
                [
                    'label' => 'RAM (GB)',
                    'data' => [
                        round((float) $memory['used'] / 1024 ** 3, 2),
                        round((float) $memory['free'] / 1024 ** 3, 2),
                    ],
                    'backgroundColor' => ['#ef4444', '#22c55e'],
                    'borderWidth' => 0,
                ],
            ],
            'labels' => ['Terpakai', 'Tersedia'],
        ];
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => ['position' => 'bottom'],
            ],
            'cutout' => '62%',
            'maintainAspectRatio' => false,
        ];
    }
}
