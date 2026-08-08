<?php

namespace App\Filament\Widgets;

use App\Enums\StatusPenarikan;
use App\Enums\StatusSetoran;
use App\Filament\Resources\PenarikanTabunganResource;
use App\Filament\Resources\SetoranTabunganResource;
use App\Models\PenarikanTabungan;
use App\Models\SetoranTabungan;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class VerifikasiSimpananWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected ?string $heading = 'Antrean Verifikasi Simpanan';

    protected ?string $description = 'Transaksi baru yang perlu segera diperiksa oleh admin.';

    protected static ?string $pollingInterval = '15s';

    protected int|string|array $columnSpan = 'full';

    protected function getStats(): array
    {
        $jumlahSetoran = SetoranTabungan::query()
            ->where('status', StatusSetoran::MENUNGGU_VERIFIKASI)
            ->count();

        $jumlahPenarikan = PenarikanTabungan::query()
            ->where('status', StatusPenarikan::MENUNGGU_VERIFIKASI)
            ->count();

        return [
            Stat::make('Setoran Simpanan', number_format($jumlahSetoran))
                ->description('Menunggu verifikasi admin')
                ->descriptionIcon('heroicon-m-arrow-down-tray')
                ->color($jumlahSetoran > 0 ? 'warning' : 'success')
                ->url(SetoranTabunganResource::getUrl('index', [
                    'tableFilters' => [
                        'status' => ['value' => StatusSetoran::MENUNGGU_VERIFIKASI->value],
                    ],
                ])),

            Stat::make('Penarikan Simpanan', number_format($jumlahPenarikan))
                ->description('Menunggu verifikasi admin')
                ->descriptionIcon('heroicon-m-arrow-up-tray')
                ->color($jumlahPenarikan > 0 ? 'warning' : 'success')
                ->url(PenarikanTabunganResource::getUrl('index', [
                    'tableFilters' => [
                        'status' => ['value' => StatusPenarikan::MENUNGGU_VERIFIKASI->value],
                    ],
                ])),
        ];
    }
}
