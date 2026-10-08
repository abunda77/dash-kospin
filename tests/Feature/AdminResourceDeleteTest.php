<?php

namespace Tests\Feature;

use App\Models\Admin;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use ReflectionProperty;
use Tests\TestCase;

class AdminResourceDeleteTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * Simulasi request web agar hook boot trait Shield ikut terdaftar.
     * PHPUnit berjalan lewat CLI sehingga runningInConsole() bernilai true.
     */
    private function simulateWebRequest(): void
    {
        $property = new ReflectionProperty($this->app, 'isRunningInConsole');
        $property->setAccessible(true);
        $property->setValue($this->app, false);

        Admin::clearBootedModels();
    }

    protected function tearDown(): void
    {
        $property = new ReflectionProperty($this->app, 'isRunningInConsole');
        $property->setAccessible(true);
        $property->setValue($this->app, true);

        Admin::clearBootedModels();

        parent::tearDown();
    }

    public function test_admin_dapat_dihapus_tanpa_error_role_panel_user(): void
    {
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        $admin = Admin::create([
            'name' => 'Admin Delete Test',
            'email' => 'admin-delete-test@kospin.com',
            'password' => bcrypt('password'),
        ]);

        $this->simulateWebRequest();

        Admin::findOrFail($admin->id)->delete();

        $this->assertDatabaseMissing('admins', ['id' => $admin->id]);
    }

    public function test_admin_dapat_dibuat_pada_request_web_tanpa_error_role_panel_user(): void
    {
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        $this->simulateWebRequest();

        $admin = Admin::create([
            'name' => 'Admin Create Test',
            'email' => 'admin-create-test@kospin.com',
            'password' => bcrypt('password'),
        ]);

        $this->assertDatabaseHas('admins', ['id' => $admin->id]);
    }
}
