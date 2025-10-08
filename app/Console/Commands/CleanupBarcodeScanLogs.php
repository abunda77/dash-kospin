<?php

namespace App\Console\Commands;

use App\Models\BarcodeScanLog;
use Illuminate\Console\Command;
use Carbon\Carbon;

class CleanupBarcodeScanLogs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'barcode:cleanup-logs {--days=90 : Number of days to keep logs}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cleanup old barcode scan logs to keep database clean';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $days = (int) $this->option('days');
        $cutoffDate = Carbon::now()->subDays($days);

        $this->info("Cleaning up barcode scan logs older than {$days} days...");
        $this->info("Cutoff date: {$cutoffDate->format('Y-m-d H:i:s')}");

        // Count logs to be deleted
        $count = BarcodeScanLog::where('scanned_at', '<', $cutoffDate)->count();

        if ($count === 0) {
            $this->info('No old logs found to cleanup.');
            return self::SUCCESS;
        }

        $this->warn("Found {$count} log(s) to delete.");

        if ($this->confirm('Do you want to proceed with deletion?', true)) {
            $deleted = BarcodeScanLog::where('scanned_at', '<', $cutoffDate)->delete();

            $this->info("Successfully deleted {$deleted} log(s).");
            $this->line('');
            $this->info('✓ Cleanup completed successfully!');

            return self::SUCCESS;
        }

        $this->warn('Cleanup cancelled.');
        return self::FAILURE;
    }
}
