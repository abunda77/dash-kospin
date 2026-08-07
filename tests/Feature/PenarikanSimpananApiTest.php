<?php

namespace Tests\Feature;

use App\Enums\StatusPenarikan;
use App\Models\PenarikanTabungan;
use App\Models\ProdukTabungan;
use App\Models\Profile;
use App\Models\Tabungan;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PenarikanSimpananApiTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        Http::fake();
    }

    private function getOrCreateProduct(): ProdukTabungan
    {
        $produk = ProdukTabungan::first();
        if (! $produk) {
            $produk = ProdukTabungan::create([
                'kode_produk' => 'PROD-TEST-API-PNK',
                'nama_produk' => 'Simpanan Test API',
                'jenis_tabungan_id' => 1,
                'bunga_beaya_id' => 2,
            ]);
        }

        return $produk;
    }

    private function buatUserDenganTabungan(float $saldo, string $noTabungan): array
    {
        $user = User::factory()->create();

        Profile::factory()->create([
            'id_user' => $user->id,
            'is_active' => true,
        ]);

        $profileId = DB::table('profiles')->where('id_user', $user->id)->value('id');
        $produk = $this->getOrCreateProduct();

        $tabungan = Tabungan::create([
            'no_tabungan' => $noTabungan,
            'id_profile' => $profileId,
            'produk_tabungan' => $produk->id,
            'saldo' => $saldo,
            'tanggal_buka_rekening' => Carbon::now(),
            'status_rekening' => 'aktif',
        ]);

        return [$user, $tabungan];
    }

    public function test_rekening_options_mengembalikan_tabungan_aktif_milik_user(): void
    {
        [$user, $tabungan] = $this->buatUserDenganTabungan(500000.00, 'API-PNK-001');

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/penarikan/rekening-options');

        $response->assertStatus(200)
            ->assertJsonPath('status', true)
            ->assertJsonPath('data.0.id', $tabungan->id)
            ->assertJsonPath('data.0.no_tabungan', 'API-PNK-001');
    }

    public function test_ajukan_penarikan_berhasil(): void
    {
        Queue::fake();
        Storage::fake('private');

        [$user, $tabungan] = $this->buatUserDenganTabungan(500000.00, 'API-PNK-002');

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/penarikan', [
            'id_tabungan' => $tabungan->id,
            'jumlah' => 100000,
            'bank' => 'BRI',
            'nama_bank' => 'BRI Unit Kota',
            'nama_nasabah' => 'Nasabah Uji',
            'referensi_penarikan' => 'REF-API-123',
            'catatan_pengguna' => 'Catatan API',
            'bukti_penarikan' => UploadedFile::fake()->image('bukti.png'),
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('status', true)
            ->assertJsonPath('data.jumlah', 100000)
            ->assertJsonPath('data.status', StatusPenarikan::MENUNGGU_VERIFIKASI->value);

        $this->assertDatabaseHas('penarikan_tabungans', [
            'id' => $response->json('data.id'),
            'user_id' => $user->id,
            'id_tabungan' => $tabungan->id,
            'jumlah' => 100000,
            'bank' => 'BRI',
        ]);
    }

    public function test_ajukan_penarikan_gagal_validasi_nominal(): void
    {
        [$user, $tabungan] = $this->buatUserDenganTabungan(500000.00, 'API-PNK-003');

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/penarikan', [
            'id_tabungan' => $tabungan->id,
            'jumlah' => 5000,
            'bank' => 'BRI',
            'nama_bank' => 'BRI Unit Kota',
            'nama_nasabah' => 'Nasabah Uji',
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('status', false)
            ->assertJsonValidationErrors(['jumlah']);
    }

    public function test_ajukan_penarikan_gagal_karena_saldo_kurang(): void
    {
        [$user, $tabungan] = $this->buatUserDenganTabungan(50000.00, 'API-PNK-004');

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/penarikan', [
            'id_tabungan' => $tabungan->id,
            'jumlah' => 100000,
            'bank' => 'BRI',
            'nama_bank' => 'BRI Unit Kota',
            'nama_nasabah' => 'Nasabah Uji',
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('status', false);
    }

    public function test_ajukan_penarikan_gagal_untuk_rekening_orang_lain(): void
    {
        [, $tabunganOrangLain] = $this->buatUserDenganTabungan(500000.00, 'API-PNK-005');
        [$user] = $this->buatUserDenganTabungan(100000.00, 'API-PNK-006');

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/penarikan', [
            'id_tabungan' => $tabunganOrangLain->id,
            'jumlah' => 50000,
            'bank' => 'BRI',
            'nama_bank' => 'BRI Unit Kota',
            'nama_nasabah' => 'Nasabah Uji',
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('status', false);
    }

    public function test_penarikan_aktif_dan_history(): void
    {
        [$user, $tabungan] = $this->buatUserDenganTabungan(500000.00, 'API-PNK-007');

        $penarikan = PenarikanTabungan::create([
            'nomor_penarikan' => 'PNK-API-ACTIVE-1',
            'user_id' => $user->id,
            'id_tabungan' => $tabungan->id,
            'jenis_simpanan' => 'Simpanan Test API',
            'jumlah' => 50000,
            'bank' => 'BRI',
            'nama_bank' => 'BRI Unit Kota',
            'nama_nasabah' => 'Nasabah Uji',
            'status' => StatusPenarikan::MENUNGGU_VERIFIKASI,
            'dikirim_at' => Carbon::now(),
        ]);

        PenarikanTabungan::create([
            'nomor_penarikan' => 'PNK-API-HISTORY-1',
            'user_id' => $user->id,
            'id_tabungan' => $tabungan->id,
            'jenis_simpanan' => 'Simpanan Test API',
            'jumlah' => 25000,
            'bank' => 'BCA',
            'nama_bank' => 'BCA KCU Sudirman',
            'nama_nasabah' => 'Nasabah Uji',
            'status' => StatusPenarikan::SELESAI,
            'dikirim_at' => Carbon::now()->subDay(),
        ]);

        Sanctum::actingAs($user);

        $aktif = $this->getJson('/api/penarikan/aktif');
        $aktif->assertStatus(200)
            ->assertJsonPath('status', true)
            ->assertJsonPath('data.id', $penarikan->id);

        $history = $this->getJson('/api/penarikan/history');
        $history->assertStatus(200)
            ->assertJsonPath('status', true)
            ->assertJsonCount(2, 'data');
    }

    public function test_show_penarikan_tidak_menemukan_data_orang_lain(): void
    {
        [$pemilik, $tabungan] = $this->buatUserDenganTabungan(500000.00, 'API-PNK-008');
        [$userLain] = $this->buatUserDenganTabungan(100000.00, 'API-PNK-009');

        $penarikan = PenarikanTabungan::create([
            'nomor_penarikan' => 'PNK-API-OWNER-1',
            'user_id' => $pemilik->id,
            'id_tabungan' => $tabungan->id,
            'jenis_simpanan' => 'Simpanan Test API',
            'jumlah' => 50000,
            'bank' => 'BRI',
            'nama_bank' => 'BRI Unit Kota',
            'nama_nasabah' => 'Nasabah Uji',
            'status' => StatusPenarikan::SELESAI,
            'dikirim_at' => Carbon::now(),
        ]);

        Sanctum::actingAs($userLain);

        $this->getJson("/api/penarikan/{$penarikan->id}")
            ->assertStatus(404)
            ->assertJsonPath('status', false);
    }

    public function test_kirim_revisi_penarikan_berhasil(): void
    {
        Storage::fake('private');

        [$user, $tabungan] = $this->buatUserDenganTabungan(500000.00, 'API-PNK-010');

        $penarikan = PenarikanTabungan::create([
            'nomor_penarikan' => 'PNK-API-REVISI-1',
            'user_id' => $user->id,
            'id_tabungan' => $tabungan->id,
            'jenis_simpanan' => 'Simpanan Test API',
            'jumlah' => 50000,
            'bank' => 'BRI',
            'nama_bank' => 'BRI Unit Kota',
            'nama_nasabah' => 'Nasabah Uji',
            'status' => StatusPenarikan::PERLU_REVISI,
            'dikirim_at' => Carbon::now(),
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson("/api/penarikan/{$penarikan->id}/revisi", [
            'referensi_penarikan' => 'REF-REVISI-1',
            'catatan_pengguna' => 'Revisi API',
            'bukti_penarikan' => UploadedFile::fake()->image('bukti-revisi.png'),
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('status', true)
            ->assertJsonPath('data.status', StatusPenarikan::MENUNGGU_VERIFIKASI->value);
    }

    public function test_kirim_revisi_ditolak_ketika_status_bukan_perlu_revisi(): void
    {
        [$user, $tabungan] = $this->buatUserDenganTabungan(500000.00, 'API-PNK-011');

        $penarikan = PenarikanTabungan::create([
            'nomor_penarikan' => 'PNK-API-REVISI-2',
            'user_id' => $user->id,
            'id_tabungan' => $tabungan->id,
            'jenis_simpanan' => 'Simpanan Test API',
            'jumlah' => 50000,
            'bank' => 'BRI',
            'nama_bank' => 'BRI Unit Kota',
            'nama_nasabah' => 'Nasabah Uji',
            'status' => StatusPenarikan::MENUNGGU_VERIFIKASI,
            'dikirim_at' => Carbon::now(),
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson("/api/penarikan/{$penarikan->id}/revisi", [
            'catatan_pengguna' => 'Revisi API',
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('status', false);
    }

    public function test_pemilik_dapat_membatalkan_penarikan_yang_menunggu_verifikasi(): void
    {
        [$user, $tabungan] = $this->buatUserDenganTabungan(500000.00, 'API-PNK-012');

        $penarikan = PenarikanTabungan::create([
            'nomor_penarikan' => 'PNK-API-CANCEL-1',
            'user_id' => $user->id,
            'id_tabungan' => $tabungan->id,
            'jenis_simpanan' => 'Simpanan Test API',
            'jumlah' => 50000,
            'bank' => 'BRI',
            'nama_bank' => 'BRI Unit Kota',
            'nama_nasabah' => 'Nasabah Uji',
            'status' => StatusPenarikan::MENUNGGU_VERIFIKASI,
            'dikirim_at' => Carbon::now(),
        ]);

        Sanctum::actingAs($user);

        $this->postJson("/api/penarikan/{$penarikan->id}/batalkan")
            ->assertOk()
            ->assertJsonPath('status', true)
            ->assertJsonPath('data.status', StatusPenarikan::DIBATALKAN->value);

        $this->assertEquals(StatusPenarikan::DIBATALKAN, $penarikan->fresh()->status);
        $this->assertDatabaseHas('riwayat_status_penarikans', [
            'penarikan_id' => $penarikan->id,
            'status_sebelumnya' => StatusPenarikan::MENUNGGU_VERIFIKASI->value,
            'status_baru' => StatusPenarikan::DIBATALKAN->value,
            'diubah_oleh_type' => User::class,
            'diubah_oleh_id' => $user->id,
        ]);

        $this->getJson('/api/penarikan/aktif')
            ->assertOk()
            ->assertJsonPath('data', null);
    }

    public function test_penarikan_orang_lain_tidak_dapat_dibatalkan(): void
    {
        [$pemilik, $tabungan] = $this->buatUserDenganTabungan(500000.00, 'API-PNK-013');
        [$userLain] = $this->buatUserDenganTabungan(500000.00, 'API-PNK-014');

        $penarikan = PenarikanTabungan::create([
            'nomor_penarikan' => 'PNK-API-CANCEL-2',
            'user_id' => $pemilik->id,
            'id_tabungan' => $tabungan->id,
            'jenis_simpanan' => 'Simpanan Test API',
            'jumlah' => 50000,
            'bank' => 'BRI',
            'nama_bank' => 'BRI Unit Kota',
            'nama_nasabah' => 'Nasabah Uji',
            'status' => StatusPenarikan::MENUNGGU_VERIFIKASI,
            'dikirim_at' => Carbon::now(),
        ]);

        Sanctum::actingAs($userLain);

        $this->postJson("/api/penarikan/{$penarikan->id}/batalkan")
            ->assertNotFound()
            ->assertJsonPath('status', false);

        $this->assertEquals(StatusPenarikan::MENUNGGU_VERIFIKASI, $penarikan->fresh()->status);
    }

    public function test_penarikan_yang_sedang_diperiksa_tidak_dapat_dibatalkan(): void
    {
        [$user, $tabungan] = $this->buatUserDenganTabungan(500000.00, 'API-PNK-015');

        $penarikan = PenarikanTabungan::create([
            'nomor_penarikan' => 'PNK-API-CANCEL-3',
            'user_id' => $user->id,
            'id_tabungan' => $tabungan->id,
            'jenis_simpanan' => 'Simpanan Test API',
            'jumlah' => 50000,
            'bank' => 'BRI',
            'nama_bank' => 'BRI Unit Kota',
            'nama_nasabah' => 'Nasabah Uji',
            'status' => StatusPenarikan::SEDANG_DIPERIKSA,
            'dikirim_at' => Carbon::now(),
        ]);

        Sanctum::actingAs($user);

        $this->postJson("/api/penarikan/{$penarikan->id}/batalkan")
            ->assertForbidden()
            ->assertJsonPath('status', false);

        $this->assertEquals(StatusPenarikan::SEDANG_DIPERIKSA, $penarikan->fresh()->status);
    }

    public function test_endpoint_memerlukan_autentikasi(): void
    {
        $this->getJson('/api/penarikan/aktif')->assertStatus(401);
        $this->postJson('/api/penarikan', [])->assertStatus(401);
        $this->postJson('/api/penarikan/1/batalkan')->assertStatus(401);
    }
}
