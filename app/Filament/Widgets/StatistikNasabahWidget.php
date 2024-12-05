<?php

namespace App\Filament\Widgets;

use App\Models\Profile;
use App\Models\TransaksiTabungan;
use App\Models\Pinjaman;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatistikNasabahWidget extends BaseWidget
{
    protected static ?string $pollingInterval = '15s'; // Refresh setiap 15 detik

    protected int | string | array $columnSpan = '3';

    protected function getStats(): array
    {
        // Hitung total nasabah
        $totalNasabah = Profile::where('is_active', true)->count();

        // Hitung total setoran (kredit)
        $totalSetoran = TransaksiTabungan::where('jenis_transaksi', 'kredit')
            ->sum('jumlah');

        // Hitung total penarikan (debit)
        $totalPenarikan = TransaksiTabungan::where('jenis_transaksi', 'debit')
            ->sum('jumlah');

        // Hitung total pencairan kredit
        $totalPencairanKredit = Pinjaman::sum('jumlah_pinjaman');

        // Hitung total nasabah telat bayar
        $today = now();
        $totalTelatBayar = Pinjaman::where('status_pinjaman', 'approved')
            ->whereDoesntHave('transaksiPinjaman', function ($q) use ($today) {
                $q->whereMonth('tanggal_pembayaran', $today->month)
                  ->whereYear('tanggal_pembayaran', $today->year);
            })->count();

        return [
            Stat::make('Total Nasabah Aktif', number_format($totalNasabah))
                ->description('Jumlah nasabah yang terdaftar')
                ->descriptionIcon('heroicon-m-users')
                ->color('success'),

            Stat::make('Total Setoran', 'Rp ' . number_format($totalSetoran, 2))
                ->description('Total transaksi kredit')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),

            Stat::make('Total Penarikan', 'Rp ' . number_format($totalPenarikan, 2))
                ->description('Total transaksi debit')
                ->descriptionIcon('heroicon-m-arrow-trending-down')
                ->color('danger'),

            Stat::make('Total Pencairan Kredit', 'Rp ' . number_format($totalPencairanKredit, 2))
                ->description('Total pinjaman yang dicairkan')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('warning'),

            Stat::make('Total Telat Bayar', number_format($totalTelatBayar))
                ->description('Jumlah nasabah yang telat bayar')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color('danger'),
        ];
    }
}
