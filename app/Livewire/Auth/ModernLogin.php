<?php

namespace App\Livewire\Auth;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.auth')]
#[Title('Masuk')]
class ModernLogin extends Component
{
    public const MAX_ATTEMPTS = 5;

    public const LOCKOUT_MINUTES = 1;

    public const CAPTCHA_THRESHOLD = 3;

    public string $email = '';

    public string $password = '';

    public bool $remember = false;

    public bool $showCaptcha = false;

    public string $captchaQuestion = '';

    public string $captchaAnswer = '';

    protected function rules(): array
    {
        $rules = [
            'email' => ['required', 'email'],
            'password' => ['required', 'min:6'],
        ];

        if ($this->showCaptcha) {
            $rules['captchaAnswer'] = ['required'];
        }

        return $rules;
    }

    protected function messages(): array
    {
        return [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal 6 karakter.',
            'captchaAnswer.required' => 'Jawaban keamanan wajib diisi.',
        ];
    }

    public function mount(): void
    {
        $failed = (int) session('login_failures', 0);

        if ($failed >= self::CAPTCHA_THRESHOLD) {
            $this->showCaptcha = true;
            $this->generateCaptcha();
        }
    }

    public function refreshCaptcha(): void
    {
        $this->reset('captchaAnswer');
        $this->generateCaptcha();
    }

    public function login()
    {
        $this->validate();

        $key = $this->rateLimitKey();
        if (RateLimiter::tooManyAttempts($key, self::MAX_ATTEMPTS)) {
            $seconds = RateLimiter::availableIn($key);

            $minutes = ceil($seconds / 60);
            $this->addError(
                'email',
                "Terlalu banyak percobaan login. Coba lagi dalam {$minutes} menit."
            );

            return;
        }

        if ($this->showCaptcha) {
            if (! $this->isCaptchaValid()) {
                $this->addError('captchaAnswer', 'Jawaban keamanan salah.');

                return;
            }

            $this->resetCaptcha();
        }

        if (! Auth::attempt(['email' => $this->email, 'password' => $this->password], $this->remember)) {
            RateLimiter::hit($key, self::LOCKOUT_MINUTES * 60);

            $failed = (int) session('login_failures', 0) + 1;
            session(['login_failures' => $failed]);

            $this->addError('email', 'Email atau password salah.');

            if ($failed >= self::CAPTCHA_THRESHOLD && ! $this->showCaptcha) {
                $this->showCaptcha = true;
                $this->generateCaptcha();
            }

            return;
        }

        RateLimiter::clear($key);
        session()->forget('login_failures');
        session()->regenerate();

        return redirect()->intended('/user');
    }

    private function rateLimitKey(): string
    {
        return 'login:'.$this->email.':'.request()->ip();
    }

    private function generateCaptcha(): void
    {
        $a = random_int(1, 9);
        $b = random_int(1, 9);
        $operator = ['+', '-', '×'][array_rand(['+', '-', '×'])];

        $this->captchaQuestion = "{$a} {$operator} {$b}";
        session()->put('login_captcha_result', $this->solveCaptcha($a, $b, $operator));
        $this->reset('captchaAnswer');
    }

    private function solveCaptcha(int $a, int $b, string $operator): int
    {
        return match ($operator) {
            '+' => $a + $b,
            '×' => $a * $b,
            default => $a - $b,
        };
    }

    private function isCaptchaValid(): bool
    {
        $expected = (int) session('login_captcha_result');

        return $expected > 0 && (int) $this->captchaAnswer === $expected;
    }

    private function resetCaptcha(): void
    {
        $this->showCaptcha = false;
        $this->captchaQuestion = '';
        session()->forget('login_captcha_result');
    }

    public function render()
    {
        return view('livewire.auth.modern-login');
    }
}
