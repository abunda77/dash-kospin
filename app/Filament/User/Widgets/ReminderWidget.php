<?php

namespace App\Filament\User\Widgets;

use Filament\Widgets\Widget;
use App\Models\Pinjaman;
use App\Models\Deposito;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ReminderWidget extends Widget
{
    protected static ?int $sort = 5;
    
    protected int | string | array $columnSpan = 'full';

    protected static string $view = 'filament.user.widgets.reminder-widget';

    public function getReminders(): array
    {
        $profile = Auth::user()->profile;
        $reminders = [];

        if (!$profile) {
            return $reminders;
        }

        // Pinjaman yang akan jatuh tempo dalam 30 hari
        $pinjamanJatuhTempo = Pinjaman::where('profile_id', $profile->id_user)
            ->where('status_pinjaman', 'approved')
            ->whereBetween('tanggal_jatuh_tempo', [Carbon::now(), Carbon::now()->addDays(30)])
            ->with('produkPinjaman')
            ->get();

        foreach ($pinjamanJatuhTempo as $pinjaman) {
            $daysLeft = Carbon::now()->diffInDays(Carbon::parse($pinjaman->tanggal_jatuh_tempo));
            $reminders[] = [
                'type' => 'pinjaman',
                'icon' => 'heroicon-o-exclamation-triangle',
                'color' => $daysLeft <= 7 ? 'danger' : 'warning',
                'title' => 'Pinjaman Jatuh Tempo',
                'message' => "Pinjaman {$pinjaman->no_pinjaman} akan jatuh tempo dalam {$daysLeft} hari",
                'date' => Carbon::parse($pinjaman->tanggal_jatuh_tempo)->format('d M Y'),
            ];
        }

        // Pinjaman yang sudah melewati jatuh tempo
        $pinjamanTerlambat = Pinjaman::where('profile_id', $profile->id_user)
            ->where('status_pinjaman', 'approved')
            ->where('tanggal_jatuh_tempo', '<', Carbon::now())
            ->with('produkPinjaman')
            ->get();

        foreach ($pinjamanTerlambat as $pinjaman) {
            $daysLate = Carbon::now()->diffInDays(Carbon::parse($pinjaman->tanggal_jatuh_tempo));
            $reminders[] = [
                'type' => 'pinjaman_terlambat',
                'icon' => 'heroicon-o-x-circle',
                'color' => 'danger',
                'title' => 'Pinjaman Terlambat',
                'message' => "Pinjaman {$pinjaman->no_pinjaman} sudah terlambat {$daysLate} hari!",
                'date' => Carbon::parse($pinjaman->tanggal_jatuh_tempo)->format('d M Y'),
            ];
        }

        // Deposito yang akan jatuh tempo dalam 30 hari
        $depositoJatuhTempo = Deposito::where('id_user', $profile->id_user)
            ->whereIn('status', ['aktif', 'Aktif', 'active', 'Active'])
            ->whereBetween('tanggal_jatuh_tempo', [Carbon::now(), Carbon::now()->addDays(30)])
            ->get();

        foreach ($depositoJatuhTempo as $deposito) {
            $daysLeft = Carbon::now()->diffInDays(Carbon::parse($deposito->tanggal_jatuh_tempo));
            $message = $deposito->perpanjangan_otomatis 
                ? "Deposito {$deposito->nomor_rekening} akan diperpanjang otomatis dalam {$daysLeft} hari"
                : "Deposito {$deposito->nomor_rekening} akan jatuh tempo dalam {$daysLeft} hari";
            
            $reminders[] = [
                'type' => 'deposito',
                'icon' => 'heroicon-o-bell-alert',
                'color' => $deposito->perpanjangan_otomatis ? 'info' : 'warning',
                'title' => 'Deposito Jatuh Tempo',
                'message' => $message,
                'date' => Carbon::parse($deposito->tanggal_jatuh_tempo)->format('d M Y'),
                'aro' => $deposito->perpanjangan_otomatis,
            ];
        }

        return $reminders;
    }
}
