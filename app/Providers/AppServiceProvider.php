<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Spatie\Activitylog\Models\Activity;
use Spatie\Health\Facades\Health;
use Spatie\Health\Checks\Checks\OptimizedAppCheck;
use Spatie\Health\Checks\Checks\DebugModeCheck;
use Spatie\Health\Checks\Checks\EnvironmentCheck;
use Carbon\Carbon;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {

    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Filament Logger
        Gate::policy(\TomatoPHP\FilamentLogger\Models\Activity::class, \App\Policies\ActivityPolicy::class);
        Gate::policy(\TomatoPHP\FilamentLogger\Filament\Resources\ActivityResource::class, \App\Policies\ActivityPolicy::class);


        // Filament Artisan
        Gate::policy(\TomatoPHP\FilamentArtisan\Pages\Artisan::class, \App\Policies\ArtisanPolicy::class);
        // Activity Log
        Gate::policy(Activity::class, \App\Policies\ActivityLogPolicy::class);

        Health::checks([
            OptimizedAppCheck::new(),
            DebugModeCheck::new(),
            EnvironmentCheck::new(),
        ]);

        // Set locale ke Indonesia
        Carbon::setLocale('id');
        
        // Opsional: Set fallback locale jika terjemahan tidak tersedia
        Carbon::setFallbackLocale('id');
    }
}
