<?php

namespace Tests\Feature;

use App\Models\Admin;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Rmsramos\Activitylog\Resources\ActivitylogResource\Pages\ViewActivitylog;
use Tests\TestCase;

class ActivitylogViewPageTest extends TestCase
{
    use DatabaseTransactions;

    public function test_halaman_view_activitylog_dapat_dirender(): void
    {
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        $admin = Admin::create([
            'name' => 'Admin Activitylog Test',
            'email' => 'admin-activitylog@kospin.com',
            'password' => bcrypt('password'),
        ]);

        $admin->assignRole('super_admin');

        $this->actingAs($admin, 'admin');

        $activityLogId = DB::table('activity_log')->orderBy('id')->value('id');

        if ($activityLogId === null) {
            $this->markTestSkipped('Tidak ada data activity_log untuk diuji.');
        }

        Livewire::test(ViewActivitylog::class, ['record' => $activityLogId])
            ->assertSuccessful();
    }
}
