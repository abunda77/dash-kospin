<?php

namespace App\Filament\Pages;

use App\Models\BackupLog;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class RestoreDatabase extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-arrow-path';
    protected static ?string $navigationLabel = 'Database Restore';
    protected static ?string $navigationGroup = 'Setting';
    protected static ?string $title = 'Database Restore';
    protected static ?string $slug = 'restore-database';
    protected static ?int $navigationSort = 2;

    protected static string $view = 'filament.pages.restore-database';

    public ?array $data = [];

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('backup_file')
                    ->label('Pilih File Backup')
                    ->options(function() {
                        return BackupLog::where('status', BackupLog::STATUS_SUCCESS)
                            ->orderBy('created_at', 'desc')
                            ->pluck('filename', 'id');
                    })
                    ->helperText('Pilih file backup dari daftar backup yang tersedia')
                    ->live()
                    ->afterStateUpdated(function($state) {
                        if ($state) {
                            $this->validateBackupFile($state);
                        }
                    }),

                FileUpload::make('upload_file')
                    ->label('Atau Upload File Backup')
                    ->helperText('Upload file .sql yang telah didownload sebelumnya')
                    ->acceptedFileTypes(['application/sql', 'text/plain', '.sql'])
                    ->maxSize(50 * 1024) // 50MB
                    ->directory('temp-restore')
                    ->live()
                    ->afterStateUpdated(function($state) {
                        if ($state) {
                            $this->validateUploadedFile($state);
                        }
                    }),
            ])
            ->statePath('data');
    }

    protected function validateBackupFile($backupId): void
    {
        try {
            $backup = BackupLog::findOrFail($backupId);
            $fullPath = storage_path('app/' . $backup->path);

            if (!File::exists($fullPath)) {
                Notification::make()
                    ->danger()
                    ->title('File backup tidak ditemukan')
                    ->send();
                return;
            }

        } catch (\Exception $e) {
            Log::error('Error validating backup file: ' . $e->getMessage());
            Notification::make()
                ->danger()
                ->title('Gagal memvalidasi file backup')
                ->body('Terjadi kesalahan saat memvalidasi file backup')
                ->send();
        }
    }

    protected function validateUploadedFile(TemporaryUploadedFile $file): void
    {
        try {
            // Validasi ekstensi dan konten file
            $extension = $file->getClientOriginalExtension();
            if (!in_array(strtolower($extension), ['sql'])) {
                Notification::make()
                    ->danger()
                    ->title('Format file tidak valid')
                    ->body('Hanya file .sql yang diperbolehkan')
                    ->send();
                return;
            }

        } catch (\Exception $e) {
            Log::error('Error validating uploaded file: ' . $e->getMessage());
            Notification::make()
                ->danger()
                ->title('Gagal memvalidasi file')
                ->body('Terjadi kesalahan saat memvalidasi file')
                ->send();
        }
    }

    public function restore(): void
    {
        try {
            DB::beginTransaction();

            $filePath = null;

            // Ambil file dari backup yang dipilih
            if (!empty($this->data['backup_file'])) {
                $backup = BackupLog::findOrFail($this->data['backup_file']);
                $filePath = storage_path('app/' . $backup->path);
            }
            // Atau gunakan file yang diupload
            elseif (!empty($this->data['upload_file'])) {
                /** @var TemporaryUploadedFile $uploadedFile */
                $uploadedFile = $this->data['upload_file'];

                // Validasi file yang diupload
                if (!$uploadedFile instanceof TemporaryUploadedFile) {
                    throw new \Exception('File tidak valid');
                }

                // Dapatkan path file temporary
                $filePath = $uploadedFile->getRealPath();
            }

            if (!$filePath || !File::exists($filePath)) {
                throw new \Exception('File restore tidak ditemukan');
            }

            // Baca isi file SQL
            $sql = File::get($filePath);

            // Reset database
            DB::statement('SET FOREIGN_KEY_CHECKS=0');

            // Hapus semua tabel yang ada
            $tables = DB::select('SHOW TABLES');
            foreach ($tables as $table) {
                $tableName = array_values((array)$table)[0];
                DB::statement("DROP TABLE IF EXISTS `$tableName`");
            }

            // Eksekusi SQL restore
            DB::unprepared($sql);

            DB::statement('SET FOREIGN_KEY_CHECKS=1');

            DB::commit();

            // Hapus file temporary jika ada
            if (!empty($this->data['upload_file'])) {
                $uploadedFile->delete(); // Gunakan method delete() dari TemporaryUploadedFile
            }

            Notification::make()
                ->success()
                ->title('Database berhasil dipulihkan')
                ->send();

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Restore error: ' . $e->getMessage());

            Notification::make()
                ->danger()
                ->title('Gagal memulihkan database')
                ->body($e->getMessage())
                ->send();
        }
    }
}
