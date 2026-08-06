<?php

namespace Tests\Feature;

use App\Enums\StatusSetoran;
use App\Jobs\SendQRISClaimNotificationJob;
use App\Models\Admin;
use App\Models\ProdukTabungan;
use App\Models\Profile;
use App\Models\QrisStatic;
use App\Models\SetoranTabungan;
use App\Models\Tabungan;
use App\Models\User;
use App\Services\BuatSetoranTabunganService;
use App\Services\KadaluarsaSetoranTidakDibayarService;
use App\Services\KirimKlaimPembayaranService;
use App\Services\MintaRevisiSetoranService;
use App\Services\MulaiReviewSetoranService;
use App\Services\PostingSetoranKeTabunganService;
use App\Services\SetujuiSetoranService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SetoranQrisWorkflowTest extends TestCase
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

    public function test_workflow_setoran_qris_lengkap(): void
    {
        Queue::fake();
        Storage::fake('private');
        Storage::fake('public');

        $staticQris = QrisStatic::create([
            'name' => 'Kospin Static QRIS',
            'qris_string' => '00020101021130005802ID000508040001000000000000',
            'merchant_name' => 'Kospin',
            'is_active' => true,
        ]);

        $admin = Admin::create([
            'name' => 'Admin Test',
            'email' => 'admin@kospin.com',
            'password' => bcrypt('password'),
        ]);

        $user = User::factory()->create();
        Profile::factory()->create([
            'id_user' => $user->id,
            'is_active' => true,
        ]);

        $profileId = DB::table('profiles')->where('id_user', $user->id)->value('id');

        $produk = $this->getOrCreateProduct();

        $tabungan = Tabungan::create([
            'no_tabungan' => '00123-02',
            'id_profile' => $profileId,
            'produk_tabungan' => $produk->id,
            'saldo' => 100000.00,
            'tanggal_buka_rekening' => Carbon::now(),
            'status_rekening' => 'aktif',
        ]);

        $buatService = app(BuatSetoranTabunganService::class);
        $setoran = $buatService->execute($user, $tabungan, 50000);

        $this->assertEquals(StatusSetoran::MENUNGGU_PEMBAYARAN, $setoran->status);
        $this->assertEquals(50000, $setoran->jumlah);
        $this->assertGreaterThan(50000, $setoran->jumlah_bayar);
        $this->assertNotNull($setoran->qris_payload);
        $this->assertNotNull($setoran->qris_image_path);

        $waktuKlaim = Carbon::now();
        $klaimService = app(KirimKlaimPembayaranService::class);
        $mockFile = UploadedFile::fake()->image('bukti.png');

        $setoran = $klaimService->execute(
            $user,
            $setoran,
            $waktuKlaim,
            'Pembayar Ujicoba',
            'REF-12345',
            'Catatan testing',
            $mockFile
        );

        $this->assertEquals(StatusSetoran::MENUNGGU_VERIFIKASI, $setoran->status);
        $this->assertEquals('Pembayar Ujicoba', $setoran->nama_pembayar);
        $this->assertEquals('REF-12345', $setoran->referensi_pembayaran);

        Queue::assertPushed(SendQRISClaimNotificationJob::class);

        $reviewService = app(MulaiReviewSetoranService::class);
        $setoran = $reviewService->execute($admin, $setoran);
        $this->assertEquals(StatusSetoran::SEDANG_DIPERIKSA, $setoran->status);
        $this->assertEquals($admin->id, $setoran->diperiksa_oleh);

        $revisiService = app(MintaRevisiSetoranService::class);
        $setoran = $revisiService->execute($admin, $setoran, 'Bukti kurang jelas');
        $this->assertEquals(StatusSetoran::PERLU_REVISI, $setoran->status);
        $this->assertEquals('Bukti kurang jelas', $setoran->catatan_verifikasi);

        $setoran = $klaimService->execute(
            $user,
            $setoran,
            $waktuKlaim,
            'Pembayar Ujicoba',
            'REF-12345',
            'Catatan testing revisi',
            $mockFile
        );
        $this->assertEquals(StatusSetoran::MENUNGGU_VERIFIKASI, $setoran->status);

        $setoran = $reviewService->execute($admin, $setoran);

        $setujuiService = app(SetujuiSetoranService::class);
        $setoran = $setujuiService->execute(
            $admin,
            $setoran,
            'PROV-QRIS-999',
            Carbon::now(),
            'Klaiman',
            'Verifikasi OK'
        );

        $this->assertEquals(StatusSetoran::SELESAI, $setoran->status);
        $this->assertNotNull($setoran->diposting_at);
        $this->assertNotNull($setoran->selesai_at);

        $transaksi = \App\Models\TransaksiTabungan::where('setoran_id', $setoran->id)->first();
        $this->assertNotNull($transaksi);
        $this->assertEquals($setoran->jumlah_bayar, $transaksi->jumlah);
        $this->assertEquals($tabungan->id, $transaksi->id_tabungan);

        $tabunganLengkap = $tabungan->fresh();
        $this->assertEquals(100000 + $setoran->jumlah_bayar, $tabunganLengkap->saldo_akhir);
    }

    public function test_kegagalan_posting_tetap_menjaga_status_disetujui_dan_bisa_coba_ulang(): void
    {
        $admin = Admin::create([
            'name' => 'Admin Test 2',
            'email' => 'admin2@kospin.com',
            'password' => bcrypt('password'),
        ]);

        $user = User::factory()->create();
        Profile::factory()->create([
            'id_user' => $user->id,
            'is_active' => true,
        ]);

        $profileId = DB::table('profiles')->where('id_user', $user->id)->value('id');

        $produk = $this->getOrCreateProduct();

        $tabungan = Tabungan::create([
            'no_tabungan' => '00123-03',
            'id_profile' => $profileId,
            'produk_tabungan' => $produk->id,
            'saldo' => 100.00,
            'tanggal_buka_rekening' => Carbon::now(),
            'status_rekening' => 'aktif',
        ]);

        $setoran = SetoranTabungan::create([
            'nomor_setoran' => 'STR-TEST-FAIL-POST',
            'user_id' => $user->id,
            'id_tabungan' => $tabungan->id,
            'jenis_simpanan' => 'Simpanan Harian',
            'jumlah' => 10000,
            'kode_unik' => 12,
            'jumlah_bayar' => 10012,
            'status' => StatusSetoran::SEDANG_DIPERIKSA,
            'diperiksa_oleh' => $admin->id,
        ]);

        $postingServiceMock = $this->getMockBuilder(PostingSetoranKeTabunganService::class)
            ->onlyMethods(['execute'])
            ->getMock();

        $postingServiceMock->expects($this->once())
            ->method('execute')
            ->willThrowException(new \RuntimeException('Gagal mem-posting secara sengaja'));

        $this->app->instance(PostingSetoranKeTabunganService::class, $postingServiceMock);

        $setujuiService = app(SetujuiSetoranService::class);
        $setoran = $setujuiService->execute($admin, $setoran);

        $this->assertEquals(StatusSetoran::DISETUJUI, $setoran->status);

        $this->app->forgetInstance(PostingSetoranKeTabunganService::class);

        $realPostingService = app(PostingSetoranKeTabunganService::class);
        $setoran = $realPostingService->execute($setoran->id, $admin->id);

        $this->assertEquals(StatusSetoran::SELESAI, $setoran->status);
    }

    public function test_scheduler_proses_kedaluwarsa(): void
    {
        $user = User::factory()->create();
        Profile::factory()->create([
            'id_user' => $user->id,
            'is_active' => true,
        ]);

        $profileId = DB::table('profiles')->where('id_user', $user->id)->value('id');

        $produk = $this->getOrCreateProduct();

        $tabungan = Tabungan::create([
            'no_tabungan' => '00123-04',
            'id_profile' => $profileId,
            'produk_tabungan' => $produk->id,
            'saldo' => 100.00,
            'tanggal_buka_rekening' => Carbon::now(),
            'status_rekening' => 'aktif',
        ]);

        $setoranAktif = SetoranTabungan::create([
            'nomor_setoran' => 'STR-EXP-1',
            'user_id' => $user->id,
            'id_tabungan' => $tabungan->id,
            'jenis_simpanan' => 'Simpanan',
            'jumlah' => 10000,
            'kode_unik' => 12,
            'jumlah_bayar' => 10012,
            'status' => StatusSetoran::MENUNGGU_PEMBAYARAN,
            'kedaluwarsa_at' => Carbon::now()->subMinutes(1),
        ]);

        $setoranBaru = SetoranTabungan::create([
            'nomor_setoran' => 'STR-EXP-2',
            'user_id' => $user->id,
            'id_tabungan' => $tabungan->id,
            'jenis_simpanan' => 'Simpanan',
            'jumlah' => 10000,
            'kode_unik' => 15,
            'jumlah_bayar' => 10015,
            'status' => StatusSetoran::MENUNGGU_PEMBAYARAN,
            'kedaluwarsa_at' => Carbon::now()->addMinutes(10),
        ]);

        $scheduler = app(KadaluarsaSetoranTidakDibayarService::class);
        $processed = $scheduler->execute();

        $this->assertEquals(1, $processed);
        $this->assertEquals(StatusSetoran::KEDALUWARSA, $setoranAktif->fresh()->status);
        $this->assertEquals(StatusSetoran::MENUNGGU_PEMBAYARAN, $setoranBaru->fresh()->status);
    }
}
