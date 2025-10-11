<?php

namespace App\Console\Commands;

use App\Models\MakanBergizisGratis;
use Illuminate\Console\Command;

class CleanupMakanBergizisGratisCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mbg:cleanup {--days=90 : Number of days to keep}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cleanup old Makan Bergizi Gratis records';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $days = (int) $this->option('days');
        
        $this->info("Cleaning up Makan Bergizi Gratis records older than {$days} days...");
        
        $cutoffDate = now()->subDays($days);
        
        $count = MakanBergizisGratis::where('tanggal_pemberian', '<', $cutoffDate)->count();
        
        if ($count === 0) {
            $this->info('No records to cleanup.');
            return Command::SUCCESS;
        }
        
        if ($this->confirm("Found {$count} records to delete. Continue?", true)) {
            $deleted = MakanBergizisGratis::where('tanggal_pemberian', '<', $cutoffDate)->delete();
            
            $this->info("Successfully deleted {$deleted} records.");
            
            return Command::SUCCESS;
        }
        
        $this->info('Cleanup cancelled.');
        return Command::SUCCESS;
    }
}
