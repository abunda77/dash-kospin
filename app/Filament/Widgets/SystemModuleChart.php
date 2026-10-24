<?php

namespace App\Filament\Widgets;

use App\Support\SystemInfo;
use Filament\Widgets\ChartWidget;

class SystemModuleChart extends ChartWidget
{
    protected static bool $isDiscovered = false;

    protected static ?string $heading = 'Modul PHP per Kategori';

    protected static ?string $description = 'Jumlah ekstensi yang aktif pada proses ini';

    protected static ?string $maxHeight = '220px';

    protected int|string|array $columnSpan = 1;

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getData(): array
    {
        $counts = app(SystemInfo::class)->moduleCountsByCategory();

        return [
            'datasets' => [
                [
                    'label' => 'Modul',
                    'data' => array_values($counts),
                    'backgroundColor' => ['#3b82f6', '#8b5cf6', '#10b981'],
                    'borderRadius' => 4,
                ],
            ],
            'labels' => array_keys($counts),
        ];
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => ['display' => false],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => ['precision' => 0],
                ],
            ],
            'maintainAspectRatio' => false,
        ];
    }
}
