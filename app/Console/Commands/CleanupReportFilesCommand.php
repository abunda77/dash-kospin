<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class CleanupReportFilesCommand extends Command
{
    protected $signature = 'report:cleanup 
                            {--hours=24 : Delete files older than specified hours}
                            {--dry-run : Show what would be deleted without actually deleting}
                            {--force : Skip confirmation prompt}';

    protected $description = 'Clean up old PDF report files from storage';

    public function handle(): int
    {
        $hours = (int) $this->option('hours');
        $dryRun = $this->option('dry-run');
        $force = $this->option('force');

        $this->info("🧹 Starting cleanup of report files older than {$hours} hours...");
        
        $cutoffTime = Carbon::now()->subHours($hours);
        $this->line("📅 Cutoff time: {$cutoffTime->format('d M Y H:i:s')}");
        
        // Clean public reports
        $publicFiles = $this->getOldFiles('public', 'reports', $cutoffTime);
        
        // Clean temp files
        $tempFiles = $this->getOldTempFiles($cutoffTime);
        
        $totalFiles = count($publicFiles) + count($tempFiles);
        
        if ($totalFiles === 0) {
            $this->info('✅ No old files found to clean up.');
            return Command::SUCCESS;
        }
        
        $this->displayFilesToDelete($publicFiles, $tempFiles, $dryRun);
        
        if ($dryRun) {
            $this->info('🔍 Dry run completed. Use without --dry-run to actually delete files.');
            return Command::SUCCESS;
        }
        
        if (!$force && !$this->confirm("Are you sure you want to delete {$totalFiles} files?")) {
            $this->info('❌ Operation cancelled.');
            return Command::SUCCESS;
        }
        
        $deleted = $this->deleteFiles($publicFiles, $tempFiles);
        
        $this->info("✅ Cleanup completed. Deleted {$deleted} files.");
        
        return Command::SUCCESS;
    }

    private function getOldFiles(string $disk, string $directory, Carbon $cutoffTime): array
    {
        $files = [];
        
        if (!Storage::disk($disk)->exists($directory)) {
            return $files;
        }
        
        $allFiles = Storage::disk($disk)->files($directory);
        
        foreach ($allFiles as $file) {
            $lastModified = Carbon::createFromTimestamp(Storage::disk($disk)->lastModified($file));
            
            if ($lastModified->lt($cutoffTime) && str_ends_with($file, '.pdf')) {
                $files[] = [
                    'disk' => $disk,
                    'path' => $file,
                    'size' => Storage::disk($disk)->size($file),
                    'modified' => $lastModified,
                    'full_path' => Storage::disk($disk)->path($file)
                ];
            }
        }
        
        return $files;
    }

    private function getOldTempFiles(Carbon $cutoffTime): array
    {
        $files = [];
        $tempDir = storage_path('app/temp');
        
        if (!is_dir($tempDir)) {
            return $files;
        }
        
        $iterator = new \DirectoryIterator($tempDir);
        
        foreach ($iterator as $fileInfo) {
            if ($fileInfo->isDot() || !$fileInfo->isFile()) {
                continue;
            }
            
            $lastModified = Carbon::createFromTimestamp($fileInfo->getMTime());
            
            if ($lastModified->lt($cutoffTime) && str_ends_with($fileInfo->getFilename(), '.pdf')) {
                $files[] = [
                    'disk' => 'temp',
                    'path' => $fileInfo->getFilename(),
                    'size' => $fileInfo->getSize(),
                    'modified' => $lastModified,
                    'full_path' => $fileInfo->getPathname()
                ];
            }
        }
        
        return $files;
    }

    private function displayFilesToDelete(array $publicFiles, array $tempFiles, bool $dryRun): void
    {
        $this->newLine();
        
        if ($dryRun) {
            $this->warn('🔍 DRY RUN - Files that would be deleted:');
        } else {
            $this->warn('🗑️  Files to be deleted:');
        }
        
        $this->newLine();
        
        if (!empty($publicFiles)) {
            $this->line('<info>📁 Public Reports:</info>');
            $this->displayFileList($publicFiles);
            $this->newLine();
        }
        
        if (!empty($tempFiles)) {
            $this->line('<info>📁 Temporary Files:</info>');
            $this->displayFileList($tempFiles);
            $this->newLine();
        }
        
        $totalSize = array_sum(array_merge(
            array_column($publicFiles, 'size'),
            array_column($tempFiles, 'size')
        ));
        
        $this->line("📊 <info>Total files:</info> " . (count($publicFiles) + count($tempFiles)));
        $this->line("📏 <info>Total size:</info> " . $this->formatFileSize($totalSize));
    }

    private function displayFileList(array $files): void
    {
        foreach ($files as $file) {
            $size = $this->formatFileSize($file['size']);
            $age = $file['modified']->diffForHumans();
            
            $this->line("   • {$file['path']} ({$size}, {$age})");
        }
    }

    private function deleteFiles(array $publicFiles, array $tempFiles): int
    {
        $deleted = 0;
        
        // Delete public files
        foreach ($publicFiles as $file) {
            try {
                if (Storage::disk($file['disk'])->delete($file['path'])) {
                    $deleted++;
                    $this->line("✅ Deleted: {$file['path']}");
                } else {
                    $this->error("❌ Failed to delete: {$file['path']}");
                }
            } catch (\Exception $e) {
                $this->error("❌ Error deleting {$file['path']}: {$e->getMessage()}");
            }
        }
        
        // Delete temp files
        foreach ($tempFiles as $file) {
            try {
                if (unlink($file['full_path'])) {
                    $deleted++;
                    $this->line("✅ Deleted: {$file['path']}");
                } else {
                    $this->error("❌ Failed to delete: {$file['path']}");
                }
            } catch (\Exception $e) {
                $this->error("❌ Error deleting {$file['path']}: {$e->getMessage()}");
            }
        }
        
        return $deleted;
    }

    private function formatFileSize(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= pow(1024, $pow);
        
        return round($bytes, 2) . ' ' . $units[$pow];
    }
}