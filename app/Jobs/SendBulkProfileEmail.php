<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class SendBulkProfileEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        protected array $mailData,
        protected Collection $profiles
    ) {}

    public function handle(): void
    {
        foreach ($this->profiles as $profile) {
            SendProfileEmail::dispatch(
                $this->mailData,
                $profile->email,
                $profile->id_user
            );
        }
    }
}
