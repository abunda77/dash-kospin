<?php

namespace Tests\Feature;

use App\Services\BlacklistService;
use Tests\TestCase;

class BlacklistServiceTest extends TestCase
{
    private string $blacklistPath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->blacklistPath = tempnam(sys_get_temp_dir(), 'blacklist_service_');
        config()->set('security.blacklist_path', $this->blacklistPath);
    }

    protected function tearDown(): void
    {
        @unlink($this->blacklistPath);

        parent::tearDown();
    }

    public function test_pola_wildcard_local_part_hanya_memblokir_email_yang_cocok(): void
    {
        file_put_contents($this->blacklistPath, "email:aba*@gmail.com\n");

        $service = app(BlacklistService::class);

        $this->assertTrue($service->isBlacklistedEmail('aba123@gmail.com'));
        $this->assertTrue($service->isBlacklistedEmail('aba234@gmail.com'));
        $this->assertTrue($service->isBlacklistedEmail('ABA999@GMAIL.COM'));
        $this->assertFalse($service->isBlacklistedEmail('budi@gmail.com'));
        $this->assertFalse($service->isBlacklistedEmail('aba123@yahoo.com'));
        $this->assertFalse($service->isBlacklistedEmail('xaba123@gmail.com'));
    }

    public function test_pola_wildcard_di_awal_local_part(): void
    {
        file_put_contents($this->blacklistPath, "email:*ereo@gmail.com\n");

        $service = app(BlacklistService::class);

        $this->assertTrue($service->isBlacklistedEmail('xereo@gmail.com'));
        $this->assertTrue($service->isBlacklistedEmail('ereo@gmail.com'));
        $this->assertFalse($service->isBlacklistedEmail('xereoo@gmail.com'));
        $this->assertFalse($service->isBlacklistedEmail('xereo@yahoo.com'));
    }

    public function test_domain_polos_tetap_memblokir_seluruh_domain_dan_subdomain(): void
    {
        file_put_contents($this->blacklistPath, "email:mailinator.com\n");

        $service = app(BlacklistService::class);

        $this->assertTrue($service->isBlacklistedEmail('siapa@mailinator.com'));
        $this->assertTrue($service->isBlacklistedEmail('siapa@sub.mailinator.com'));
        $this->assertFalse($service->isBlacklistedEmail('siapa@notmailinator.com'));
    }

    public function test_bentuk_lama_bintang_at_domain_memblokir_seluruh_domain(): void
    {
        file_put_contents($this->blacklistPath, "email:*@fakedomain.com\n");

        $service = app(BlacklistService::class);

        $this->assertTrue($service->isBlacklistedEmail('siapa@fakedomain.com'));
        $this->assertTrue($service->isBlacklistedEmail('siapa@sub.fakedomain.com'));
        $this->assertFalse($service->isBlacklistedEmail('siapa@lain.com'));
    }

    public function test_beberapa_pola_sekaligus(): void
    {
        file_put_contents($this->blacklistPath, "email:aba*@gmail.com\nemail:test@yahoo.com\nip:203.0.113.10\n");

        $service = app(BlacklistService::class);

        $this->assertTrue($service->isBlacklistedEmail('aba1@gmail.com'));
        $this->assertTrue($service->isBlacklistedEmail('test@yahoo.com'));
        $this->assertFalse($service->isBlacklistedEmail('test2@yahoo.com'));
        $this->assertTrue($service->isBlacklistedIp('203.0.113.10'));
    }
}
