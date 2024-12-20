<?php

namespace App\Jobs;

use App\Mail\ProfileEmail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendProfileEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $mailData;
    protected $userEmail;

    public function __construct($mailData, $userEmail)
    {
        $this->mailData = $mailData;
        $this->userEmail = $userEmail;
    }

    public function handle()
    {
        Mail::to($this->userEmail)->send(new ProfileEmail($this->mailData));
    }
}
