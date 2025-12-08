<?php

namespace App\Filament\User\Widgets;

use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class WelcomeWidget extends Widget
{
    protected static ?int $sort = 0;
    
    protected int | string | array $columnSpan = 'full';

    protected static string $view = 'filament.user.widgets.welcome-widget';

    public function getGreeting(): string
    {
        $hour = Carbon::now()->hour;
        
        if ($hour >= 5 && $hour < 12) {
            return 'Selamat Pagi';
        } elseif ($hour >= 12 && $hour < 15) {
            return 'Selamat Siang';
        } elseif ($hour >= 15 && $hour < 18) {
            return 'Selamat Sore';
        } else {
            return 'Selamat Malam';
        }
    }

    public function getUserName(): string
    {
        $profile = Auth::user()->profile;
        
        if ($profile && $profile->first_name) {
            return $profile->first_name . ($profile->last_name ? ' ' . $profile->last_name : '');
        }
        
        return Auth::user()->name;
    }

    public function getProfileStatus(): array
    {
        $profile = Auth::user()->profile;
        
        if (!$profile) {
            return [
                'complete' => false,
                'message' => 'Lengkapi profile Anda untuk mengakses semua fitur.',
                'color' => 'warning',
            ];
        }

        $requiredFields = ['first_name', 'phone', 'address', 'no_identity'];
        $filledFields = 0;
        
        foreach ($requiredFields as $field) {
            if (!empty($profile->$field)) {
                $filledFields++;
            }
        }

        $percentage = ($filledFields / count($requiredFields)) * 100;

        if ($percentage < 100) {
            return [
                'complete' => false,
                'percentage' => $percentage,
                'message' => 'Profile Anda ' . round($percentage) . '% lengkap.',
                'color' => 'warning',
            ];
        }

        return [
            'complete' => true,
            'message' => 'Profile Anda sudah lengkap!',
            'color' => 'success',
        ];
    }

    public function getCurrentDate(): string
    {
        return Carbon::now()->translatedFormat('l, d F Y');
    }
}
