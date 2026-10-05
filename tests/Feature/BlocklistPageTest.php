<?php

namespace Tests\Feature;

use App\Filament\Pages\Blocklist;
use App\Models\Admin;
use App\Services\BlacklistService;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Gate;
use Livewire\Livewire;
use Tests\TestCase;

class BlocklistPageTest extends TestCase
{
    private string $blacklistPath;

    private bool $canEdit = true;

    protected function setUp(): void
    {
        parent::setUp();

        $this->blacklistPath = tempnam(sys_get_temp_dir(), 'blacklist_');
        config()->set('security.blacklist_path', $this->blacklistPath);
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        $admin = new Admin(['name' => 'Admin', 'email' => 'admin@example.com']);
        $admin->id = 1;
        $this->actingAs($admin, 'admin');
        Gate::before(fn ($user, string $ability): ?bool => $ability === 'page_Blocklist' ? $this->canEdit : null);
    }

    protected function tearDown(): void
    {
        @unlink($this->blacklistPath);

        parent::tearDown();
    }

    public function test_halaman_memuat_dan_menyimpan_isi_file_tanpa_mengubah_komentar(): void
    {
        $original = "# Catatan\nemail:example.com\nip:203.0.113.10\n";
        file_put_contents($this->blacklistPath, $original);

        Livewire::test(Blocklist::class)
            ->assertSet('data.content', $original)
            ->set('data.content', "# Catatan\nemail:example.com\nip:2001:db8::1\n")
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame("# Catatan\nemail:example.com\nip:2001:db8::1\n", file_get_contents($this->blacklistPath));
    }

    public function test_entri_tidak_valid_tidak_mengubah_file(): void
    {
        $original = "ip:203.0.113.10\n";
        file_put_contents($this->blacklistPath, $original);

        Livewire::test(Blocklist::class)
            ->set('data.content', "email:example.com\nip:invalid\n")
            ->call('save')
            ->assertHasFormErrors(['content']);

        $this->assertSame($original, file_get_contents($this->blacklistPath));
    }

    public function test_perubahan_dengan_ukuran_sama_langsung_terbaca_oleh_layanan(): void
    {
        file_put_contents($this->blacklistPath, "ip:203.0.113.10\n");
        $service = app(BlacklistService::class);
        $this->assertTrue($service->isBlacklistedIp('203.0.113.10'));

        Livewire::test(Blocklist::class)
            ->set('data.content', "ip:203.0.113.11\n")
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertFalse($service->isBlacklistedIp('203.0.113.10'));
        $this->assertTrue($service->isBlacklistedIp('203.0.113.11'));
    }

    public function test_semua_entri_dapat_dihapus(): void
    {
        file_put_contents($this->blacklistPath, "ip:203.0.113.10\n");

        Livewire::test(Blocklist::class)
            ->set('data.content', '')
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame('', file_get_contents($this->blacklistPath));
    }

    public function test_halaman_tidak_dapat_diakses_tanpa_izin_shield(): void
    {
        $this->canEdit = false;

        $this->assertFalse(Blocklist::canAccess());
        $this->get(Blocklist::getUrl())->assertForbidden();
    }

    public function test_format_tidak_dikenal_ditolak(): void
    {
        file_put_contents($this->blacklistPath, "# tetap\n");

        Livewire::test(Blocklist::class)
            ->set('data.content', "domain:example.com\n")
            ->call('save')
            ->assertHasFormErrors(['content']);

        $this->assertSame("# tetap\n", file_get_contents($this->blacklistPath));
    }
}
