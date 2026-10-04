<?php

namespace App\Providers;

use App\Models\Deposito;
use App\Models\PenarikanTabungan;
use App\Models\Pinjaman;
use App\Models\SetoranTabungan;
use App\Models\Tabungan;
use App\Models\TransaksiPinjaman;
use App\Models\TransaksiTabungan;
use App\Observers\DepositoObserver;
use App\Observers\PinjamanObserver;
use App\Observers\TabunganObserver;
use App\Observers\TransaksiPinjamanObserver;
use App\Observers\TransaksiTabunganObserver;
use App\Policies\ActivityLogPolicy;
use App\Policies\ActivityPolicy;
use App\Policies\ArtisanPolicy;
use App\Policies\PenarikanTabunganPolicy;
use App\Policies\SetoranTabunganPolicy;
use Carbon\Carbon;
use Dedoc\Scramble\Scramble;
use Dedoc\Scramble\Support\Generator\OpenApi;
use Dedoc\Scramble\Support\Generator\SecurityScheme;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Spatie\Activitylog\Models\Activity;
use Spatie\Health\Checks\Checks\DebugModeCheck;
use Spatie\Health\Checks\Checks\EnvironmentCheck;
use Spatie\Health\Checks\Checks\OptimizedAppCheck;
use Spatie\Health\Facades\Health;
use TomatoPHP\FilamentArtisan\Pages\Artisan;
use TomatoPHP\FilamentLogger\Filament\Resources\ActivityResource;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureRateLimiters();

        // Configure Scramble for API documentation
        Scramble::afterOpenApiGenerated(function (OpenApi $openApi) {
            $openApi->secure(
                SecurityScheme::http('bearer')
            );
        });

        // Mengatur akses ke dokumentasi API
        Gate::define('viewApiDocs', function () {
            // Selalu minta password dari env
            return request()->hasHeader('PHP_AUTH_PW') &&
                   request()->header('PHP_AUTH_PW') === env('SCRAMBLE_DOCS_PASSWORD');
        });

        // if(request()->server('HTTP_CF_VISITOR') || request()->server('HTTPS')) {
        //     URL::forceScheme('https');
        // }

        // Filament Logger
        Gate::policy(\TomatoPHP\FilamentLogger\Models\Activity::class, ActivityPolicy::class);
        Gate::policy(ActivityResource::class, ActivityPolicy::class);

        // Filament Artisan
        Gate::policy(Artisan::class, ArtisanPolicy::class);
        // Activity Log
        Gate::policy(Activity::class, ActivityLogPolicy::class);
        Gate::policy(SetoranTabungan::class, SetoranTabunganPolicy::class);
        Gate::policy(PenarikanTabungan::class, PenarikanTabunganPolicy::class);

        Health::checks([
            OptimizedAppCheck::new(),
            DebugModeCheck::new(),
            EnvironmentCheck::new(),
        ]);

        // Register Observers
        Pinjaman::observe(PinjamanObserver::class);
        TransaksiPinjaman::observe(TransaksiPinjamanObserver::class);
        Deposito::observe(DepositoObserver::class);
        Tabungan::observe(TabunganObserver::class);
        TransaksiTabungan::observe(TransaksiTabunganObserver::class);

        // Set locale ke Indonesia
        Carbon::setLocale('id');

        // Opsional: Set fallback locale jika terjemahan tidak tersedia
        Carbon::setFallbackLocale('id');
    }

    /**
     * Limiter untuk endpoint autentikasi API.
     */
    protected function configureRateLimiters(): void
    {
        RateLimiter::for('api-register', fn (Request $request) => Limit::perMinute(5)->by($request->ip()));

        RateLimiter::for('api-forgot-password', fn (Request $request) => [
            Limit::perMinute(3)->by('email:'.(string) $request->input('email')),
            Limit::perMinute(5)->by('ip:'.$request->ip()),
        ]);

        RateLimiter::for('api-reset-password', fn (Request $request) => Limit::perMinute(5)->by($request->ip()));
    }
}
