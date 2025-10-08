<?php

namespace App\Filament\Resources\BarcodeScanLogResource\Widgets;

use App\Models\BarcodeScanLog;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ScanStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $totalScans = BarcodeScanLog::count();
        $todayScans = BarcodeScanLog::whereDate('scanned_at', today())->count();
        $weekScans = BarcodeScanLog::where('scanned_at', '>=', now()->subDays(7))->count();
        $mobileScans = BarcodeScanLog::where('is_mobile', true)->count();
        $mobilePercentage = $totalScans > 0 ? round(($mobileScans / $totalScans) * 100, 1) : 0;

        // Get unique IPs
        $uniqueIps = BarcodeScanLog::distinct('ip_address')->count('ip_address');

        // Most scanned tabungan
        $mostScanned = BarcodeScanLog::selectRaw('tabungan_id, COUNT(*) as scan_count')
            ->groupBy('tabungan_id')
            ->orderByDesc('scan_count')
            ->with('tabungan')
            ->first();

        return [
            Stat::make('Total Scans', number_format($totalScans))
                ->description('All time barcode scans')
                ->descriptionIcon('heroicon-o-qr-code')
                ->color('success')
                ->chart([7, 12, 15, 18, 22, 25, $todayScans]),

            Stat::make('Today', number_format($todayScans))
                ->description('Scans today')
                ->descriptionIcon('heroicon-o-calendar')
                ->color('info'),

            Stat::make('This Week', number_format($weekScans))
                ->description('Last 7 days')
                ->descriptionIcon('heroicon-o-chart-bar')
                ->color('warning'),

            Stat::make('Mobile Scans', $mobilePercentage . '%')
                ->description(number_format($mobileScans) . ' from mobile devices')
                ->descriptionIcon('heroicon-o-device-phone-mobile')
                ->color('primary'),

            Stat::make('Unique IPs', number_format($uniqueIps))
                ->description('Different IP addresses')
                ->descriptionIcon('heroicon-o-globe-alt')
                ->color('gray'),

            Stat::make('Most Scanned', $mostScanned ? $mostScanned->tabungan->no_tabungan : 'N/A')
                ->description($mostScanned ? $mostScanned->scan_count . ' scans' : 'No data')
                ->descriptionIcon('heroicon-o-fire')
                ->color('danger'),
        ];
    }
}
