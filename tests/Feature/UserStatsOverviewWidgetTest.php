<?php

namespace Tests\Feature;

use App\Filament\User\Widgets\UserStatsOverview;
use App\Models\Profile;
use App\Models\Tabungan;
use App\Models\TransaksiTabungan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class UserStatsOverviewWidgetTest extends TestCase
{
    use RefreshDatabase;

    public function test_widget_calculates_total_saldo_tabungan_correctly(): void
    {
        $user = User::factory()->create();
        $profile = Profile::factory()->create(['id_user' => $user->id]);
        
        $tabungan1 = Tabungan::factory()->create([
            'id_profile' => $profile->id,
            'saldo' => 1000000,
            'status_rekening' => 'aktif',
        ]);
        
        $tabungan2 = Tabungan::factory()->create([
            'id_profile' => $profile->id,
            'saldo' => 500000,
            'status_rekening' => 'aktif',
        ]);
        
        TransaksiTabungan::factory()->debit()->create([
            'id_tabungan' => $tabungan1->id,
            'jumlah' => 200000,
        ]);
        
        TransaksiTabungan::factory()->kredit()->create([
            'id_tabungan' => $tabungan1->id,
            'jumlah' => 100000,
        ]);
        
        TransaksiTabungan::factory()->debit()->create([
            'id_tabungan' => $tabungan2->id,
            'jumlah' => 300000,
        ]);
        
        $expectedTotal = 1000000 + 200000 - 100000 + 500000 + 300000;
        
        $this->actingAs($user);
        
        Livewire::test(UserStatsOverview::class)
            ->assertSee(format_rupiah($expectedTotal))
            ->assertSee('2 rekening tabungan');
    }
    
    public function test_widget_only_counts_active_tabungan(): void
    {
        $user = User::factory()->create();
        $profile = Profile::factory()->create(['id_user' => $user->id]);
        
        $tabunganAktif = Tabungan::factory()->create([
            'id_profile' => $profile->id,
            'saldo' => 1000000,
            'status_rekening' => 'aktif',
        ]);
        
        Tabungan::factory()->create([
            'id_profile' => $profile->id,
            'saldo' => 500000,
            'status_rekening' => 'tutup',
        ]);
        
        $this->actingAs($user);
        
        Livewire::test(UserStatsOverview::class)
            ->assertSee(format_rupiah(1000000))
            ->assertSee('1 rekening tabungan');
    }
    
    public function test_widget_handles_user_without_profile(): void
    {
        $user = User::factory()->create();
        
        $this->actingAs($user);
        
        Livewire::test(UserStatsOverview::class)
            ->assertSee('0')
            ->assertSee('Belum ada profile');
    }
}
