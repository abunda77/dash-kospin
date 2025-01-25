<?php

namespace App\Providers;

use Carbon\Carbon;
use Dedoc\Scramble\Scramble;
use Spatie\Health\Facades\Health;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Spatie\Activitylog\Models\Activity;
use Dedoc\Scramble\Support\Generator\OpenApi;
use Dedoc\Scramble\Support\Generator\SecurityScheme;
use Spatie\Health\Checks\Checks\DebugModeCheck;
use Spatie\Health\Checks\Checks\EnvironmentCheck;
use Spatie\Health\Checks\Checks\OptimizedAppCheck;

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
        // Configure Scramble for API documentation
        Scramble::afterOpenApiGenerated(function (OpenApi $openApi) {
            $openApi->secure(
                SecurityScheme::http('bearer')
            );
        });

        // if(request()->server('HTTP_CF_VISITOR') || request()->server('HTTPS')) {
        //     URL::forceScheme('https');
        // }

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
