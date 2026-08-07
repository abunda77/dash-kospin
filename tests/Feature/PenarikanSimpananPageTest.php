<?php

namespace Tests\Feature;

use App\Enums\StatusPenarikan;
use App\Filament\User\Pages\PenarikanSimpanan;
use App\Models\PenarikanTabungan;
use App\Models\ProdukTabungan;
use App\Models\Profile;
use App\Models\Tabungan;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

class PenarikanSimpananPageTest extends TestCase
{
    use DatabaseTransactions;

    public function test_user_dapat_membatalkan_penarikan_dari_dashboard(): void
    {
        $user = User::factory()->create();
        Profile::factory()->create([
            'id_user' => $user->id,
            'is_active' => true,
        ]);

        $produk = ProdukTabungan::firstOrCreate(
            ['kode_produk' => 'PROD-TEST-PAGE-PNK'],
            [
                'nama_produk' => 'Simpanan Test Page',
                'jenis_tabungan_id' => 1,
                'bunga_beaya_id' => 2,
            ]
        );

        $tabungan = Tabungan::create([
            'no_tabungan' => 'PAGE-PNK-001',
            'id_profile' => DB::table('profiles')->where('id_user', $user->id)->value('id'),
            'produk_tabungan' => $produk->id,
            'saldo' => 500000,
            'tanggal_buka_rekening' => Carbon::now(),
            'status_rekening' => 'aktif',
        ]);

        $penarikan = PenarikanTabungan::create([
            'nomor_penarikan' => 'PNK-PAGE-CANCEL-1',
            'user_id' => $user->id,
            'id_tabungan' => $tabungan->id,
            'jenis_simpanan' => 'Simpanan Test Page',
            'jumlah' => 50000,
            'bank' => 'BRI',
            'nama_bank' => 'BRI Unit Kota',
            'nama_nasabah' => 'Nasabah Uji',
            'status' => StatusPenarikan::MENUNGGU_VERIFIKASI,
            'dikirim_at' => Carbon::now(),
        ]);

        Filament::setCurrentPanel(Filament::getPanel('user'));
        $this->actingAs($user);

        Livewire::test(PenarikanSimpanan::class)
            ->assertSee('Batalkan Penarikan')
            ->call('cancelPenarikan', $penarikan->id)
            ->assertNotified('Penarikan Dibatalkan');

        $this->assertEquals(StatusPenarikan::DIBATALKAN, $penarikan->fresh()->status);
    }
}
