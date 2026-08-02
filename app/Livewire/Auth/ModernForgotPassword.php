<?php

namespace App\Livewire\Auth;

use Illuminate\Support\Facades\Password;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.auth')]
#[Title('Lupa Password')]
class ModernForgotPassword extends Component
{
    public string $email = '';

    public bool $emailSent = false;

    protected function rules(): array
    {
        return [
            'email' => ['required', 'email'],
        ];
    }

    protected function messages(): array
    {
        return [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
        ];
    }

    public function sendResetLink(): void
    {
        $this->validate();

        $status = Password::broker('users')->sendResetLink([
            'email' => $this->email,
        ]);

        if ($status !== Password::RESET_LINK_SENT) {
            $this->addError('email', __($status));

            return;
        }

        $this->emailSent = true;
    }

    public function render()
    {
        return view('livewire.auth.modern-forgot-password');
    }
}
