<?php

namespace App\Jobs;

use App\Mail\ProfileEmail;
use App\Models\Profile;
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
    protected $profileId;

    public function __construct($mailData, $userEmail, $profileId)
    {
        $this->mailData = $mailData;
        $this->userEmail = $userEmail;
        $this->profileId = $profileId;
    }

    public function handle()
    {
        $profile = Profile::find($this->profileId);

        if ($profile) {
            // Mengganti variabel placeholder dengan data profil
            $this->mailData['subject'] = str_replace(
                ['{first_name}', '{last_name}'],
                [$profile->first_name, $profile->last_name],
                $this->mailData['subject']
            );

            $this->mailData['message'] = str_replace(
                ['{first_name}', '{last_name}'],
                [$profile->first_name, $profile->last_name],
                $this->mailData['message']
            );
        }

        Mail::to($this->userEmail)->send(new ProfileEmail($this->mailData));
    }
}
