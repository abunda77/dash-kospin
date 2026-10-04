# Keamanan API: Rate Limiting & Blacklist

Dokumen ini menjelaskan pola pengamanan endpoint autentikasi API (login, register, forgot-password, reset-password) yang dipakai di Dash-Kospin. Ditulis agar bisa **dipakai ulang sebagai rujukan** di proyek Laravel 11 lain — setiap bagian menyertakan kode yang bisa disalin dan titik adaptasinya.

- [Tujuan](#tujuan)
- [Arsitektur](#arsitektur)
- [1. Rate Limiting](#1-rate-limiting)
- [2. Respons 429 Terstandar](#2-respons-429-terstandar)
- [3. Blacklist Domain Email & IP](#3-blacklist-domain-email--ip)
- [4. Anti User Enumeration pada Forgot Password](#4-anti-user-enumeration-pada-forgot-password)
- [5. Testing](#5-testing)
- [6. Checklist Adopsi ke Proyek Lain](#6-checklist-adopsi-ke-proyek-lain)
- [7. Catatan Operasional & Trade-off](#7-catatan-operasional--trade-off)
- [Lampiran: Konfigurasi & File](#lampiran-konfigurasi--file)

---

## Tujuan

| Ancaman | Mitigasi |
|---|---|
| Brute force password | Limit login berbasis **kegagalan** per `email\|ip` + jaring per IP |
| Spam pembuatan akun | Limit register per IP |
| Email bombing / spam reset password | Limit forgot-password per email **dan** per IP |
| Brute force token reset | Limit reset-password per IP |
| Pendaftaran dari domain email disposable/fiktif | Blacklist domain email berbasis file |
| Request dari sumber jahat | Blacklist alamat IP berbasis file |
| User enumeration (menebak email terdaftar) | Pesan generik pada forgot-password |

---

## Arsitektur

```
config/security.php                       # path file blacklist
blacklist.txt                             # daftar email: / ip:  (di-gitignore)
app/Services/BlacklistService.php         # baca + cache + query blacklist
app/Http/Middleware/BlockBlacklistedIp.php# tolak request dari IP blacklist (403)
app/Providers/AppServiceProvider.php      # named rate limiter (configureRateLimiters)
app/Http/Controllers/Api/AuthController.php # limiter login + cek domain email
bootstrap/app.php                         # registrasi middleware + render 429
routes/api.php                            # pemasangan throttle:NamaLimiter
tests/Feature/ApiAuthSecurityTest.php     # pengujian
```

---

## 1. Rate Limiting

### 1.1 Ringkasan aturan

| Endpoint | Batas | Berbasis | Mekanisme |
|---|---|---|---|
| `POST /api/login` | 5 gagal / 5 menit (per `email\|ip`) | **kegagalan**, bukan request | `RateLimiter` manual di controller |
| `POST /api/login` | 20 gagal / 5 menit (per IP) | kegagalan | jaring credential stuffing |
| `POST /api/register` | 5 / menit (per IP) | semua request | named limiter `throttle:api-register` |
| `POST /api/forgot-password` | 3 / menit (per email) | semua request | named limiter `throttle:api-forgot-password` |
| `POST /api/forgot-password` | 5 / menit (per IP) | semua request | named limiter (limit kedua) |
| `POST /api/reset-password` | 5 / menit (per IP) | semua request | named limiter `throttle:api-reset-password` |

> **Kenapa login memakai limiter manual?** Middleware `throttle:` menghitung **semua** request. Kebutuhan "5 kali **gagal**" mengharuskan counter hanya bertambah saat autentikasi gagal dan di-reset saat berhasil — jadi `RateLimiter` dipanggil langsung di controller.

### 1.2 Named limiter (register / forgot / reset)

Definisikan di `AppServiceProvider::boot()`:

```php
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

protected function configureRateLimiters(): void
{
    RateLimiter::for('api-register', fn (Request $request) => Limit::perMinute(5)->by($request->ip()));

    // Mengembalikan array Limit = semua limit berlaku (email DAN ip).
    RateLimiter::for('api-forgot-password', fn (Request $request) => [
        Limit::perMinute(3)->by('email:'.(string) $request->input('email')),
        Limit::perMinute(5)->by('ip:'.$request->ip()),
    ]);

    RateLimiter::for('api-reset-password', fn (Request $request) => Limit::perMinute(5)->by($request->ip()));
}
```

Pasang di route:

```php
Route::post('/register', [AuthController::class, 'register'])
    ->middleware('throttle:api-register');
Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])
    ->middleware('throttle:api-forgot-password');
Route::post('/reset-password', [AuthController::class, 'resetPassword'])
    ->middleware('throttle:api-reset-password');
```

**Titik adaptasi:** nilai `perMinute(n)` dan key `by(...)`. Untuk batas per jam gunakan `Limit::perHour(n)`; untuk menit kustom `Limit::perMinutes($decayMinutes, $maxAttempts)`.

### 1.3 Limiter login berbasis kegagalan

```php
private const MAX_LOGIN_ATTEMPTS = 5;          // per email|ip
private const MAX_LOGIN_ATTEMPTS_PER_IP = 20;  // jaring per IP
private const LOGIN_LOCKOUT_SECONDS = 300;     // 5 menit

public function login(Request $request)
{
    $validatedData = $request->validate([
        'email' => 'required|string|email',
        'password' => 'required|string',
    ]);

    $emailKey = 'api-login:'.Str::lower($validatedData['email']).'|'.$request->ip();
    $ipKey = 'api-login:ip:'.$request->ip();

    if (RateLimiter::tooManyAttempts($emailKey, self::MAX_LOGIN_ATTEMPTS)
        || RateLimiter::tooManyAttempts($ipKey, self::MAX_LOGIN_ATTEMPTS_PER_IP)) {
        $seconds = max(1, RateLimiter::availableIn($emailKey), RateLimiter::availableIn($ipKey));

        return response()->json([
            'status' => false,
            'message' => 'Terlalu banyak percobaan login. Coba lagi dalam '.ceil($seconds / 60).' menit.',
        ], 429)->header('Retry-After', (string) $seconds);
    }

    $user = User::where('email', $validatedData['email'])->first();

    if (! $user || ! Hash::check($validatedData['password'], $user->password)) {
        RateLimiter::hit($emailKey, self::LOGIN_LOCKOUT_SECONDS);
        RateLimiter::hit($ipKey, self::LOGIN_LOCKOUT_SECONDS);

        return response()->json(['status' => false, 'message' => 'Invalid credentials'], 401);
    }

    RateLimiter::clear($emailKey);
    RateLimiter::clear($ipKey);
    // ... buat token
}
```

**Prinsip penting:**

- `hit()` **hanya** dipanggil pada cabang gagal; `clear()` pada cabang sukses.
- Key menggabungkan `email` **dan** `ip`: mencegah satu akun dilock dari mana saja (DoS akun) sekaligus tidak mengunci satu IP NAT saat satu email salah.
- Jaring per IP mencegah credential stuffing yang berputar-putar di banyak email.
- `availableIn()` dipakai untuk mengisi header `Retry-After` agar klien tahu harus menunggu berapa lama.

---

## 2. Respons 429 Terstandar

Tanpa penanganan khusus, `ThrottleRequestsException` akan jatuh ke handler generik dan menghasilkan pesan yang menyesatkan. Tambahkan render khusus di `bootstrap/app.php`:

```php
use Illuminate\Http\Exceptions\ThrottleRequestsException;

$exceptions->render(function (Throwable $e, $request) {
    if ($request->is('api/*') || $request->expectsJson()) {
        // ... handler lain (ValidationException, AuthenticationException)

        if ($e instanceof ThrottleRequestsException) {
            $headers = $e->getHeaders();
            $retryAfter = (int) ($headers['Retry-After'] ?? 0);

            return response()->json([
                'status' => false,
                'message' => $retryAfter > 0
                    ? "Terlalu banyak permintaan. Coba lagi dalam {$retryAfter} detik."
                    : 'Terlalu banyak permintaan. Silakan coba lagi nanti.',
                'retry_after' => $retryAfter,
            ], 429, $headers);
        }
    }
});
```

`$e->getHeaders()` juga membawa `X-RateLimit-*` bawaan Laravel sehingga diteruskan ke klien.

---

## 3. Blacklist Domain Email & IP

Blacklist disimpan sebagai file teks sederhana supaya bisa diubah oleh ops tanpa deploy.

### 3.1 Format file (`blacklist.txt`)

```
# Baris diawali # = komentar
email:*@fakedomain.com      # wildcard domain
email:mailinator.com        # tanpa *@ juga didukung (termasuk subdomain)
ip:203.0.113.10
```

- `email:<domain>` → memblokir domain email **dan** subdomain-nya (mis. `mailinator.com` memblokir `a.mailinator.com`).
- `ip:<alamat>` → memblokir seluruh request API dari IP tersebut (exact match).
- Taruh di root proyek dan **tambahkan ke `.gitignore`** karena berisi data operasional, bukan kode.

### 3.2 Config

```php
// config/security.php
return [
    'blacklist_path' => env('SECURITY_BLACKLIST_PATH') ?: base_path('blacklist.txt'),
];
```

### 3.3 Service pembaca + cache

Membaca file setiap request itu mahal. Cache hasil parsing dan kunci cache dengan signature file (`mtime` + `size`) supaya otomatis refresh saat file berubah:

```php
// app/Services/BlacklistService.php
private function all(): array
{
    $path = (string) config('security.blacklist_path');
    $signature = md5($path.'|'.(@filemtime($path) ?: 0).'|'.(@filesize($path) ?: 0));

    return Cache::rememberForever(
        'security.blacklist:'.$signature,
        fn (): array => $this->parse($path)
    );
}
```

Service menyediakan `isBlacklistedEmailDomain(?string $email): bool` dan `isBlacklistedIp(?string $ip): bool`. Lihat implementasi lengkap di `app/Services/BlacklistService.php`.

### 3.4 Middleware blokir IP

```php
// app/Http/Middleware/BlockBlacklistedIp.php
class BlockBlacklistedIp
{
    public function __construct(private BlacklistService $blacklist) {}

    public function handle(Request $request, Closure $next): Response
    {
        if ($this->blacklist->isBlacklistedIp($request->ip())) {
            return response()->json([
                'status' => false,
                'message' => 'Akses ditolak.',
            ], 403);
        }

        return $next($request);
    }
}
```

Daftarkan **paling awal** di grup `api` agar request dari IP blacklist tidak menghabiskan kuota rate limit:

```php
// bootstrap/app.php
use App\Http\Middleware\BlockBlacklistedIp;

->withMiddleware(function (Middleware $middleware) {
    $middleware->api(prepend: [
        BlockBlacklistedIp::class,
    ]);
})
```

> ⚠️ Gunakan named argument `prepend:`. Parameter pertama `api()` adalah `$append`.

### 3.5 Validasi domain email di controller

Cek blacklist **setelah** `$request->validate()`, bukan sebagai closure di dalam array rules:

```php
private function ensureEmailDomainNotBlacklisted(string $email): void
{
    if (app(BlacklistService::class)->isBlacklistedEmailDomain($email)) {
        throw ValidationException::withMessages([
            'email' => ['Unknown occurs'],
        ]);
    }
}

// pemakaian
$validatedData = $request->validate([
    'name' => 'required|string|max:255',
    'email' => 'required|string|email|max:255|unique:users',
    'password' => 'required|string|min:8',
    'password_confirmation' => 'required|same:password',
]);

$this->ensureEmailDomainNotBlacklisted($validatedData['email']);
```

**Kenapa bukan closure di dalam array rules?** Generator dokumentasi API (Scramble) mengevaluasi array rules secara statis dan gagal saat menemukan pemanggilan method seperti `$this->aturanKustom()` (`Call to undefined method ...ValidateCallExtractor::...`). Menaruh cek di luar array rules menghindari masalah ini tanpa mengubah bentuk respons.

Pesan **`Unknown occurs`** sengaja tidak menjelaskan alasan penolakan agar penyerang tidak bisa memetakan aturan blacklist.

---

## 4. Anti User Enumeration pada Forgot Password

Jangan pernah memvalidasi `exists:users` atau mengembalikan "email tidak terdaftar" — itu membocorkan email mana yang terdaftar. Selalu balas pesan generik, dan kirim email hanya jika user ada:

```php
$validatedData = $request->validate([
    'email' => 'required|email',
], [
    'email.required' => 'Email wajib diisi',
    'email.email' => 'Format email tidak valid',
]);

$this->ensureEmailDomainNotBlacklisted($validatedData['email']);

if (User::where('email', $validatedData['email'])->exists()) {
    SendResetPasswordEmail::dispatch($validatedData['email']);
}

return response()->json([
    'status' => true,
    'message' => 'Jika email terdaftar, link reset password akan dikirim.',
], 200);
```

---

## 5. Testing

Buat feature test yang mengunci perilaku. File blacklist diarahkan ke file sementara lewat config supaya tidak menyentuh file asli:

```php
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

public function test_login_diblokir_setelah_lima_kegagalan(): void
{
    $user = User::factory()->create(['password' => bcrypt('rahasia123')]);

    for ($attempt = 0; $attempt < 5; $attempt++) {
        $this->postJson('/api/login', ['email' => $user->email, 'password' => 'salah'])
            ->assertStatus(401);
    }

    $this->postJson('/api/login', ['email' => $user->email, 'password' => 'salah'])
        ->assertStatus(429)
        ->assertHeader('Retry-After');
}
```

Skenario minimum yang sebaiknya diuji: limit login tercapai, limiter **reset** setelah login sukses, limit register, limit forgot per email, forgot tidak membocorkan email tidak terdaftar, domain email blacklist ditolak, domain normal tetap lolos, IP blacklist ditolak.

> `Cache::flush()` di `setUp` penting: state `RateLimiter` disimpan di cache store, jadi harus bersih antar-test.

---

## 6. Checklist Adopsi ke Proyek Lain

1. Definisikan named limiter di `AppServiceProvider::boot()` (`RateLimiter::for(...)`).
2. Pasang `->middleware('throttle:namaLimiter')` pada route autentikasi.
3. Untuk login, pakai `RateLimiter` manual: `hit()` hanya saat gagal, `clear()` saat sukses; key `email|ip` + jaring per IP.
4. Tambahkan render `ThrottleRequestsException` → 429 + header `Retry-After` di `bootstrap/app.php`.
5. Tambahkan `config/security.php` + `blacklist.txt` (gitignored) + `BlacklistService`.
6. Tambahkan middleware blokir IP dan `prepend` ke grup `api`.
7. Cek domain email blacklist di luar array rules `$request->validate()`.
8. Hapus `exists:users` pada forgot-password dan pakai pesan generik.
9. Tulis feature test dengan `Cache::flush()` di `setUp`.
10. Pastikan `CACHE_STORE` produksi solid (lihat catatan di bawah).

---

## 7. Catatan Operasional & Trade-off

1. **Cache store menentukan efektivitas limiter.** `RateLimiter` bergantung pada cache. Driver `array`/`file`/`database` aman untuk **single node**; di multi-server pakai Redis agar counter konsisten. Wajib diisi juga `Cache::flush()` pada test.
2. **`trustProxies('*')` berisiko.** Jika aplikasi memercayai semua proxy, `$request->ip()` bisa dipalsukan lewat header `X-Forwarded-For`, sehingga limiter dan blacklist IP dapat di-bypass. Kunci ke IP proxy/CDN nyata (lihat `bootstrap/app.php`).
3. **Blacklist IP ≠ silver bullet.** Pengguna sah di belakang NAT/operator seluler bisa ikut terblokir. Idealnya pertahanan IP juga dipasang di level reverse proxy/CDN (nginx, Cloudflare, fail2ban).
4. **Exact match IP.** Implementasi ini belum mendukung CIDR/range. Tambahkan parser CIDR bila diperlukan.
5. **Jangan bocorkan alasan penolakan.** Baik user enumeration maupun alasan blacklist sebaiknya memakai pesan seragam.
6. **Logging disarankan.** Catat kejadian 429 dan hit blacklist ke log/audit trail agar bisa ditindaklanjuti.
7. **Pembersihan cache signature.** Cache di-key per signature file; versi lama akan kedaluwarsa alami pada `rememberForever`. Untuk file yang sering berubah, pertimbangkan TTL pendek alih-alih `rememberForever`.

---

## Lampiran: Konfigurasi & File

**`.env`**
```dotenv
# Opsional: lokasi file blacklist (default: root/blacklist.txt)
# SECURITY_BLACKLIST_PATH=
```

**`.gitignore`**
```
/blacklist.txt
```

**Contoh respons**
```json
// 429
{
  "status": false,
  "message": "Terlalu banyak permintaan. Coba lagi dalam 42 detik.",
  "retry_after": 42
}

// 422 (domain email blacklist)
{
  "status": false,
  "message": "Validation failed",
  "errors": { "email": ["Unknown occurs"] }
}

// 403 (IP blacklist)
{
  "status": false,
  "message": "Akses ditolak."
}
```
