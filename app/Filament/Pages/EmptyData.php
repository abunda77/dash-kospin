<?php

namespace App\Filament\Pages;

use App\Models\Activity;
use App\Models\Pinjaman;
use App\Models\Tabungan;
use Filament\Pages\Page;
use Filament\Actions\Action;
use App\Models\TransaksiPinjaman;
use App\Models\TransaksiTabungan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Filament\Support\Exceptions\Halt;

use Illuminate\Support\Facades\Artisan;
use Filament\Notifications\Notification;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Spatie\Activitylog\Models\Activity as SpatieActivity;
use Exception;

class EmptyData extends Page
{
    use HasPageShield;
    protected static ?string $navigationIcon = 'heroicon-o-trash';
    protected static ?string $navigationLabel = 'Hapus Data';
    protected static ?string $title = 'Hapus Data';
    public static function getNavigationGroup(): ?string
    {
        return 'Settings';
    }

    protected static string $view = 'filament.pages.empty-data';

    private function createEmptyAction(string $name, string $model, string $successMessage): Action
    {
        return Action::make($name)
            ->label('Hapus Semua Data')
            ->requiresConfirmation()
            ->color('danger')
            ->action(function () use ($model, $successMessage) {
                try {
                    Log::info("Mencoba menghapus data {$model}");

                    $modelClass = "App\\Models\\{$model}";
                    $modelClass::truncate();

                    Log::info("Berhasil menghapus data {$model}");

                    Notification::make()
                        ->title($successMessage)
                        ->success()
                        ->send();

                } catch (\Exception $e) {
                    Log::error("Gagal menghapus data {$model}: " . $e->getMessage());

                    Notification::make()
                        ->title('Gagal menghapus data')
                        ->danger()
                        ->send();

                    throw new Halt($e->getMessage());
                }
            });
    }

    public function emptyTransaksiTabungan(): Action
    {
        return $this->createEmptyAction(
            'emptyTransaksiTabungan',
            'TransaksiTabungan',
            'Data transaksi tabungan berhasil dihapus'
        );
    }

    public function emptyTransaksiPinjaman(): Action
    {
        return $this->createEmptyAction(
            'emptyTransaksiPinjaman',
            'TransaksiPinjaman',
            'Data transaksi pinjaman berhasil dihapus'
        );
    }

    public function emptyTabungan(): Action
    {
        return $this->createEmptyAction(
            'emptyTabungan',
            'Tabungan',
            'Data tabungan berhasil dihapus'
        );
    }

    public function emptyPinjaman(): Action
    {
        return $this->createEmptyAction(
            'emptyPinjaman',
            'Pinjaman',
            'Data pinjaman berhasil dihapus'
        );
    }

    public function emptyActivities(): Action
    {
        return $this->createEmptyAction(
            'emptyActivities',
            'Activity',
            'Data aktivitas berhasil dihapus'
        );
    }

    public function emptyActivitiesLog(): Action
    {
        return Action::make('emptyActivitiesLog')
            ->label('Hapus Semua Log Aktivitas')
            ->requiresConfirmation()
            ->color('danger')
            ->action(function () {
                try {
                    Log::info("Mencoba menghapus data log aktivitas");

                    // Menggunakan model Activity dari package spatie
                    \Spatie\Activitylog\Models\Activity::truncate();

                    Log::info("Berhasil menghapus data log aktivitas");

                    Notification::make()
                        ->title('Data log aktivitas berhasil dihapus')
                        ->success()
                        ->send();

                } catch (\Exception $e) {
                    Log::error("Gagal menghapus data log aktivitas: " . $e->getMessage());

                    Notification::make()
                        ->title('Gagal menghapus data log aktivitas')
                        ->danger()
                        ->send();

                    throw new Halt($e->getMessage());
                }
            });
    }

    public function emptySessions(): Action
    {
        return Action::make('emptySessions')
            ->label('Hapus Semua Sessions')
            ->requiresConfirmation()
            ->color('danger')
            ->action(function () {
                try {
                    Log::info("Mencoba menghapus data sessions");

                    DB::table('sessions')->truncate();

                    Log::info("Berhasil menghapus data sessions");

                    Notification::make()
                        ->title('Data sessions berhasil dihapus')
                        ->success()
                        ->send();

                } catch (\Exception $e) {
                    Log::error("Gagal menghapus data sessions: " . $e->getMessage());

                    Notification::make()
                        ->title('Gagal menghapus data sessions')
                        ->danger()
                        ->send();

                    throw new Halt($e->getMessage());
                }
            });
    }

    public function optimizeCache(): Action
    {
        return Action::make('optimizeCache')
            ->label('Optimize Cache')
            ->requiresConfirmation()
            ->color('warning')
            ->action(function () {
                try {
                    Log::info("Mencoba mengoptimasi cache");

                    // Menjalankan perintah artisan
                    Artisan::call('optimize:clear');
                    Artisan::call('optimize');

                    Log::info("Berhasil mengoptimasi cache");

                    // Mengubah cara menampilkan notifikasi
                    Notification::make()
                        ->title('Cache berhasil dioptimasi')
                        ->success()
                        ->persistent() // Membuat notifikasi tetap ada sampai user menutupnya
                        ->send();

                    return true; // Menambahkan return value

                } catch (Exception $e) {
                    Log::error("Gagal mengoptimasi cache: " . $e->getMessage());

                    Notification::make()
                        ->title('Gagal mengoptimasi cache')
                        ->body($e->getMessage()) // Menambahkan detail error
                        ->danger()
                        ->persistent() // Membuat notifikasi tetap ada sampai user menutupnya
                        ->send();

                    return false;
                }
            });
    }
}
