<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\SystemDiskChart;
use App\Filament\Widgets\SystemMemoryChart;
use App\Filament\Widgets\SystemModuleChart;
use App\Support\SystemInfo;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;

class InfoSystem extends Page
{
    use HasPageShield;

    protected static ?string $navigationIcon = 'heroicon-o-cpu-chip';

    protected static ?string $navigationLabel = 'Info System';

    protected static ?string $title = 'Info System';

    protected static ?string $slug = 'info-system';

    protected static ?int $navigationSort = 5;

    protected static string $view = 'filament.pages.info-system';

    public string $activeTab = 'overview';

    public bool $autoRefresh = false;

    private ?SystemInfo $systemInfo = null;

    public static function getNavigationGroup(): ?string
    {
        return 'Settings';
    }

    public function getSubheading(): string|Htmlable|null
    {
        return 'Kondisi server, konfigurasi PHP, modul yang aktif, dan paket Composer.';
    }

    /**
     * @return array<string, string>
     */
    public function tabs(): array
    {
        return [
            'overview' => 'Ringkasan',
            'os' => 'Sistem Operasi',
            'php' => 'PHP',
            'modules' => 'Modul PHP',
            'composer' => 'Composer',
            'infra' => 'Runtime & Infra',
        ];
    }

    public function setTab(string $tab): void
    {
        if (array_key_exists($tab, $this->tabs())) {
            $this->activeTab = $tab;
        }
    }

    public function refresh(): void
    {
        $this->systemInfo()->flush();

        Notification::make()
            ->title('Informasi sistem diperbarui')
            ->success()
            ->send();
    }

    public function systemInfo(): SystemInfo
    {
        return $this->systemInfo ??= app(SystemInfo::class);
    }

    /**
     * @return array<class-string>
     */
    protected function getHeaderWidgets(): array
    {
        if ($this->activeTab !== 'overview') {
            return [];
        }

        return [
            SystemMemoryChart::class,
            SystemDiskChart::class,
            SystemModuleChart::class,
        ];
    }

    /**
     * @return int|string|array<string, int|null>
     */
    public function getHeaderWidgetsColumns(): int|string|array
    {
        return 3;
    }
}
