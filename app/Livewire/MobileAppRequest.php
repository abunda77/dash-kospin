<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\User;
use App\Mail\MobileAppRequestNotification;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Validate;

class MobileAppRequest extends Component
{
    #[Validate('required|email')]
    public string $email = '';

    public bool $loading = false;
    public ?string $error = null;
    public ?string $success = null;
    public ?array $userData = null;
    public bool $requestSent = false;

    public function submit(): void
    {
        $this->validate();

        $this->loading = true;
        $this->error = null;
        $this->success = null;
        $this->userData = null;

        try {
            // Cari user berdasarkan email
            $user = User::where('email', $this->email)->first();

            if (!$user) {
                $this->error = 'Email tidak terdaftar dalam sistem kami. Silakan hubungi admin atau daftar terlebih dahulu.';
                $this->loading = false;
                return;
            }

            $this->userData = [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ];

            // Kirim email notifikasi ke admin
            $this->sendNotificationToAdmin($user);

            $this->success = 'Permintaan Anda telah dikirim! Silakancoba join tester dan download app ke depan maksimal 1x24 jam. Jika ada kendala silakan hubungi email : admin@kospinsinaraartha.co.id.';
            $this->requestSent = true;

            Log::info('Mobile app request submitted', [
                'user_id' => $user->id,
                'email' => $user->email,
                'name' => $user->name,
                'requested_at' => now()->toISOString(),
            ]);

        } catch (\Exception $e) {
            Log::error('Error processing mobile app request', [
                'email' => $this->email,
                'error' => $e->getMessage(),
            ]);
            $this->error = 'Terjadi kesalahan saat memproses permintaan Anda. Silakan coba lagi nanti.';
        }

        $this->loading = false;
    }

    private function sendNotificationToAdmin(User $user): void
    {
        $adminEmail = config('mail.mobile_app_request_admin', 'admin@kospinsinaraartha.co.id');

        try {
            Mail::to($adminEmail)->send(new MobileAppRequestNotification($user));
            
            Log::info('Mobile app request notification sent to admin', [
                'admin_email' => $adminEmail,
                'user_email' => $user->email,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send mobile app request notification', [
                'admin_email' => $adminEmail,
                'user_email' => $user->email,
                'error' => $e->getMessage(),
            ]);
            // Tetap lanjutkan meskipun email gagal dikirim
        }
    }

    public function resetForm(): void
    {
        $this->reset(['email', 'error', 'success', 'userData', 'requestSent']);
    }

    public function render()
    {
        return view('livewire.mobile-app-request')
            ->layout('layouts.public', ['title' => 'Permintaan Download Aplikasi Mobile']);
    }
}
