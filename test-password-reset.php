<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Password;
use App\Models\User;
use App\Models\Admin;

echo "=== Test Password Reset Email ===\n\n";

$email = 'pangestu.vivi@kospin.test';

// Cek apakah user ada
$user = User::where('email', $email)->first();

if (!$user) {
    echo "❌ User dengan email '{$email}' tidak ditemukan.\n";
    echo "📝 Membuat user baru...\n\n";
    
    $user = User::create([
        'name' => 'Vivi Pangestu',
        'email' => $email,
        'password' => bcrypt('password123'),
    ]);
    
    echo "✅ User berhasil dibuat!\n";
    echo "   Name: {$user->name}\n";
    echo "   Email: {$user->email}\n\n";
}

echo "📧 Mengirim email password reset ke: {$email}\n\n";

// Testing untuk User Panel (guard: web, broker: users)
try {
    $status = Password::broker('users')->sendResetLink(
        ['email' => $email]
    );

    if ($status === Password::RESET_LINK_SENT) {
        echo "✅ Email password reset berhasil dikirim!\n";
        echo "🔗 Status: {$status}\n\n";
        echo "📬 Cek Mailhog di: http://localhost:8025\n";
        echo "   (Default Mailhog web interface)\n\n";
    } else {
        echo "❌ Gagal mengirim email.\n";
        echo "   Status: {$status}\n\n";
    }
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n\n";
}

// Informasi Konfigurasi Email
echo "=== Konfigurasi Email ===\n";
echo "MAIL_MAILER: " . config('mail.default') . "\n";
echo "MAIL_HOST: " . config('mail.mailers.smtp.host') . "\n";
echo "MAIL_PORT: " . config('mail.mailers.smtp.port') . "\n";
echo "MAIL_FROM: " . config('mail.from.address') . "\n";
echo "\n";
