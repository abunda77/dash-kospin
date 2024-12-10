<?php

namespace App\Filament\Pages;

use App\Models\BackupLog;
use Filament\Pages\Page;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Actions\Action;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use Filament\Tables\Actions\BulkAction;
use Illuminate\Database\Eloquent\Collection;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;

class BackupDatabase extends Page implements HasTable
{
    use InteractsWithTable,HasPageShield ;

    protected static ?string $navigationIcon = 'heroicon-o-server';
    protected static ?string $navigationLabel = 'Database Backup';
    protected static ?string $navigationGroup = 'Setting';
    protected static ?string $title = 'Database Backup';
    protected static ?string $slug = 'backup-database';
    protected static ?int $navigationSort = 1;

    protected static string $view = 'filament.pages.backup-database';

    public function table(Table $table): Table
    {
        return $table
            ->query(BackupLog::query())
            ->columns([
                TextColumn::make('filename')
                    ->label('Nama File')
                    ->searchable(),
                TextColumn::make('type')
                    ->label('Tipe')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'manual' => 'primary',
                        'scheduled' => 'success',
                    }),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'success' => 'success',
                        'failed' => 'danger',
                    }),
                TextColumn::make('formatted_size')
                    ->label('Ukuran'),
                TextColumn::make('created_at')
                    ->label('Tanggal')
                    ->dateTime('d M Y H:i:s')
                    ->sortable(),
            ])
            ->actions([
                Action::make('download')
                    ->label('Download')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(function (BackupLog $record) {
                        // Cek path file lengkap
                        $fullPath = storage_path('app/' . $record->path);

                        // Validasi file exists menggunakan File facade
                        if (!File::exists($fullPath)) {
                            Notification::make()
                                ->danger()
                                ->title('File tidak ditemukan')
                                ->send();
                            return;
                        }

                        // Return response download dengan nama file asli
                        return response()->download(
                            $fullPath,
                            $record->filename,
                            ['Content-Type' => 'application/sql']
                        );
                    })
                    ->visible(fn (BackupLog $record) => $record->status === BackupLog::STATUS_SUCCESS),
                Action::make('delete')
                    ->label('Hapus')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function (BackupLog $record) {
                        try {
                            // Cek dan hapus file fisik menggunakan path lengkap
                            $fullPath = storage_path('app/' . $record->path);
                            if (File::exists($fullPath)) {
                                File::delete($fullPath);
                            }

                            // Hapus file dari storage jika masih ada
                            if (Storage::exists($record->path)) {
                                Storage::delete($record->path);
                            }

                            // Hapus record dari database
                            $record->delete();

                            Notification::make()
                                ->success()
                                ->title('Backup berhasil dihapus')
                                ->send();

                        } catch (\Exception $e) {
                            Log::error('Error deleting backup: ' . $e->getMessage());

                            Notification::make()
                                ->danger()
                                ->title('Gagal menghapus backup')
                                ->body('Terjadi kesalahan saat menghapus file backup')
                                ->send();
                        }
                    }),
            ])
            ->bulkActions([
                BulkAction::make('delete')
                    ->label('Hapus yang dipilih')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->deselectRecordsAfterCompletion()
                    ->action(function (Collection $records) {
                        try {
                            foreach ($records as $record) {
                                // Hapus file fisik
                                $fullPath = storage_path('app/' . $record->path);
                                if (File::exists($fullPath)) {
                                    File::delete($fullPath);
                                }

                                // Hapus dari storage jika masih ada
                                if (Storage::exists($record->path)) {
                                    Storage::delete($record->path);
                                }

                                // Hapus record dari database
                                $record->delete();
                            }

                            Notification::make()
                                ->success()
                                ->title('Backup berhasil dihapus')
                                ->send();

                        } catch (\Exception $e) {
                            Log::error('Error deleting backups: ' . $e->getMessage());

                            Notification::make()
                                ->danger()
                                ->title('Gagal menghapus backup')
                                ->body('Terjadi kesalahan saat menghapus file backup')
                                ->send();
                        }
                    })
            ])
            ->headerActions([
                Action::make('create_backup')
                    ->label('Backup Sekarang')
                    ->icon('heroicon-o-plus')
                    ->action(function () {
                        try {
                            // Generate nama file
                            $filename = 'backup-' . Carbon::now()->format('Y-m-d-H-i-s') . '.sql';
                            $path = storage_path('app/backup');

                            // Buat direktori jika belum ada
                            if (!File::exists($path)) {
                                File::makeDirectory($path, 0755, true);
                            }

                            // Buat command mysqldump dengan path lengkap
                            $command = sprintf(
                                '"%s" --user="%s" --password="%s" --host="%s" --port="%s" "%s" > "%s"',
                                'C:\\laragon\\bin\\mysql\\mysql-8.0.30-winx64\\bin\\mysqldump.exe', // Windows path
                                // '/usr/bin/mysqldump', // Linux/Ubuntu path
                                config('database.connections.mysql.username'),
                                config('database.connections.mysql.password'),
                                config('database.connections.mysql.host'),
                                config('database.connections.mysql.port'),
                                config('database.connections.mysql.database'),
                                $path . '/' . $filename
                            );

                            // Jalankan command
                            $output = shell_exec($command . ' 2>&1');

                            // Cek apakah file backup berhasil dibuat
                            if (File::exists($path . '/' . $filename)) {
                                // Buat log backup sukses
                                BackupLog::create([
                                    'filename' => $filename,
                                    'path' => 'backup/' . $filename,
                                    'size' => File::size($path . '/' . $filename),
                                    'type' => BackupLog::TYPE_MANUAL,
                                    'status' => BackupLog::STATUS_SUCCESS,
                                    'notes' => 'Backup berhasil dibuat'
                                ]);

                                Notification::make()
                                    ->success()
                                    ->title('Backup berhasil dibuat')
                                    ->send();
                            } else {
                                throw new \Exception("Backup gagal: " . ($output ?? 'Unknown error'));
                            }
                        } catch (\Exception $e) {
                            Log::error('Backup error: ' . $e->getMessage());

                            Notification::make()
                                ->danger()
                                ->title('Backup gagal')
                                ->body($e->getMessage())
                                ->send();
                        }
                    })
            ])
            ->defaultSort('created_at', 'desc')
            ->poll('30s');
    }
}
