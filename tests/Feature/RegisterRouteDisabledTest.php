<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class RegisterRouteDisabledTest extends TestCase
{
    use DatabaseTransactions;

    public function test_route_register_sudah_tidak_terdaftar(): void
    {
        $this->assertFalse(Route::has('register'));
    }

    public function test_endpoint_register_api_menolak_pendaftaran(): void
    {
        $this->postJson('/api/register', [
            'name' => 'Pendaftar Baru',
            'email' => 'pendaftar@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertNotFound();
    }

    public function test_halaman_login_masih_dapat_dirender(): void
    {
        $this->get('/login')->assertSuccessful();
    }

    public function test_halaman_welcome_masih_dapat_dirender(): void
    {
        $this->get('/')->assertSuccessful();
    }
}
