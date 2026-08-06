<?php

namespace App\Console\Commands;

use App\Services\KadaluarsaSetoranTidakDibayarService;
use Illuminate\Console\Command;

class KadaluarsaSetoranTidakDibayarCommand extends Command
{
    protected $signature = 'setorans:kadaluarsa-tidak-dibayar';

    protected $description = 'Processing expired QRIS deposits';

    public function handle(KadaluarsaSetoranTidakDibayarService $service): int
    {
        $count = $service->execute();
        $this->info("Successfully processed {$count} expired deposits.");

        return 0;
    }
}
