<?php

namespace Tests\Feature;

use App\Enums\MetodePembayaranSetoran;
use App\Enums\StatusPenarikan;
use App\Enums\StatusSetoran;
use App\Filament\Pages\Dashboard;
use App\Filament\Resources\PenarikanTabunganResource;
use App\Filament\Resources\SetoranTabunganResource;
use App\Filament\Widgets\Birthday;
use App\Filament\Widgets\CriticalOverdueWidget;
use App\Filament\Widgets\DepositoChartWidget;
use App\Filament\Widgets\DepositoJangkaWaktuWidget;
use App\Filament\Widgets\LaporanDepositoStatsWidget;
use App\Filament\Widgets\StatistikNasabahWidget;
use App\Filament\Widgets\VerifikasiSimpananWidget;
use App\Models\PenarikanTabungan;
use App\Models\ProdukTabungan;
use App\Models\Profile;
use App\Models\SetoranTabungan;
use App\Models\Tabungan;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class VerifikasiSimpananWidgetTest extends TestCase
{
    use DatabaseTransactions;

    public function test_widget_menampilkan_jumlah_transaksi_yang_menunggu_verifikasi(): void
    {
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        $tabungan = $this->createTabungan();
        $jumlahSetoranAwal = SetoranTabungan::query()
            ->where('status', StatusSetoran::MENUNGGU_VERIFIKASI)
            ->count();
        $jumlahPenarikanAwal = PenarikanTabungan::query()
            ->where('status', StatusPenarikan::MENUNGGU_VERIFIKASI)
            ->count();

        SetoranTabungan::query()->create([
            ...$this->setoranData($tabungan),
            'nomor_setoran' => 'STR-WIDGET-001',
            'status' => StatusSetoran::MENUNGGU_VERIFIKASI,
        ]);
        SetoranTabungan::query()->create([
            ...$this->setoranData($tabungan),
            'nomor_setoran' => 'STR-WIDGET-002',
            'status' => StatusSetoran::SEDANG_DIPERIKSA,
        ]);
        PenarikanTabungan::query()->create([
            ...$this->penarikanData($tabungan),
            'nomor_penarikan' => 'PNK-WIDGET-001',
            'status' => StatusPenarikan::MENUNGGU_VERIFIKASI,
        ]);
        PenarikanTabungan::query()->create([
            ...$this->penarikanData($tabungan),
            'nomor_penarikan' => 'PNK-WIDGET-002',
            'status' => StatusPenarikan::SELESAI,
        ]);

        $stats = (new class extends VerifikasiSimpananWidget
        {
            /** @return array<Stat> */
            public function stats(): array
            {
                return $this->getStats();
            }
        })->stats();

        $this->assertSame('Setoran Simpanan', $stats[0]->getLabel());
        $this->assertSame(number_format($jumlahSetoranAwal + 1), $stats[0]->getValue());
        $this->assertSame(SetoranTabunganResource::getUrl('index', [
            'tableFilters' => [
                'status' => ['value' => StatusSetoran::MENUNGGU_VERIFIKASI->value],
            ],
        ]), $stats[0]->getUrl());

        $this->assertSame('Penarikan Simpanan', $stats[1]->getLabel());
        $this->assertSame(number_format($jumlahPenarikanAwal + 1), $stats[1]->getValue());
        $this->assertSame(PenarikanTabunganResource::getUrl('index', [
            'tableFilters' => [
                'status' => ['value' => StatusPenarikan::MENUNGGU_VERIFIKASI->value],
            ],
        ]), $stats[1]->getUrl());
    }

    public function test_widget_terdaftar_pada_panel_admin(): void
    {
        $widgets = Filament::getPanel('admin')->getWidgets();

        $this->assertContains(VerifikasiSimpananWidget::class, $widgets);
        $this->assertContains(StatistikNasabahWidget::class, $widgets);
        $this->assertContains(CriticalOverdueWidget::class, $widgets);
        $this->assertContains(Birthday::class, $widgets);
        $this->assertNotContains(LaporanDepositoStatsWidget::class, $widgets);
        $this->assertNotContains(DepositoChartWidget::class, $widgets);
        $this->assertNotContains(DepositoJangkaWaktuWidget::class, $widgets);
    }

    public function test_dashboard_admin_menggunakan_grid_responsif_dua_kolom(): void
    {
        $dashboard = app(Dashboard::class);

        $this->assertSame([
            'md' => 2,
            'xl' => 2,
        ], $dashboard->getColumns());
    }

    private function createTabungan(): Tabungan
    {
        $user = User::factory()->create();
        Profile::factory()->create([
            'id_user' => $user->id,
            'is_active' => true,
        ]);

        $produk = ProdukTabungan::query()->firstOrCreate(
            ['kode_produk' => 'PROD-TEST-WIDGET'],
            [
                'nama_produk' => 'Simpanan Test Widget',
                'jenis_tabungan_id' => 1,
                'bunga_beaya_id' => 2,
            ]
        );

        return Tabungan::query()->create([
            'no_tabungan' => 'WIDGET-'.fake()->unique()->numerify('######'),
            'id_profile' => DB::table('profiles')->where('id_user', $user->id)->value('id'),
            'produk_tabungan' => $produk->id,
            'saldo' => 500000,
            'tanggal_buka_rekening' => Carbon::now(),
            'status_rekening' => 'aktif',
        ]);
    }

    /** @return array<string, mixed> */
    private function setoranData(Tabungan $tabungan): array
    {
        return [
            'user_id' => $tabungan->profile->id_user,
            'id_tabungan' => $tabungan->id,
            'jenis_simpanan' => 'Simpanan Test Widget',
            'jumlah' => 100000,
            'kode_unik' => 123,
            'jumlah_bayar' => 100123,
            'metode_pembayaran' => MetodePembayaranSetoran::TransferRekening,
        ];
    }

    /** @return array<string, mixed> */
    private function penarikanData(Tabungan $tabungan): array
    {
        return [
            'user_id' => $tabungan->profile->id_user,
            'id_tabungan' => $tabungan->id,
            'jenis_simpanan' => 'Simpanan Test Widget',
            'jumlah' => 50000,
            'bank' => 'BRI',
            'nama_bank' => 'BRI Unit Test',
            'nama_nasabah' => 'Nasabah Widget',
        ];
    }
}
