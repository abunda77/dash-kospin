<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class ApiAuthSecurityTest extends TestCase
{
    use DatabaseTransactions;

    private string $blacklistPath;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();

        $this->blacklistPath = tempnam(sys_get_temp_dir(), 'blacklist_');
        config()->set('security.blacklist_path', $this->blacklistPath);
    }

    protected function tearDown(): void
    {
        @unlink($this->blacklistPath);

        parent::tearDown();
    }

    private function writeBlacklist(string $contents): void
    {
        file_put_contents($this->blacklistPath, $contents);
        clearstatcache();
        Cache::flush();
    }

    public function test_login_diblokir_setelah_lima_kegagalan(): void
    {
        $user = User::factory()->create(['password' => bcrypt('rahasia123')]);

        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->postJson('/api/login', [
                'email' => $user->email,
                'password' => 'password-salah',
            ])->assertStatus(401);
        }

        $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password-salah',
        ])
            ->assertStatus(429)
            ->assertJsonPath('status', false)
            ->assertHeader('Retry-After');
    }

    public function test_login_sukses_mereset_limiter(): void
    {
        $user = User::factory()->create(['password' => bcrypt('rahasia123')]);

        for ($attempt = 0; $attempt < 3; $attempt++) {
            $this->postJson('/api/login', [
                'email' => $user->email,
                'password' => 'password-salah',
            ])->assertStatus(401);
        }

        $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'rahasia123',
        ])->assertStatus(200);

        $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password-salah',
        ])->assertStatus(401);
    }

    public function test_register_dibatasi_per_menit(): void
    {
        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->postJson('/api/register', [
                'name' => 'Pendaftar '.$attempt,
                'email' => "pendaftar{$attempt}@example.com",
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ]);
        }

        $this->postJson('/api/register', [
            'name' => 'Pendaftar Terakhir',
            'email' => 'pendaftar-terakhir@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])
            ->assertStatus(429)
            ->assertHeader('Retry-After')
            ->assertJsonPath('status', false)
            ->assertJsonPath('message', fn (string $message): bool => str_contains($message, 'Terlalu banyak permintaan'));
    }

    public function test_forgot_password_dibatasi_per_email(): void
    {
        $user = User::factory()->create();

        for ($attempt = 0; $attempt < 3; $attempt++) {
            $this->postJson('/api/forgot-password', ['email' => $user->email])
                ->assertStatus(200);
        }

        $this->postJson('/api/forgot-password', ['email' => $user->email])
            ->assertStatus(429);
    }

    public function test_forgot_password_tidak_membocorkan_email_tidak_terdaftar(): void
    {
        $this->postJson('/api/forgot-password', ['email' => 'tidak-terdaftar@example.com'])
            ->assertStatus(200)
            ->assertJsonPath('status', true);
    }

    public function test_register_menolak_domain_email_blacklist(): void
    {
        $this->writeBlacklist("# Domain email yang diblokir\nemail:*@fakedomain.com\n");

        $this->postJson('/api/register', [
            'name' => 'Pendaftar Fiktif',
            'email' => 'orang@fakedomain.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors('email')
            ->assertJsonPath('errors.email.0', 'Unknown occurs');
    }

    public function test_domain_email_lain_tetap_diterima(): void
    {
        $this->writeBlacklist("email:*@fakedomain.com\n");

        $response = $this->postJson('/api/register', [
            'name' => 'Pendaftar Valid',
            'email' => 'orang@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $this->assertNotSame(422, $response->getStatusCode());
    }

    public function test_request_dari_ip_blacklist_ditolak(): void
    {
        $this->writeBlacklist("ip:127.0.0.1\n");

        $this->postJson('/api/login', [
            'email' => 'siapa@saja.com',
            'password' => 'password-salah',
        ])
            ->assertStatus(403)
            ->assertJsonPath('status', false);
    }
}
