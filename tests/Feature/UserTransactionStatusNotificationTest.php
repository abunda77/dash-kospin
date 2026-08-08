<?php

namespace Tests\Feature;

use App\Enums\StatusPenarikan;
use App\Enums\StatusSetoran;
use App\Models\PenarikanTabungan;
use App\Models\SetoranTabungan;
use App\Models\User;
use App\Services\CatatRiwayatStatusPenarikanService;
use App\Services\CatatRiwayatStatusSetoranService;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class UserTransactionStatusNotificationTest extends TestCase
{
    use DatabaseTransactions;

    public function test_panel_user_menampilkan_database_notifications(): void
    {
        $panel = Filament::getPanel('user');

        $this->assertTrue($panel->hasDatabaseNotifications());
        $this->assertSame('30s', $panel->getDatabaseNotificationsPollingInterval());
    }

    public function test_perubahan_status_setoran_membuat_notifikasi_untuk_user(): void
    {
        $user = User::factory()->create();
        $setoran = SetoranTabungan::create([
            'nomor_setoran' => 'STR-NOTIF-001',
            'user_id' => $user->id,
            'id_tabungan' => 1,
            'jenis_simpanan' => 'Simpanan Uji',
            'jumlah' => 50000,
            'kode_unik' => 25,
            'jumlah_bayar' => 50025,
            'status' => StatusSetoran::SEDANG_DIPERIKSA,
        ]);

        CatatRiwayatStatusSetoranService::catat(
            $setoran,
            StatusSetoran::MENUNGGU_VERIFIKASI,
            StatusSetoran::SEDANG_DIPERIKSA
        );

        $notification = $user->notifications()->firstOrFail();

        $this->assertSame('Status Setoran Diperbarui', $notification->data['title']);
        $this->assertStringContainsString('STR-NOTIF-001', $notification->data['body']);
        $this->assertStringContainsString('Sedang Diperiksa', $notification->data['body']);
    }

    public function test_perubahan_status_penarikan_membuat_notifikasi_untuk_user(): void
    {
        $user = User::factory()->create();
        $penarikan = PenarikanTabungan::create([
            'nomor_penarikan' => 'PNK-NOTIF-001',
            'user_id' => $user->id,
            'id_tabungan' => 1,
            'jenis_simpanan' => 'Simpanan Uji',
            'jumlah' => 50000,
            'bank' => 'BRI',
            'nama_bank' => 'BRI',
            'nama_nasabah' => 'Anggota Uji',
            'status' => StatusPenarikan::PERLU_REVISI,
        ]);

        CatatRiwayatStatusPenarikanService::catat(
            $penarikan,
            StatusPenarikan::SEDANG_DIPERIKSA,
            StatusPenarikan::PERLU_REVISI
        );

        $notification = $user->notifications()->firstOrFail();

        $this->assertSame('Status Penarikan Diperbarui', $notification->data['title']);
        $this->assertStringContainsString('PNK-NOTIF-001', $notification->data['body']);
        $this->assertStringContainsString('Perlu Revisi', $notification->data['body']);
    }
}
