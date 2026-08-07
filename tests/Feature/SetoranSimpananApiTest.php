<?php

namespace Tests\Feature;

use App\Enums\StatusSetoran;
use App\Models\ProdukTabungan;
use App\Models\Profile;
use App\Models\QrisStatic;
use App\Models\SetoranTabungan;
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

class SetoranSimpananApiTest extends TestCase
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
                'kode_produk' => 'PROD-TEST-API-STR',
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

    private function buatQrisStatic(): void
    {
        if (! QrisStatic::where('is_active', true)->exists()) {
            QrisStatic::create([
                'name' => 'Kospin Static QRIS API',
                'qris_string' => '00020101021130005802ID000508040001000000000000',
                'merchant_name' => 'Kospin',
                'is_active' => true,
            ]);
        }
    }

    public function test_rekening_options_mengembalikan_tabungan_aktif_milik_user(): void
    {
        [$user, $tabungan] = $this->buatUserDenganTabungan(100000.00, 'API-STR-001');

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/setoran/rekening-options');

        $response->assertStatus(200)
            ->assertJsonPath('status', true)
            ->assertJsonPath('data.0.id', $tabungan->id)
            ->assertJsonPath('data.0.no_tabungan', 'API-STR-001');
    }

    public function test_generate_qris_setoran_berhasil(): void
    {
        Queue::fake();
        Storage::fake('public');
        $this->buatQrisStatic();

        [$user, $tabungan] = $this->buatUserDenganTabungan(100000.00, 'API-STR-002');

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/setoran', [
            'id_tabungan' => $tabungan->id,
            'jumlah' => 50000,
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('status', true)
            ->assertJsonPath('data.jumlah', 50000)
            ->assertJsonPath('data.status', StatusSetoran::MENUNGGU_PEMBAYARAN->value);

        $this->assertNotNull($response->json('data.qris_payload'));
        $this->assertGreaterThan(50000, $response->json('data.jumlah_bayar'));

        $this->assertDatabaseHas('setoran_tabungans', [
            'id' => $response->json('data.id'),
            'user_id' => $user->id,
            'id_tabungan' => $tabungan->id,
            'jumlah' => 50000,
        ]);
    }

    public function test_generate_qris_gagal_validasi_nominal(): void
    {
        [$user, $tabungan] = $this->buatUserDenganTabungan(100000.00, 'API-STR-003');

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/setoran', [
            'id_tabungan' => $tabungan->id,
            'jumlah' => 5000,
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('status', false)
            ->assertJsonValidationErrors(['jumlah']);
    }

    public function test_generate_qris_gagal_ketika_layanan_tidak_tersedia(): void
    {
        QrisStatic::query()->update(['is_active' => false]);

        [$user, $tabungan] = $this->buatUserDenganTabungan(100000.00, 'API-STR-004');

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/setoran', [
            'id_tabungan' => $tabungan->id,
            'jumlah' => 50000,
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('status', false);
    }

    public function test_setoran_aktif_dan_history(): void
    {
        [$user, $tabungan] = $this->buatUserDenganTabungan(100000.00, 'API-STR-005');

        $setoran = SetoranTabungan::create([
            'nomor_setoran' => 'STR-API-ACTIVE-1',
            'user_id' => $user->id,
            'id_tabungan' => $tabungan->id,
            'jenis_simpanan' => 'Simpanan Test API',
            'jumlah' => 50000,
            'kode_unik' => 12,
            'jumlah_bayar' => 50012,
            'status' => StatusSetoran::MENUNGGU_PEMBAYARAN,
            'kedaluwarsa_at' => Carbon::now()->addMinutes(30),
        ]);

        SetoranTabungan::create([
            'nomor_setoran' => 'STR-API-HISTORY-1',
            'user_id' => $user->id,
            'id_tabungan' => $tabungan->id,
            'jenis_simpanan' => 'Simpanan Test API',
            'jumlah' => 25000,
            'kode_unik' => 15,
            'jumlah_bayar' => 25015,
            'status' => StatusSetoran::SELESAI,
        ]);

        Sanctum::actingAs($user);

        $aktif = $this->getJson('/api/setoran/aktif');
        $aktif->assertStatus(200)
            ->assertJsonPath('status', true)
            ->assertJsonPath('data.id', $setoran->id);

        $history = $this->getJson('/api/setoran/history');
        $history->assertStatus(200)
            ->assertJsonPath('status', true)
            ->assertJsonCount(2, 'data');
    }

    public function test_klaim_pembayaran_berhasil(): void
    {
        Queue::fake();
        Storage::fake('private');

        [$user, $tabungan] = $this->buatUserDenganTabungan(100000.00, 'API-STR-006');

        $setoran = SetoranTabungan::create([
            'nomor_setoran' => 'STR-API-KLAIM-1',
            'user_id' => $user->id,
            'id_tabungan' => $tabungan->id,
            'jenis_simpanan' => 'Simpanan Test API',
            'jumlah' => 50000,
            'kode_unik' => 12,
            'jumlah_bayar' => 50012,
            'status' => StatusSetoran::MENUNGGU_PEMBAYARAN,
            'kedaluwarsa_at' => Carbon::now()->addMinutes(30),
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson("/api/setoran/{$setoran->id}/klaim", [
            'waktu_klaim_bayar' => now()->format('Y-m-d H:i:s'),
            'nama_pembayar' => 'Pembayar Uji',
            'referensi_pembayaran' => 'REF-KLAIM-1',
            'catatan_pengguna' => 'Klaim via API',
            'bukti_pembayaran' => UploadedFile::fake()->image('bukti.png'),
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('status', true)
            ->assertJsonPath('data.status', StatusSetoran::MENUNGGU_VERIFIKASI->value)
            ->assertJsonPath('data.nama_pembayar', 'Pembayar Uji');
    }

    public function test_klaim_pembayaran_ditolak_ketika_status_tidak_mengizinkan(): void
    {
        [$user, $tabungan] = $this->buatUserDenganTabungan(100000.00, 'API-STR-007');

        $setoran = SetoranTabungan::create([
            'nomor_setoran' => 'STR-API-KLAIM-2',
            'user_id' => $user->id,
            'id_tabungan' => $tabungan->id,
            'jenis_simpanan' => 'Simpanan Test API',
            'jumlah' => 50000,
            'kode_unik' => 20,
            'jumlah_bayar' => 50020,
            'status' => StatusSetoran::MENUNGGU_VERIFIKASI,
            'kedaluwarsa_at' => Carbon::now()->addMinutes(30),
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson("/api/setoran/{$setoran->id}/klaim", [
            'waktu_klaim_bayar' => now()->format('Y-m-d H:i:s'),
            'nama_pembayar' => 'Pembayar Uji',
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('status', false);
    }

    public function test_show_setoran_tidak_menemukan_data_orang_lain(): void
    {
        [$pemilik, $tabungan] = $this->buatUserDenganTabungan(100000.00, 'API-STR-008');
        [$userLain] = $this->buatUserDenganTabungan(100000.00, 'API-STR-009');

        $setoran = SetoranTabungan::create([
            'nomor_setoran' => 'STR-API-OWNER-1',
            'user_id' => $pemilik->id,
            'id_tabungan' => $tabungan->id,
            'jenis_simpanan' => 'Simpanan Test API',
            'jumlah' => 50000,
            'kode_unik' => 30,
            'jumlah_bayar' => 50030,
            'status' => StatusSetoran::SELESAI,
        ]);

        Sanctum::actingAs($userLain);

        $this->getJson("/api/setoran/{$setoran->id}")
            ->assertStatus(404)
            ->assertJsonPath('status', false);
    }

    public function test_endpoint_memerlukan_autentikasi(): void
    {
        $this->getJson('/api/setoran/aktif')->assertStatus(401);
        $this->postJson('/api/setoran', [])->assertStatus(401);
    }
}
