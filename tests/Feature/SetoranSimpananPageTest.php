<?php

namespace Tests\Feature;

use App\Enums\MetodePembayaranSetoran;
use App\Enums\StatusSetoran;
use App\Filament\User\Pages\SetoranSimpanan;
use App\Jobs\SendQRISClaimNotificationJob;
use App\Models\ProdukTabungan;
use App\Models\Profile;
use App\Models\SetoranTabungan;
use App\Models\Tabungan;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class SetoranSimpananPageTest extends TestCase
{
    use DatabaseTransactions;

    public function test_user_dapat_memilih_transfer_rekening_dan_mengirim_konfirmasi(): void
    {
        Queue::fake();
        Storage::fake('private');

        $user = User::factory()->create();
        Profile::factory()->create([
            'id_user' => $user->id,
            'is_active' => true,
        ]);

        $produk = ProdukTabungan::firstOrCreate(
            ['kode_produk' => 'PROD-TEST-PAGE-TRANSFER'],
            [
                'nama_produk' => 'Simpanan Transfer',
                'jenis_tabungan_id' => 1,
                'bunga_beaya_id' => 2,
            ]
        );

        $tabungan = Tabungan::create([
            'no_tabungan' => 'PAGE-TRANSFER-001',
            'id_profile' => DB::table('profiles')->where('id_user', $user->id)->value('id'),
            'produk_tabungan' => $produk->id,
            'saldo' => 100000,
            'tanggal_buka_rekening' => Carbon::now(),
            'status_rekening' => 'aktif',
        ]);

        Filament::setCurrentPanel(Filament::getPanel('user'));
        $this->actingAs($user);

        Livewire::test(SetoranSimpanan::class)
            ->assertSee('Transfer Rekening')
            ->fillForm([
                'id_tabungan' => $tabungan->id,
                'preset_jumlah' => '50000',
                'metode_pembayaran' => MetodePembayaranSetoran::TransferRekening->value,
            ], 'generateForm')
            ->call('createSetoran')
            ->assertNotified('Sukses')
            ->assertSee('0889333288')
            ->assertSee('KOPERASI SINARA ARTHA');

        $setoran = SetoranTabungan::query()
            ->where('user_id', $user->id)
            ->latest('id')
            ->firstOrFail();

        $this->assertSame(MetodePembayaranSetoran::TransferRekening, $setoran->metode_pembayaran);
        $this->assertNull($setoran->qris_payload);
        $this->assertNull($setoran->qris_image_path);

        Livewire::test(SetoranSimpanan::class)
            ->fillForm([
                'waktu_klaim_bayar' => now()->format('Y-m-d H:i:s'),
                'nama_pembayar' => 'Pengirim Transfer',
                'referensi_pembayaran' => 'TRF-BCA-001',
            ], 'claimForm')
            ->call('claimPayment', $setoran->id)
            ->assertNotified('Sukses');

        $this->assertSame(StatusSetoran::MENUNGGU_VERIFIKASI, $setoran->fresh()->status);
        Queue::assertPushed(SendQRISClaimNotificationJob::class);
    }

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
