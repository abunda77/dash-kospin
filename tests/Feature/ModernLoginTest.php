<?php

namespace Tests\Feature;

use App\Livewire\Auth\ModernLogin;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use Tests\TestCase;

class ModernLoginTest extends TestCase
{
    use DatabaseTransactions;

    public function test_login_page_can_be_rendered(): void
    {
        $this->get('/login')
            ->assertSuccessful()
            ->assertSeeLivewire(ModernLogin::class);
    }

    public function test_user_can_log_in_with_valid_credentials(): void
    {
        $user = User::factory()->create(['password' => bcrypt('password')]);

        Livewire::test(ModernLogin::class)
            ->set('email', $user->email)
            ->set('password', 'password')
            ->call('login')
            ->assertHasNoErrors()
            ->assertRedirect('/user');

        $this->assertAuthenticatedAs($user);
    }

    public function test_login_fails_with_invalid_credentials(): void
    {
        Livewire::test(ModernLogin::class)
            ->set('email', 'none@example.com')
            ->set('password', 'wrongpass')
            ->call('login')
            ->assertHasErrors('email');

        $this->assertGuest();
    }

    public function test_captcha_appears_after_repeated_failures(): void
    {
        $component = Livewire::test(ModernLogin::class);

        foreach (range(1, 3) as $i) {
            $component
                ->set('email', 'none@example.com')
                ->set('password', 'wrongpass')
                ->call('login');
        }

        $component->assertHasErrors('email')
            ->assertSet('showCaptcha', true)
            ->assertNotSet('captchaQuestion', '');
    }

    public function test_login_rejected_when_captcha_answer_is_wrong(): void
    {
        $component = Livewire::test(ModernLogin::class);

        foreach (range(1, 3) as $i) {
            $component
                ->set('email', 'none@example.com')
                ->set('password', 'wrongpass')
                ->call('login');
        }

        $component
            ->set('captchaAnswer', '999')
            ->call('login')
            ->assertHasErrors('captchaAnswer');
    }

    public function test_successful_login_resets_captcha_and_failure_counter(): void
    {
        $user = User::factory()->create(['password' => bcrypt('password')]);

        $component = Livewire::test(ModernLogin::class)
            ->set('email', 'none@example.com')
            ->set('password', 'wrongpass')
            ->call('login')
            ->set('email', 'none@example.com')
            ->set('password', 'wrongpass')
            ->call('login')
            ->set('email', $user->email)
            ->set('password', 'password')
            ->call('login');

        $component->assertRedirect('/user');

        $this->assertNull(session('login_failures'));
    }
}
