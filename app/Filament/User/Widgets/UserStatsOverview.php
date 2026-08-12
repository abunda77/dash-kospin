<?php

namespace App\Filament\User\Widgets;

use App\Models\Deposito;
use App\Models\Pinjaman;
use App\Models\Tabungan;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class UserStatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected int|string|array $columnSpan = 'full';

    protected function getStats(): array
    {
        $profile = Auth::user()->profile;

        if (! $profile) {
            return [
                Stat::make('Total Tabungan', '0')
                    ->description('Belum ada profile')
                    ->descriptionIcon('heroicon-m-information-circle')
                    ->color('gray'),
            ];
        }

        $tabungans = Tabungan::where('id_profile', $profile->id)
            ->where('status_rekening', 'aktif')
            ->get();

        $totalSaldoTabungan = $tabungans->sum(fn ($tabungan) => $tabungan->saldo_akhir);

        // Hitung sisa pinjaman
        $pinjamans = Pinjaman::where('profile_id', $profile->id_user)
            ->where('status_pinjaman', 'approved')
            ->with('transaksiPinjaman')
            ->get();

        $totalSisaPinjaman = 0;
        foreach ($pinjamans as $pinjaman) {
            $lastTransaksi = $pinjaman->transaksiPinjaman->sortByDesc('id')->first();
            $totalSisaPinjaman += $lastTransaksi ? $lastTransaksi->sisa_pinjaman : $pinjaman->jumlah_pinjaman;
        }

        // Total deposito aktif
        $totalDeposito = Deposito::where('id_user', $profile->id_user)
            ->whereIn('status', ['aktif', 'Aktif', 'active', 'Active'])
            ->sum('nominal_penempatan');

        // Jumlah rekening
        $jumlahTabungan = $tabungans->count();
        $jumlahPinjaman = Pinjaman::where('profile_id', $profile->id_user)->count();
        $jumlahDeposito = Deposito::where('id_user', $profile->id_user)->count();

        return [
            Stat::make('Total Saldo Tabungan', format_rupiah($totalSaldoTabungan))
                ->description($jumlahTabungan.' rekening tabungan')
                ->descriptionIcon('heroicon-m-banknotes')
                ->chart([7, 3, 4, 5, 6, 3, 5, 3])
                ->color('success'),

            Stat::make('Sisa Pinjaman', format_rupiah($totalSisaPinjaman))
                ->description($pinjamans->count().' pinjaman aktif')
                ->descriptionIcon('heroicon-m-credit-card')
                ->chart([3, 5, 4, 3, 6, 5, 4, 3])
                ->color('danger'),

            Stat::make('Total Deposito', format_rupiah($totalDeposito))
                ->description($jumlahDeposito.' deposito')
                ->descriptionIcon('heroicon-m-building-library')
                ->chart([4, 5, 6, 5, 7, 6, 8, 7])
                ->color('info'),
        ];
    }
}
