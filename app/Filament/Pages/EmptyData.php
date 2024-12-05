<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Actions\Action;
use Filament\Support\Exceptions\Halt;
use Filament\Notifications\Notification;
use App\Models\TransaksiTabungan;
use App\Models\TransaksiPinjaman;
use App\Models\Tabungan;
use App\Models\Pinjaman;

class EmptyData extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-trash';
    protected static ?string $navigationLabel = 'Hapus Data';
    protected static ?string $title = 'Hapus Data';
    protected static ?string $navigationGroup = 'Setting';

    protected static string $view = 'filament.pages.empty-data';

    public function emptyTransaksiTabungan(): Action
    {
        return Action::make('emptyTransaksiTabungan')
            ->label('Hapus Semua Data')
            ->requiresConfirmation()
            ->color('danger')
            ->action(function () {
                try {
                    TransaksiTabungan::truncate();
                    Notification::make()
                        ->title('Data transaksi tabungan berhasil dihapus')
                        ->success()
                        ->send();
                } catch (\Exception $e) {
                    Notification::make()
                        ->title('Gagal menghapus data')
                        ->danger()
                        ->send();
                    throw new Halt($e->getMessage());
                }
            });
    }

    public function emptyTransaksiPinjaman(): Action
    {
        return Action::make('emptyTransaksiPinjaman')
            ->label('Hapus Semua Data')
            ->requiresConfirmation()
            ->color('danger')
            ->action(function () {
                try {
                    TransaksiPinjaman::truncate();
                    Notification::make()
                        ->title('Data transaksi pinjaman berhasil dihapus')
                        ->success()
                        ->send();
                } catch (\Exception $e) {
                    Notification::make()
                        ->title('Gagal menghapus data')
                        ->danger()
                        ->send();
                    throw new Halt($e->getMessage());
                }
            });
    }

    public function emptyTabungan(): Action
    {
        return Action::make('emptyTabungan')
            ->label('Hapus Semua Data')
            ->requiresConfirmation()
            ->color('danger')
            ->action(function () {
                try {
                    Tabungan::truncate();
                    Notification::make()
                        ->title('Data tabungan berhasil dihapus')
                        ->success()
                        ->send();
                } catch (\Exception $e) {
                    Notification::make()
                        ->title('Gagal menghapus data')
                        ->danger()
                        ->send();
                    throw new Halt($e->getMessage());
                }
            });
    }

    public function emptyPinjaman(): Action
    {
        return Action::make('emptyPinjaman')
            ->label('Hapus Semua Data')
            ->requiresConfirmation()
            ->color('danger')
            ->action(function () {
                try {
                    Pinjaman::truncate();
                    Notification::make()
                        ->title('Data pinjaman berhasil dihapus')
                        ->success()
                        ->send();
                } catch (\Exception $e) {
                    Notification::make()
                        ->title('Gagal menghapus data')
                        ->danger()
                        ->send();
                    throw new Halt($e->getMessage());
                }
            });
    }
}
