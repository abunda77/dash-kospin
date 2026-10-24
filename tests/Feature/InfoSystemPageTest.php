<?php

namespace Tests\Feature;

use App\Filament\Pages\InfoSystem;
use App\Filament\Widgets\SystemDiskChart;
use App\Filament\Widgets\SystemMemoryChart;
use App\Filament\Widgets\SystemModuleChart;
use App\Models\Admin;
use App\Support\SystemInfo;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use Tests\TestCase;

class InfoSystemPageTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel(Filament::getPanel('admin'));
    }

    public function test_probe_sistem_mengembalikan_data_utama(): void
    {
        $systemInfo = app(SystemInfo::class);

        $this->assertNotEmpty($systemInfo->overviewCards());
        $this->assertNotEmpty($systemInfo->osInfo());
        $this->assertNotEmpty($systemInfo->phpInfo());
        $this->assertNotEmpty($systemInfo->phpModules());
        $this->assertIsArray($systemInfo->unloadedExtensions());
        $this->assertNotEmpty($systemInfo->infraGroups());

        $composer = $systemInfo->composerPackages();
        $this->assertNotEmpty($composer['packages']);
        $this->assertSame(count($composer['packages']), $composer['counts']['total']);
        $this->assertSame(
            $composer['counts']['production'] + $composer['counts']['development'],
            $composer['counts']['total'],
        );
    }

    public function test_modul_php_memiliki_kategori_yang_dikenal(): void
    {
        $systemInfo = app(SystemInfo::class);
        $categories = ['Core', 'Zend', 'Ekstensi'];

        foreach ($systemInfo->phpModules() as $module) {
            $this->assertArrayHasKey('name', $module);
            $this->assertArrayHasKey('version', $module);
            $this->assertContains($module['category'], $categories);
        }

        $counts = $systemInfo->moduleCountsByCategory();
        $this->assertSame(count($systemInfo->phpModules()), array_sum($counts));
    }

    public function test_admin_tanpa_izin_tidak_dapat_mengakses_halaman(): void
    {
        $admin = Admin::create([
            'name' => 'Admin Tanpa Izin',
            'email' => 'admin-tanpa-izin-'.uniqid().'@kospin.com',
            'password' => bcrypt('password'),
        ]);

        $this->actingAs($admin, 'admin');

        $this->assertFalse(InfoSystem::canAccess());
    }

    public function test_halaman_info_system_dapat_dirender(): void
    {
        $this->actingAs($this->superAdmin(), 'admin');

        Livewire::test(InfoSystem::class)
            ->assertSuccessful()
            ->assertSee('Info System')
            ->assertSee('Ringkasan')
            ->assertSee('Sistem Operasi');
    }

    public function test_tab_dapat_berpindah_dan_menampilkan_konten(): void
    {
        $this->actingAs($this->superAdmin(), 'admin');

        Livewire::test(InfoSystem::class)
            ->call('setTab', 'modules')
            ->assertSet('activeTab', 'modules')
            ->assertSee('Modul PHP Aktif')
            ->call('setTab', 'composer')
            ->assertSet('activeTab', 'composer')
            ->assertSee('Paket Terpasang')
            ->call('setTab', 'infra')
            ->assertSet('activeTab', 'infra')
            ->assertSee('Runtime Aplikasi');
    }

    public function test_tab_tidak_dikenal_diabaikan(): void
    {
        $this->actingAs($this->superAdmin(), 'admin');

        Livewire::test(InfoSystem::class)
            ->call('setTab', 'tidak-ada')
            ->assertSet('activeTab', 'overview');
    }

    public function test_refresh_mengirim_notifikasi(): void
    {
        $this->actingAs($this->superAdmin(), 'admin');

        Livewire::test(InfoSystem::class)
            ->call('refresh')
            ->assertNotified();
    }

    public function test_widget_grafik_dapat_dirender(): void
    {
        $this->actingAs($this->superAdmin(), 'admin');

        Livewire::test(SystemMemoryChart::class)->assertSuccessful();
        Livewire::test(SystemDiskChart::class)->assertSuccessful();
        Livewire::test(SystemModuleChart::class)
            ->assertSuccessful()
            ->assertSee('Modul PHP per Kategori');
    }

    private function superAdmin(): Admin
    {
        $admin = Admin::create([
            'name' => 'Admin Info System Test',
            'email' => 'admin-info-system-'.uniqid().'@kospin.com',
            'password' => bcrypt('password'),
        ]);

        $admin->assignRole('super_admin');

        $permissionClass = config('permission.models.permission');
        $admin->givePermissionTo($permissionClass::findOrCreate('page_InfoSystem', 'admin'));

        return $admin;
    }
}
