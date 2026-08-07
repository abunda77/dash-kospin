<?php

namespace Tests\Feature;

use App\Enums\StatusSetoran;
use App\Filament\User\Pages\SetoranSimpanan;
use App\Models\ProdukTabungan;
use App\Models\Profile;
use App\Models\SetoranTabungan;
use App\Models\Tabungan;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

class SetoranSimpananPageTest extends TestCase
{
    use DatabaseTransactions;

    public function test_user_dapat_membatalkan_setoran_dari_dashboard(): void
    {
        $user = User::factory()->create();
        Profile::factory()->create([
            'id_user' => $user->id,
            'is_active' => true,
        ]);

        $produk = ProdukTabungan::firstOrCreate(
            ['kode_produk' => 'PROD-TEST-PAGE-STR'],
            [
                'nama_produk' => 'Simpanan Test Page',
                'jenis_tabungan_id' => 1,
                'bunga_beaya_id' => 2,
            ]
        );

        $tabungan = Tabungan::create([
            'no_tabungan' => 'PAGE-STR-001',
            'id_profile' => DB::table('profiles')->where('id_user', $user->id)->value('id'),
            'produk_tabungan' => $produk->id,
            'saldo' => 100000,
            'tanggal_buka_rekening' => Carbon::now(),
            'status_rekening' => 'aktif',
        ]);

        $setoran = SetoranTabungan::create([
            'nomor_setoran' => 'STR-PAGE-CANCEL-1',
            'user_id' => $user->id,
            'id_tabungan' => $tabungan->id,
            'jenis_simpanan' => 'Simpanan Test Page',
            'jumlah' => 50000,
            'kode_unik' => 50,
            'jumlah_bayar' => 50050,
            'status' => StatusSetoran::MENUNGGU_PEMBAYARAN,
            'kedaluwarsa_at' => Carbon::now()->addMinutes(30),
        ]);

        Filament::setCurrentPanel(Filament::getPanel('user'));
        $this->actingAs($user);

        Livewire::test(SetoranSimpanan::class)
            ->assertSee('Batalkan Setoran')
            ->call('cancelSetoran', $setoran->id)
            ->assertNotified('Setoran Dibatalkan');

        $this->assertEquals(StatusSetoran::DIBATALKAN, $setoran->fresh()->status);
    }
}
