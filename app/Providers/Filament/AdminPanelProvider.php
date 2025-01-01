<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Rupadana\ApiService\ApiServicePlugin;
use App\Filament\Pages\MutasiTabungan;
use App\Filament\Widgets\StatistikNasabahWidget;
use Rmsramos\Activitylog\ActivitylogPlugin;
use ShuvroRoy\FilamentSpatieLaravelHealth\FilamentSpatieLaravelHealthPlugin;
// use Mvenghaus\FilamentScheduleMonitor\FilamentPlugin;
use Filament\Navigation\NavigationItem;
use Joaopaulolndev\FilamentEditEnv\FilamentEditEnvPlugin;
use Illuminate\Support\Facades\Auth;


class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->authGuard('admin')
            ->authPasswordBroker('admin')
            ->brandName('Kospin Sinara Artha')
            ->brandLogo(asset('images/logo_kospin.png'))
            ->brandLogoHeight('4rem')
            ->darkModeBrandLogo(asset('images/logo_kospin.png'))
            ->login()
            ->colors([
                'primary' => Color::Amber,
            ])
            ->navigationGroups([
                'Data Nasabah',
                'Data Karyawan',
                'Tabungan',
                'Deposito',
                'Pinjaman',
                'Settings'
            ])
            ->navigationItems([
                NavigationItem::make('Whatsapp Gateway')
                    ->group('Settings')
                    ->icon('heroicon-o-chat-bubble-left-right')
                    ->url('http://46.102.156.214:3001/', shouldOpenInNewTab: true)
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
                // MutasiTabungan::class,

            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                //Widgets\AccountWidget::class,
                // Widgets\FilamentInfoWidget::class,
                StatistikNasabahWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->plugins([
                \BezhanSalleh\FilamentShield\FilamentShieldPlugin::make(),
                ApiServicePlugin::make(),
                \TomatoPHP\FilamentLogger\FilamentLoggerPlugin::make(),
                \TomatoPHP\FilamentArtisan\FilamentArtisanPlugin::make(),
                ActivitylogPlugin::make()
                    ->navigationIcon('heroicon-o-shield-check')
                    ->navigationCountBadge(true),
                FilamentEditEnvPlugin::make()
                    ->showButton(fn () => Auth::guard('admin')->user()?->id === 1)
                    ->setIcon('heroicon-o-cog'),
            ])
            ->plugin(FilamentSpatieLaravelHealthPlugin::make())
            ->plugin(\Mvenghaus\FilamentScheduleMonitor\FilamentPlugin::make());
            //->databaseNotifications();
    }
}
