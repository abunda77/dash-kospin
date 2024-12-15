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
    use InteractsWithTable, HasPageShield;

    protected static ?string $navigationIcon = 'heroicon-o-server';
    protected static ?string $navigationLabel = 'Backup Database';
    public static function getNavigationGroup(): ?string
            {
                return 'Settings';
            }
    protected static ?string $title = 'Backup Database';
    protected static ?string $slug = 'backup-database';
    protected static ?int $navigationSort = 3;
    protected static string $view = 'filament.pages.backup-database';

    private const BACKUP_PATH = 'backup';
    private const MYSQLDUMP_PATH = [
        'windows' => 'C:\\laragon\\bin\\mysql\\mysql-8.0.30-winx64\\bin\\mysqldump.exe',
        'linux' => '/usr/bin/mysqldump'
    ];

    public function table(Table $table): Table
    {
        return $table
            ->query(BackupLog::query())
            ->columns($this->getTableColumns())
            ->actions($this->getTableActions())
            ->bulkActions($this->getTableBulkActions())
            ->headerActions($this->getTableHeaderActions())
            ->defaultSort('created_at', 'desc')
            ->poll('30s');
    }

    private function getTableColumns(): array
    {
        return [
            TextColumn::make('filename')
                ->label('Nama File')
                ->searchable(),
            TextColumn::make('type')
                ->label('Tipe')
                ->badge()
                ->color(fn (string $state): string => $this->getTypeColor($state)),
            TextColumn::make('status')
                ->label('Status')
                ->badge()
                ->color(fn (string $state): string => $this->getStatusColor($state)),
            TextColumn::make('formatted_size')
                ->label('Ukuran'),
            TextColumn::make('created_at')
                ->label('Tanggal')
                ->dateTime('d M Y H:i:s')
                ->sortable(),
        ];
    }

    private function getTypeColor(string $type): string
    {
        return match ($type) {
            'manual' => 'primary',
            'scheduled' => 'success',
            default => 'secondary'
        };
    }

    private function getStatusColor(string $status): string
    {
        return match ($status) {
            'success' => 'success',
            'failed' => 'danger',
            default => 'warning'
        };
    }

    private function getTableActions(): array
    {
        return [
            Action::make('download')
                ->label('Download')
                ->icon('heroicon-o-arrow-down-tray')
                ->action(fn (BackupLog $record) => $this->handleDownload($record))
                ->visible(fn (BackupLog $record) => $record->status === BackupLog::STATUS_SUCCESS),

            Action::make('delete')
                ->label('Hapus')
                ->icon('heroicon-o-trash')
                ->color('danger')
                ->requiresConfirmation()
                ->action(fn (BackupLog $record) => $this->handleDelete($record))
        ];
    }

    private function handleDownload(BackupLog $record)
    {
        $fullPath = storage_path('app/' . $record->path);

        if (!File::exists($fullPath)) {
            $this->sendErrorNotification('File tidak ditemukan');
            return;
        }

        return response()->download(
            $fullPath,
            $record->filename,
            ['Content-Type' => 'application/sql']
        );
    }

    private function handleDelete(BackupLog $record)
    {
        try {
            $this->deleteBackupFile($record);
            $record->delete();
            $this->sendSuccessNotification('Backup berhasil dihapus');
        } catch (\Exception $e) {
            Log::error('Error deleting backup: ' . $e->getMessage());
            $this->sendErrorNotification('Gagal menghapus backup', 'Terjadi kesalahan saat menghapus file backup');
        }
    }

    private function deleteBackupFile(BackupLog $record): void
    {
        $fullPath = storage_path('app/' . $record->path);

        if (File::exists($fullPath)) {
            File::delete($fullPath);
        }

        if (Storage::exists($record->path)) {
            Storage::delete($record->path);
        }
    }

    private function getTableBulkActions(): array
    {
        return [
            BulkAction::make('delete')
                ->label('Hapus yang dipilih')
                ->icon('heroicon-o-trash')
                ->color('danger')
                ->requiresConfirmation()
                ->deselectRecordsAfterCompletion()
                ->action(fn (Collection $records) => $this->handleBulkDelete($records))
        ];
    }

    private function handleBulkDelete(Collection $records)
    {
        try {
            foreach ($records as $record) {
                $this->deleteBackupFile($record);
                $record->delete();
            }
            $this->sendSuccessNotification('Backup berhasil dihapus');
        } catch (\Exception $e) {
            Log::error('Error deleting backups: ' . $e->getMessage());
            $this->sendErrorNotification('Gagal menghapus backup', 'Terjadi kesalahan saat menghapus file backup');
        }
    }

    private function getTableHeaderActions(): array
    {
        return [
            Action::make('create_backup')
                ->label('Backup Sekarang')
                ->icon('heroicon-o-plus')
                ->action(fn () => $this->createBackup())
        ];
    }

    private function createBackup()
    {
        try {
            $filename = $this->generateBackupFilename();
            $path = $this->ensureBackupDirectory();
            $command = $this->buildMysqlDumpCommand($filename);

            $output = shell_exec($command . ' 2>&1');

            $this->validateAndLogBackup($filename, $path, $output);
        } catch (\Exception $e) {
            Log::error('Backup error: ' . $e->getMessage());
            $this->sendErrorNotification('Backup gagal', $e->getMessage());
        }
    }

    private function generateBackupFilename(): string
    {
        return 'backup-' . Carbon::now()->format('Y-m-d-H-i-s') . '.sql';
    }

    private function ensureBackupDirectory(): string
    {
        $path = storage_path('app/' . self::BACKUP_PATH);
        if (!File::exists($path)) {
            File::makeDirectory($path, 0755, true);
        }
        return $path;
    }

    private function buildMysqlDumpCommand(string $filename): string
    {
        $mysqldumpPath = PHP_OS_FAMILY === 'Windows'
            ? self::MYSQLDUMP_PATH['windows']
            : self::MYSQLDUMP_PATH['linux'];

        return sprintf(
            '"%s" --user="%s" --password="%s" --host="%s" --port="%s" "%s" > "%s"',
            $mysqldumpPath,
            config('database.connections.mysql.username'),
            config('database.connections.mysql.password'),
            config('database.connections.mysql.host'),
            config('database.connections.mysql.port'),
            config('database.connections.mysql.database'),
            $this->ensureBackupDirectory() . '/' . $filename
        );
    }

    private function validateAndLogBackup(string $filename, string $path, ?string $output)
    {
        $fullPath = $path . '/' . $filename;

        if (!File::exists($fullPath)) {
            throw new \Exception("Backup gagal: " . ($output ?? 'Unknown error'));
        }

        BackupLog::create([
            'filename' => $filename,
            'path' => self::BACKUP_PATH . '/' . $filename,
            'size' => File::size($fullPath),
            'type' => BackupLog::TYPE_MANUAL,
            'status' => BackupLog::STATUS_SUCCESS,
            'notes' => 'Backup berhasil dibuat'
        ]);

        $this->sendSuccessNotification('Backup berhasil dibuat');
    }

    private function sendSuccessNotification(string $message): void
    {
        Notification::make()
            ->success()
            ->title($message)
            ->send();
    }

    private function sendErrorNotification(string $title, ?string $body = null): void
    {
        $notification = Notification::make()
            ->danger()
            ->title($title);

        if ($body) {
            $notification->body($body);
        }

        $notification->send();
    }
}
