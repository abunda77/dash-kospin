<?php

namespace Tests\Feature;

use App\Enums\StatusPenarikan;
use App\Jobs\SendPenarikanNotificationJob;
use App\Models\Admin;
use App\Models\PenarikanTabungan;
use App\Models\ProdukTabungan;
use App\Models\Profile;
use App\Models\Tabungan;
use App\Models\TransaksiTabungan;
use App\Models\User;
use App\Services\BuatPenarikanTabunganService;
use App\Services\KirimRevisiPenarikanService;
use App\Services\MintaRevisiPenarikanService;
use App\Services\MulaiReviewPenarikanService;
use App\Services\PostingPenarikanKeTabunganService;
use App\Services\SetujuiPenarikanService;
use App\Services\TolakPenarikanService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PenarikanSimpananWorkflowTest extends TestCase
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
                'kode_produk' => 'PROD-TEST',
                'nama_produk' => 'Simpanan Test',
                'jenis_tabungan_id' => 1,
                'bunga_beaya_id' => 2,
            ]);
        }

        return $produk;
    }

    private function buatTabunganDenganSaldo(User $user, float $saldo, string $noTabungan): Tabungan
    {
        Profile::factory()->create([
            'id_user' => $user->id,
            'is_active' => true,
        ]);

        $profileId = DB::table('profiles')->where('id_user', $user->id)->value('id');

        $produk = $this->getOrCreateProduct();

        return Tabungan::create([
            'no_tabungan' => $noTabungan,
            'id_profile' => $profileId,
            'produk_tabungan' => $produk->id,
            'saldo' => $saldo,
            'tanggal_buka_rekening' => Carbon::now(),
            'status_rekening' => 'aktif',
        ]);
    }

    public function test_workflow_penarikan_simpanan_lengkap(): void
    {
        Queue::fake();
        Storage::fake('private');

        $admin = Admin::create([
            'name' => 'Admin Penarikan Test',
            'email' => 'admin-penarikan@kospin.com',
            'password' => bcrypt('password'),
        ]);

        $user = User::factory()->create();
        $tabungan = $this->buatTabunganDenganSaldo($user, 500000.00, 'PNK-00123-01');

        $buatService = app(BuatPenarikanTabunganService::class);
        $mockFile = UploadedFile::fake()->image('bukti.png');

        $penarikan = $buatService->execute(
            $user,
            $tabungan,
            100000,
            'BRI',
            'BRI Unit Kota',
            'Nasabah Uji',
            'REF-TARIK-123',
            'Catatan testing',
            $mockFile
        );

        $this->assertEquals(StatusPenarikan::MENUNGGU_VERIFIKASI, $penarikan->status);
        $this->assertEquals(100000, $penarikan->jumlah);
        $this->assertEquals('BRI', $penarikan->bank);
        $this->assertEquals('BRI Unit Kota', $penarikan->nama_bank);
        $this->assertEquals('Nasabah Uji', $penarikan->nama_nasabah);
        $this->assertNotNull($penarikan->bukti_penarikan_path);

        Queue::assertPushed(SendPenarikanNotificationJob::class);

        $reviewService = app(MulaiReviewPenarikanService::class);
        $penarikan = $reviewService->execute($admin, $penarikan);
        $this->assertEquals(StatusPenarikan::SEDANG_DIPERIKSA, $penarikan->status);
        $this->assertEquals($admin->id, $penarikan->diperiksa_oleh);

        $revisiService = app(MintaRevisiPenarikanService::class);
        $penarikan = $revisiService->execute($admin, $penarikan, 'Data rekening tujuan kurang jelas');
        $this->assertEquals(StatusPenarikan::PERLU_REVISI, $penarikan->status);
        $this->assertEquals('Data rekening tujuan kurang jelas', $penarikan->catatan_verifikasi);

        $kirimRevisiService = app(KirimRevisiPenarikanService::class);
        $penarikan = $kirimRevisiService->execute(
            $user,
            $penarikan,
            'REF-TARIK-123-R2',
            'Catatan revisi testing',
            UploadedFile::fake()->image('bukti-revisi.png')
        );
        $this->assertEquals(StatusPenarikan::MENUNGGU_VERIFIKASI, $penarikan->status);

        $penarikan = $reviewService->execute($admin, $penarikan);

        $setujuiService = app(SetujuiPenarikanService::class);
        $penarikan = $setujuiService->execute(
            $admin,
            $penarikan,
            'TRF-999',
            Carbon::now(),
            'Verifikasi OK'
        );

        $this->assertEquals(StatusPenarikan::SELESAI, $penarikan->status);
        $this->assertNotNull($penarikan->diposting_at);
        $this->assertNotNull($penarikan->selesai_at);

        $transaksi = TransaksiTabungan::where('penarikan_id', $penarikan->id)->first();
        $this->assertNotNull($transaksi);
        $this->assertEquals(TransaksiTabungan::JENIS_PENARIKAN, $transaksi->jenis_transaksi);
        $this->assertEquals($penarikan->jumlah, $transaksi->jumlah);
        $this->assertEquals($tabungan->id, $transaksi->id_tabungan);

        $tabunganLengkap = $tabungan->fresh();
        $this->assertEquals(500000 - $penarikan->jumlah, $tabunganLengkap->saldo_akhir);
    }

    public function test_penarikan_ditolak_tidak_mengubah_saldo(): void
    {
        $admin = Admin::create([
            'name' => 'Admin Penarikan Tolak',
            'email' => 'admin-penarikan-tolak@kospin.com',
            'password' => bcrypt('password'),
        ]);

        $user = User::factory()->create();
        $tabungan = $this->buatTabunganDenganSaldo($user, 200000.00, 'PNK-00123-02');

        $buatService = app(BuatPenarikanTabunganService::class);
        $penarikan = $buatService->execute($user, $tabungan, 50000, 'BNI', 'BNI Cabang Pusat', 'Nasabah Uji');

        $penarikan = app(MulaiReviewPenarikanService::class)->execute($admin, $penarikan);
        $penarikan = app(TolakPenarikanService::class)->execute($admin, $penarikan, 'Saldo tidak mencukupi');

        $this->assertEquals(StatusPenarikan::DITOLAK, $penarikan->status);
        $this->assertEquals('Saldo tidak mencukupi', $penarikan->alasan_penolakan);
        $this->assertEquals(200000, $tabungan->fresh()->saldo_akhir);
        $this->assertFalse(TransaksiTabungan::where('penarikan_id', $penarikan->id)->exists());
    }

    public function test_penarikan_ditolak_jika_melebih_saldo(): void
    {
        $user = User::factory()->create();
        $tabungan = $this->buatTabunganDenganSaldo($user, 100000.00, 'PNK-00123-03');

        $this->expectException(\InvalidArgumentException::class);

        app(BuatPenarikanTabunganService::class)->execute(
            $user,
            $tabungan,
            250000,
            'BRI',
            'BRI Unit Kota',
            'Nasabah Uji'
        );
    }

    public function test_penarikan_ditolak_jika_masih_ada_transaksi_aktif(): void
    {
        $user = User::factory()->create();
        $tabungan = $this->buatTabunganDenganSaldo($user, 500000.00, 'PNK-00123-04');

        app(BuatPenarikanTabunganService::class)->execute($user, $tabungan, 50000, 'BRI', 'BRI Unit Kota', 'Nasabah Uji');

        $this->expectException(\RuntimeException::class);

        app(BuatPenarikanTabunganService::class)->execute($user, $tabungan, 50000, 'BRI', 'BRI Unit Kota', 'Nasabah Uji');
    }

    public function test_kegagalan_posting_tetap_menjaga_status_disetujui_dan_bisa_coba_ulang(): void
    {
        $admin = Admin::create([
            'name' => 'Admin Penarikan Posting',
            'email' => 'admin-penarikan-posting@kospin.com',
            'password' => bcrypt('password'),
        ]);

        $user = User::factory()->create();
        $tabungan = $this->buatTabunganDenganSaldo($user, 300000.00, 'PNK-00123-05');

        $penarikan = PenarikanTabungan::create([
            'nomor_penarikan' => 'PNK-TEST-FAIL-POST',
            'user_id' => $user->id,
            'id_tabungan' => $tabungan->id,
            'jenis_simpanan' => 'Simpanan Harian',
            'jumlah' => 10000,
            'bank' => 'BRI',
            'nama_bank' => 'BRI Unit Kota',
            'nama_nasabah' => 'Nasabah Uji',
            'status' => StatusPenarikan::SEDANG_DIPERIKSA,
            'diperiksa_oleh' => $admin->id,
        ]);

        $postingServiceMock = $this->getMockBuilder(PostingPenarikanKeTabunganService::class)
            ->onlyMethods(['execute'])
            ->getMock();

        $postingServiceMock->expects($this->once())
            ->method('execute')
            ->willThrowException(new \RuntimeException('Gagal mem-posting secara sengaja'));

        $this->app->instance(PostingPenarikanKeTabunganService::class, $postingServiceMock);

        $setujuiService = app(SetujuiPenarikanService::class);
        $penarikan = $setujuiService->execute($admin, $penarikan);

        $this->assertEquals(StatusPenarikan::DISETUJUI, $penarikan->status);

        $this->app->forgetInstance(PostingPenarikanKeTabunganService::class);

        $realPostingService = app(PostingPenarikanKeTabunganService::class);
        $penarikan = $realPostingService->execute($penarikan->id, $admin->id);

        $this->assertEquals(StatusPenarikan::SELESAI, $penarikan->status);
    }
}
