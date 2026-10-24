<?php

namespace App\Filament\Widgets;

use App\Support\SystemInfo;
use Filament\Widgets\ChartWidget;

class SystemDiskChart extends ChartWidget
{
    protected static bool $isDiscovered = false;

    protected static ?string $heading = 'Kapasitas Disk';

    protected static ?string $description = 'Pemakaian partisi tempat aplikasi berada';

    protected static ?string $maxHeight = '220px';

    protected int|string|array $columnSpan = 1;

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getData(): array
    {
        $disk = app(SystemInfo::class)->diskSnapshot();

        if ($disk['total'] === null) {
            return [
                'datasets' => [
                    [
                        'label' => 'Disk',
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
                    'label' => 'Disk (GB)',
                    'data' => [
                        round((float) $disk['used'] / 1024 ** 3, 2),
                        round((float) $disk['free'] / 1024 ** 3, 2),
                    ],
                    'backgroundColor' => ['#f59e0b', '#22c55e'],
                    'borderWidth' => 0,
                ],
            ],
            'labels' => ['Terpakai', 'Bebas'],
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
