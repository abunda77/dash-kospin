<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class CheckExportProgressCommand extends Command
{
    protected $signature = 'report:check-progress {key? : Progress key to check}';
    protected $description = 'Check the progress of running PDF export jobs';

    public function handle(): int
    {
        $key = $this->argument('key');
        
        if ($key) {
            return $this->checkSpecificProgress($key);
        }
        
        return $this->listAllProgress();
    }

    private function checkSpecificProgress(string $key): int
    {
        $progress = Cache::get($key);
        
        if (!$progress) {
            $this->error("❌ No progress found for key: {$key}");
            return Command::FAILURE;
        }
        
        $this->displayProgress($key, $progress);
        return Command::SUCCESS;
    }

    private function listAllProgress(): int
    {
        // Get all progress keys (this is a simplified approach)
        $this->info('🔍 Scanning for active export processes...');
        
        // In a real implementation, you might want to store active keys in a separate cache entry
        $this->line('💡 To check specific progress, use: php artisan report:check-progress <key>');
        $this->line('💡 Progress keys are displayed when starting exports');
        
        return Command::SUCCESS;
    }

    private function displayProgress(string $key, array $progress): void
    {
        $this->info("📊 Export Progress: {$key}");
        $this->newLine();
        
        $processed = $progress['processed'] ?? 0;
        $total = $progress['total'] ?? 0;
        $percent = $progress['percent'] ?? 0;
        $status = $progress['status'] ?? 'unknown';
        
        // Create progress bar
        if ($total > 0) {
            $progressBar = $this->output->createProgressBar($total);
            $progressBar->setFormat(' %current%/%max% [%bar%] %percent:3s%% %message%');
            $progressBar->setMessage("Status: {$status}");
            $progressBar->setProgress($processed);
            $progressBar->display();
            $this->newLine(2);
        }
        
        // Display details
        $this->line("📈 <info>Processed:</info> {$processed}/{$total} ({$percent}%)");
        $this->line("🔄 <info>Status:</info> {$status}");
        
        if (isset($progress['started_at'])) {
            $startedAt = Carbon::parse($progress['started_at']);
            $this->line("⏰ <info>Started:</info> {$startedAt->format('d M Y H:i:s')}");
            $this->line("⏱️  <info>Duration:</info> {$startedAt->diffForHumans(null, true)}");
        }
        
        if (isset($progress['updated_at'])) {
            $updatedAt = Carbon::parse($progress['updated_at']);
            $this->line("🔄 <info>Last Update:</info> {$updatedAt->format('d M Y H:i:s')}");
        }
        
        // Estimate completion time
        if ($total > 0 && $processed > 0 && $processed < $total) {
            $this->estimateCompletion($progress);
        }
    }

    private function estimateCompletion(array $progress): void
    {
        if (!isset($progress['started_at'])) {
            return;
        }
        
        $startedAt = Carbon::parse($progress['started_at']);
        $now = Carbon::now();
        $elapsed = $now->diffInSeconds($startedAt);
        
        $processed = $progress['processed'];
        $total = $progress['total'];
        $remaining = $total - $processed;
        
        if ($processed > 0 && $elapsed > 0) {
            $rate = $processed / $elapsed; // items per second
            $estimatedSecondsRemaining = $remaining / $rate;
            
            $estimatedCompletion = $now->addSeconds($estimatedSecondsRemaining);
            
            $this->line("⏳ <info>Estimated Completion:</info> {$estimatedCompletion->format('d M Y H:i:s')}");
            $this->line("🕐 <info>Time Remaining:</info> " . $this->formatDuration($estimatedSecondsRemaining));
        }
    }

    private function formatDuration(float $seconds): string
    {
        if ($seconds < 60) {
            return round($seconds) . ' seconds';
        } elseif ($seconds < 3600) {
            return round($seconds / 60) . ' minutes';
        } else {
            $hours = floor($seconds / 3600);
            $minutes = round(($seconds % 3600) / 60);
            return "{$hours}h {$minutes}m";
        }
    }
}