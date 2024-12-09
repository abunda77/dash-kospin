<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Contracts\ActivityLogger;
use App\Services\ActivityLogService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(ActivityLogger::class, ActivityLogService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Hapus kode schedule dari sini karena sudah dipindah ke routes/console.php
    }
}
