<?php

namespace App\Helpers;

use Filament\Notifications\Notification;

class SafeNotificationHelper
{
    /**
     * Create a safe UTF-8 notification
     */
    public static function success(string $title, string $body): void
    {
        // Ensure strings are UTF-8 encoded and clean
        $safeTitle = mb_convert_encoding($title, 'UTF-8', 'auto');
        $safeBody = mb_convert_encoding($body, 'UTF-8', 'auto');
        
        // Remove any non-printable characters except basic formatting
        $safeTitle = preg_replace('/[^\p{L}\p{N}\p{P}\p{Z}]/u', '', $safeTitle);
        $safeBody = preg_replace('/[^\p{L}\p{N}\p{P}\p{Z}]/u', '', $safeBody);
        
        Notification::make()
            ->title($safeTitle)
            ->body($safeBody)
            ->success()
            ->send();
    }
    
    /**
     * Create a safe UTF-8 error notification
     */
    public static function error(string $title, string $body): void
    {
        // Ensure strings are UTF-8 encoded and clean
        $safeTitle = mb_convert_encoding($title, 'UTF-8', 'auto');
        $safeBody = mb_convert_encoding($body, 'UTF-8', 'auto');
        
        // Remove any non-printable characters except basic formatting
        $safeTitle = preg_replace('/[^\p{L}\p{N}\p{P}\p{Z}]/u', '', $safeTitle);
        $safeBody = preg_replace('/[^\p{L}\p{N}\p{P}\p{Z}]/u', '', $safeBody);
        
        Notification::make()
            ->title($safeTitle)
            ->body($safeBody)
            ->danger()
            ->send();
    }
}
