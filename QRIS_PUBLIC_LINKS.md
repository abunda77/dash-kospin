# QRIS Public Generator - Links & Integration

## 🔗 Public Links

### Main Generator Page

```
http://localhost:8000/qris-generator
```

### Production URL

```
https://your-domain.com/qris-generator
```

## 📱 Share Links

### WhatsApp

```
https://wa.me/?text=Generate%20QRIS%20Dinamis%20di%20http://localhost:8000/qris-generator
```

### Email

```
mailto:?subject=QRIS Generator&body=Gunakan QRIS Generator di http://localhost:8000/qris-generator
```

### QR Code untuk Link

Generate QR code yang mengarah ke halaman generator:

```
https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=http://localhost:8000/qris-generator
```

## 🎨 Integration Options

### 1. Add to Welcome Page

Edit `resources/views/welcome.blade.php`:

```blade
<div class="mt-8">
    <a href="{{ route('qris.public-generator') }}"
       class="inline-block px-6 py-3 bg-white text-blue-900 rounded-lg hover:bg-gray-100 transition">
        🔗 QRIS Generator
    </a>
</div>
```

### 2. Add to Navigation Menu

If you have a public navigation:

```blade
<nav>
    <a href="{{ route('qris.public-generator') }}">QRIS Generator</a>
</nav>
```

### 3. Add to Footer

```blade
<footer>
    <div class="links">
        <a href="{{ route('qris.public-generator') }}">QRIS Generator</a>
    </div>
</footer>
```

### 4. Create Shortlink

Add to `routes/web.php`:

```php
Route::redirect('/qris', '/qris-generator');
Route::redirect('/generate-qris', '/qris-generator');
Route::redirect('/qr', '/qris-generator');
```

## 🖼️ Embed Options

### 1. iFrame Embed

```html
<iframe
    src="http://localhost:8000/qris-generator"
    width="100%"
    height="800px"
    frameborder="0"
>
</iframe>
```

### 2. Modal/Popup

```html
<button onclick="openQrisGenerator()">Generate QRIS</button>

<script>
    function openQrisGenerator() {
        window.open(
            "http://localhost:8000/qris-generator",
            "QRIS Generator",
            "width=1200,height=800"
        );
    }
</script>
```

## 📊 Analytics Integration

### Google Analytics

Add to `resources/views/layouts/public.blade.php`:

```html
<!-- Google Analytics -->
<script
    async
    src="https://www.googletagmanager.com/gtag/js?id=GA_MEASUREMENT_ID"
></script>
<script>
    window.dataLayer = window.dataLayer || [];
    function gtag() {
        dataLayer.push(arguments);
    }
    gtag("js", new Date());
    gtag("config", "GA_MEASUREMENT_ID");
</script>
```

### Track Events

```javascript
// Track QRIS generation
gtag("event", "generate_qris", {
    event_category: "QRIS",
    event_label: "Dynamic QRIS Generated",
    value: amount,
});
```

## 🔐 Security Enhancements

### 1. Add Rate Limiting

Edit `routes/web.php`:

```php
Route::get('/qris-generator', App\Livewire\QrisPublicGenerator::class)
    ->middleware('throttle:60,1') // 60 requests per minute
    ->name('qris.public-generator');
```

### 2. Add CAPTCHA (Optional)

Install package:

```bash
composer require anhskohbo/no-captcha
```

Add to form:

```blade
{!! NoCaptcha::renderJs() !!}
{!! NoCaptcha::display() !!}
```

### 3. Add IP Logging

Create middleware:

```php
// app/Http/Middleware/LogQrisAccess.php
public function handle($request, Closure $next)
{
    \Log::info('QRIS Generator accessed', [
        'ip' => $request->ip(),
        'user_agent' => $request->userAgent(),
        'timestamp' => now()
    ]);

    return $next($request);
}
```

## 📱 Mobile App Integration

### Deep Link

```
yourapp://qris-generator
```

### API Endpoint (Optional)

Create API version:

```php
// routes/api.php
Route::post('/v1/qris/generate', [QrisApiController::class, 'generate'])
    ->middleware('throttle:30,1');
```

```php
// app/Http/Controllers/QrisApiController.php
public function generate(Request $request)
{
    $validated = $request->validate([
        'static_qris' => 'required|string',
        'amount' => 'required|numeric|min:1',
        'fee_type' => 'nullable|in:Rupiah,Persentase',
        'fee_value' => 'nullable|numeric|min:0',
    ]);

    // Generate QRIS
    $component = new \App\Livewire\QrisPublicGenerator();
    $dynamicQris = $component->generateDynamicQris(
        $validated['static_qris'],
        $validated['amount'],
        $validated['fee_type'] ?? 'Rupiah',
        $validated['fee_value'] ?? 0
    );

    return response()->json([
        'success' => true,
        'data' => [
            'dynamic_qris' => $dynamicQris,
            'merchant_name' => $component->parseMerchantName($validated['static_qris']),
        ]
    ]);
}
```

## 🎯 Marketing Materials

### QR Code Poster

Create a QR code that links to the generator:

```
https://api.qrserver.com/v1/create-qr-code/?size=500x500&data=http://localhost:8000/qris-generator
```

### Social Media Posts

**Facebook/Instagram:**

```
🔥 Generate QRIS Dinamis dengan Mudah!
✅ Gratis
✅ Cepat
✅ Aman

Kunjungi: http://localhost:8000/qris-generator
```

**Twitter:**

```
Generate QRIS dinamis dalam hitungan detik!
🚀 http://localhost:8000/qris-generator
#QRIS #DigitalPayment #Indonesia
```

## 📧 Email Template

```html
<!DOCTYPE html>
<html>
    <head>
        <title>QRIS Generator</title>
    </head>
    <body>
        <h1>QRIS Generator Tersedia!</h1>
        <p>Sekarang Anda dapat generate QRIS dinamis dengan mudah.</p>
        <a
            href="http://localhost:8000/qris-generator"
            style="background: #4F46E5; color: white; padding: 12px 24px; text-decoration: none; border-radius: 8px;"
        >
            Buka QRIS Generator
        </a>
    </body>
</html>
```

## 🔔 Notifications

### WhatsApp Notification

```php
// Send via WhatsApp API
$message = "QRIS Generator tersedia di: " . route('qris.public-generator');
// Send using your WhatsApp service
```

### SMS Notification

```php
// Send via SMS gateway
$message = "Generate QRIS dinamis di: " . route('qris.public-generator');
// Send using your SMS service
```

## 📈 Usage Statistics

### Track Usage

Create a simple counter:

```php
// app/Models/QrisGeneratorLog.php
class QrisGeneratorLog extends Model
{
    protected $fillable = ['ip_address', 'amount', 'generated_at'];
}

// In QrisPublicGenerator.php
public function generate()
{
    // ... existing code ...

    QrisGeneratorLog::create([
        'ip_address' => request()->ip(),
        'amount' => $this->amount,
        'generated_at' => now()
    ]);
}
```

### View Statistics

```php
// Total generations
$total = QrisGeneratorLog::count();

// Today's generations
$today = QrisGeneratorLog::whereDate('generated_at', today())->count();

// Total amount
$totalAmount = QrisGeneratorLog::sum('amount');
```

## 🌐 Multi-Language Support

Add language switcher:

```blade
<div class="language-switcher">
    <a href="?lang=id">🇮🇩 Indonesia</a>
    <a href="?lang=en">🇬🇧 English</a>
</div>
```

## 🎨 Custom Branding

### White Label

Customize for different brands:

```php
// config/qris.php
return [
    'branding' => [
        'logo' => env('QRIS_LOGO', '/images/logo.png'),
        'name' => env('QRIS_BRAND_NAME', 'QRIS Generator'),
        'color' => env('QRIS_BRAND_COLOR', '#4F46E5'),
    ]
];
```

## 📱 Progressive Web App (PWA)

Make it installable:

```json
// public/manifest.json
{
    "name": "QRIS Generator",
    "short_name": "QRIS",
    "start_url": "/qris-generator",
    "display": "standalone",
    "background_color": "#ffffff",
    "theme_color": "#4F46E5",
    "icons": [
        {
            "src": "/images/icon-192.png",
            "sizes": "192x192",
            "type": "image/png"
        }
    ]
}
```

## 🔗 Useful Links

### Documentation

-   Main: `QRIS_PUBLIC_GENERATOR.md`
-   Quick Start: `QRIS_PUBLIC_QUICK_START.md`
-   This File: `QRIS_PUBLIC_LINKS.md`

### Testing

-   Test Script: `test-qris-public-generator.php`
-   Manual Test: http://localhost:8000/qris-generator

### Support

-   Laravel Docs: https://laravel.com/docs
-   Livewire Docs: https://livewire.laravel.com
-   Endroid QR: https://github.com/endroid/qr-code

## 🎉 Ready to Share!

Your QRIS Public Generator is ready to be shared with the world! 🚀
