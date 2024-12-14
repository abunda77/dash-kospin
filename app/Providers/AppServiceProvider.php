<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Spatie\Activitylog\Models\Activity;

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
    }
}
