<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Dashboard;
use App\Filament\Widgets\Birthday;
use App\Filament\Widgets\CriticalOverdueWidget;
use App\Filament\Widgets\StatistikNasabahWidget;
use App\Filament\Widgets\VerifikasiSimpananWidget;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationItem;
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
use Illuminate\Support\Facades\Auth;
use Illuminate\View\Middleware\ShareErrorsFromSession;
// use Mvenghaus\FilamentScheduleMonitor\FilamentPlugin;
use Joaopaulolndev\FilamentEditEnv\FilamentEditEnvPlugin;
use Mvenghaus\FilamentScheduleMonitor\FilamentPlugin;
use Rmsramos\Activitylog\ActivitylogPlugin;
use Rupadana\ApiService\ApiServicePlugin;
use ShuvroRoy\FilamentSpatieLaravelHealth\FilamentSpatieLaravelHealthPlugin;
use TomatoPHP\FilamentArtisan\FilamentArtisanPlugin;
use TomatoPHP\FilamentLogger\FilamentLoggerPlugin;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->authGuard('admin')
            ->authPasswordBroker('admins')
            ->brandName('Kospin Sinara Artha')
            ->brandLogo(asset('images/logo_kospin.png'))
            ->brandLogoHeight('4rem')
            ->darkModeBrandLogo(asset('images/logo_kospin.png'))
            ->login()
            ->passwordReset()
            ->colors([
                'primary' => Color::Green,
            ])
            ->navigationGroups([
                'Data Nasabah',
                'Data Karyawan',
                'Tabungan',
                'Deposito',
                'Pinjaman',
                'Laporan',
                'Settings',
            ])
            ->navigationItems([
                NavigationItem::make('Whatsapp Gateway')
                    ->group('Settings')
                    ->icon('heroicon-o-chat-bubble-left-right')
                    ->url('http://admin:sinara123@46.102.156.214:3003/', shouldOpenInNewTab: true),
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Dashboard::class,
                // MutasiTabungan::class,

            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                // Widgets\AccountWidget::class,
                // Widgets\FilamentInfoWidget::class,
                VerifikasiSimpananWidget::class,
                StatistikNasabahWidget::class,
                CriticalOverdueWidget::class,
                Birthday::class,
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
                FilamentShieldPlugin::make(),
                ApiServicePlugin::make(),
                FilamentLoggerPlugin::make(),
                FilamentArtisanPlugin::make(),
                ActivitylogPlugin::make()
                    ->navigationIcon('heroicon-o-shield-check')
                    ->navigationCountBadge(true),
                FilamentEditEnvPlugin::make()
                    ->showButton(fn () => Auth::guard('admin')->user()?->id === 1)
                    ->setIcon('heroicon-o-cog'),
            ])
            ->plugin(FilamentSpatieLaravelHealthPlugin::make())
            ->plugin(FilamentPlugin::make())
            ->databaseNotifications();
    }
}
