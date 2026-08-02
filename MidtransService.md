# Dokumentasi Service Midtrans & Payment Methods

Dokumentasi ini mencakup seluruh ekosistem pembayaran Midtrans pada project Laravel 11 / Filament POS, mulai dari arsitektur service, konfigurasi, integrasi checkout, webhook handler, hingga manajemen payment methods di admin panel.

---

## Daftar Isi

1. [Arsitektur Payment Gateway](#1-arsitektur-payment-gateway)
2. [Konfigurasi Environment](#2-konfigurasi-environment)
3. [PaymentGatewayInterface](#3-paymentgatewayinterface)
4. [MidtransGateway](#4-midtransgateway)
5. [PaymentGatewayFactory](#5-paymentgatewayfactory)
6. [PaymentServiceProvider](#6-paymentserviceprovider)
7. [Model PaymentMethod & Database Schema](#7-model-paymentmethod--database-schema)
8. [Filament Admin: PaymentMethodResource](#8-filament-admin-paymentmethodresource)
9. [Integrasi Checkout](#9-integrasi-checkout)
10. [Webhook / Notification Handler](#10-webhook--notification-handler)
11. [Route Structure](#11-route-structure)
12. [Cara Menambahkan Gateway Baru](#12-cara-menambahkan-gateway-baru)

---

## 1. Arsitektur Payment Gateway

```
┌──────────────────────────────────────────────────────────────────────┐
│                       FILAMENT ADMIN PANEL                           │
│  /admin/payment-methods                                              │
│  ┌──────────────────────────────────────────────────────────────┐    │
│  │ PaymentMethodResource (CRUD)                                  │    │
│  │ - name, image, is_cash, gateway, gateway_config, is_active    │    │
│  └──────────────────────────────────────────────────────────────┘    │
└──────────────────────────────────────────────────────────────────────┘
                                    │
                                    ▼
┌──────────────────────────────────────────────────────────────────────┐
│                        CHECKOUT FLOW                                 │
│  CheckoutController                                                  │
│  ┌──────────────────────────────────────────────────────────────┐    │
│  │ 1. User pilih PaymentMethod (by payment_method_id)            │    │
│  │ 2. Jika paymentMethod->gateway tidak null:                    │    │
│  │    → PaymentGatewayFactory::make('midtrans')                  │    │
│  │    → MidtransGateway::createTransaction(params)               │    │
│  │ 3. Jika tidak ada gateway → redirect ke thank-you             │    │
│  └──────────────────────────────────────────────────────────────┘    │
└──────────────────────────────────────────────────────────────────────┘
                                    │
                    ┌───────────────┴───────────────┐
                    ▼                               ▼
┌───────────────────────────────┐   ┌───────────────────────────────────┐
│   MIDTRANS API                │   │   WEBHOOK / NOTIFICATION           │
│   (CoreApi::charge)           │   │   POST /api/midtrans/notification   │
│                               │   │   → WebhookController::handle()    │
│   Return: redirect_url,       │   │   → Verifikasi signature SHA512    │
│   token, actions, dll.        │   │   → Update Order status            │
└───────────────────────────────┘   └───────────────────────────────────┘
```

**Pola Desain:** Strategy Pattern + Factory Pattern

- `PaymentGatewayInterface` — kontrak untuk semua gateway
- `MidtransGateway` / `XenditGateway` — implementasi konkret
- `PaymentGatewayFactory` — factory untuk membuat instance gateway berdasarkan string key

---

## 2. Konfigurasi Environment

### 2.1 File `.env`

Tambahkan variabel environment berikut:

```env
MIDTRANS_SERVER_KEY=SB-Mid-server-xxxxxxxxxxxx
MIDTRANS_CLIENT_KEY=SB-Mid-client-xxxxxxxxxxxx
MIDTRANS_IS_PRODUCTION=false
MIDTRANS_MERCHANT_ID=G123456789
```

- `MIDTRANS_SERVER_KEY` — Server Key dari Midtrans Dashboard (digunakan untuk API call server-side)
- `MIDTRANS_CLIENT_KEY` — Client Key dari Midtrans Dashboard (digunakan untuk Snap.js di frontend)
- `MIDTRANS_IS_PRODUCTION` — `true` untuk production, `false` untuk Sandbox
- `MIDTRANS_MERCHANT_ID` — Merchant ID dari Midtrans Dashboard

### 2.2 File `config/services.php`

```php
'midtrans' => [
    'server_key'   => env('MIDTRANS_SERVER_KEY', ''),
    'client_key'   => env('MIDTRANS_CLIENT_KEY', ''),
    'is_production' => env('MIDTRANS_IS_PRODUCTION', false),
    'merchant_id'  => env('MIDTRANS_MERCHANT_ID', ''),
],
```

Factory membaca konfigurasi dari sini: `config('services.midtrans')`.

---

## 3. PaymentGatewayInterface

**Lokasi:** `app/Services/Payment/PaymentGatewayInterface.php`

```php
interface PaymentGatewayInterface
{
    public function createTransaction(array $params);
    public function getTransactionStatus($transactionId);
    public function cancelTransaction($transactionId);
    public function notificationHandler(array $payload);
}
```

Semua gateway payment harus mengimplementasikan 4 method ini:

| Method | Deskripsi |
|---|---|
| `createTransaction(array $params)` | Membuat transaksi baru di gateway |
| `getTransactionStatus($transactionId)` | Mengecek status transaksi |
| `cancelTransaction($transactionId)` | Membatalkan transaksi |
| `notificationHandler(array $payload)` | Memproses notifikasi/webhook dari gateway |

---

## 4. MidtransGateway

**Lokasi:** `app/Services/Payment/MidtransGateway.php`

### 4.1 Inisialisasi

```php
public function __construct(array $config)
{
    $this->config = $config;
    $this->init();
}

protected function init()
{
    Config::$serverKey    = $this->config['server_key'];
    Config::$isProduction = (bool) $this->config['is_production'];
    Config::$isSanitized  = true;
    Config::$is3ds        = true;
}
```

Saat diinstansiasi oleh factory, constructor menerima array konfigurasi dari `config/services.php` dan langsung menginisialisasi Midtrans SDK global config (`\Midtrans\Config`).

### 4.2 Method

#### `createTransaction(array $params)`

Membuat transaksi via `CoreApi::charge()`. Parameter harus mengikuti format Midtrans:

```php
$params = [
    'payment_type'        => 'bank_transfer',
    'transaction_details' => [
        'order_id'     => 'ORDER-001',
        'gross_amount' => 150000,
    ],
    'customer_details' => [
        'first_name' => 'John',
        'phone'      => '08123456789',
    ],
    'item_details' => [
        ['id' => '1', 'price' => 50000, 'quantity' => 2, 'name' => 'Produk A'],
        ['id' => '2', 'price' => 50000, 'quantity' => 1, 'name' => 'Produk B'],
    ],
    'bank_transfer' => ['bank' => 'bca'],
    'callbacks' => [
        'finish'   => route('payment.finish'),
        'unfinish' => route('payment.unfinish'),
        'error'    => route('payment.error'),
    ],
];

$response = $gateway->createTransaction($params);
// Response berisi: redirect_url, transaction_id, actions[], dll.
```

#### `getTransactionStatus($transactionId)`

Cek status transaksi via `Transaction::status()`.

```php
$status = $gateway->getTransactionStatus('ORDER-001');
// $status->transaction_status → 'settlement', 'pending', 'expire', dll.
```

#### `cancelTransaction($transactionId)`

Membatalkan transaksi via `Transaction::cancel()`.

#### `notificationHandler(array $payload)`

Mengembalikan instance `\Midtrans\Notification` dari payload webhook.

---

## 5. PaymentGatewayFactory

**Lokasi:** `app/Services/Payment/PaymentGatewayFactory.php`

```php
class PaymentGatewayFactory
{
    public function make(string $gateway): PaymentGatewayInterface
    {
        switch (strtolower($gateway)) {
            case 'midtrans':
                $config = config('services.midtrans');
                return new MidtransGateway($config);
            case 'xendit':
                $config = config('services.xendit');
                return new XenditGateway($config);
            default:
                throw new \InvalidArgumentException("Unsupported payment gateway [{$gateway}]");
        }
    }
}
```

**Cara Penggunaan:**

```php
use App\Services\Payment\PaymentGatewayFactory;

$factory = app(PaymentGatewayFactory::class);
$gateway = $factory->make('midtrans');
```

Atau via dependency injection di controller:

```php
public function __construct(PaymentGatewayFactory $paymentGatewayFactory)
{
    $this->paymentGatewayFactory = $paymentGatewayFactory;
}
```

**Key gateway yang didukung factory:**
- `midtrans` → `MidtransGateway`
- `xendit` → `XenditGateway`

> Catatan: Form Filament menampilkan opsi `duitku`, tetapi factory belum memiliki implementasi `DuitkuGateway`.

---

## 6. PaymentServiceProvider

**Lokasi:** `app/Services/Payment/PaymentServiceProvider.php`

```php
class PaymentServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(PaymentGatewayFactory::class, function ($app) {
            return new PaymentGatewayFactory();
        });
    }
}
```

Mendaftarkan `PaymentGatewayFactory` sebagai singleton di service container Laravel.

> Pastikan provider ini terdaftar di `bootstrap/providers.php`.

---

## 7. Model PaymentMethod & Database Schema

### 7.1 Model

**Lokasi:** `app/Models/PaymentMethod.php`

```php
class PaymentMethod extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'image',
        'is_cash',
        'gateway',
        'gateway_config',
        'is_active',
    ];

    protected $appends = ['image_url'];

    protected $casts = [
        'gateway_config' => 'json',
        'is_active'      => 'boolean',
        'is_cash'        => 'boolean',
    ];

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function getImageUrlAttribute()
    {
        return $this->image ? url('storage/' . $this->image) : null;
    }
}
```

### 7.2 Database Schema — Tabel `payment_methods`

| Kolom | Tipe | Keterangan |
|---|---|---|
| `id` | `bigint unsigned` (PK, AI) | Primary key |
| `name` | `varchar(255)` | Nama metode pembayaran (contoh: "BCA Virtual Account") |
| `image` | `varchar(255)` | Path gambar (disimpan di storage) |
| `is_cash` | `tinyint(1)` (default: 0) | Flag pembayaran tunai |
| `gateway` | `varchar(255)` (nullable) | Key gateway: `midtrans`, `xendit`, `duitku`, atau null untuk manual |
| `gateway_config` | `json` (nullable) | Konfigurasi tambahan per gateway (misal: `{"payment_type": "credit_card"}`) |
| `is_active` | `tinyint(1)` (default: 1) | Status aktif/tidak |
| `created_at` | `timestamp` | Waktu pembuatan |
| `updated_at` | `timestamp` | Waktu update |
| `deleted_at` | `timestamp` | Soft delete (kolom sudah ada, trait belum aktif di model) |

### 7.3 Relasi dengan Order

Model `Order` memiliki relasi `paymentMethod()`:

```php
// app/Models/Order.php
public function paymentMethod(): BelongsTo
{
    return $this->belongsTo(PaymentMethod::class);
}
```

---

## 8. Filament Admin: PaymentMethodResource

**Lokasi:** `app/Filament/Resources/PaymentMethodResource.php`

**URL Admin:** `/admin/payment-methods`

### 8.1 Konfigurasi Resource

```php
class PaymentMethodResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model          = PaymentMethod::class;
    protected static ?string $navigationIcon = 'heroicon-m-newspaper';
    protected static ?int    $navigationSort = 5;
    protected static ?string $navigationLabel = 'Metode Pembayaran';
    protected static ?string $navigationGroup = 'Menejemen keuangan';

    public static function getPermissionPrefixes(): array
    {
        return [
            'view_any',
            'create',
            'update',
            'delete_any',
        ];
    }
}
```

### 8.2 Form Fields

| Field | Komponen | Keterangan |
|---|---|---|
| `name` | `TextInput` | Nama metode pembayaran, required, max 255 karakter |
| `image` | `FileUpload` | Upload gambar (logo bank/e-wallet), required |
| `is_cash` | `Toggle` | Tandai sebagai pembayaran tunai, label "Pembayaran Tunai" |
| `gateway` | `Select` | Pilihan gateway: Manual (null), Midtrans, Xendit, Duitku. Reactive — reset `gateway_config` saat berubah |
| `gateway_config` | `KeyValue` | Konfigurasi key-value tambahan. Hanya tampil jika `gateway` tidak kosong |
| `is_active` | `Toggle` | Status aktif, default `true` |

### 8.3 Table Columns

- `ImageColumn` — gambar metode pembayaran
- `TextColumn` — nama (searchable)
- `IconColumn` — is_cash (boolean)
- `TextColumn` — gateway (label)
- `IconColumn` — is_active (boolean, label "Aktif?")
- `TextColumn` — created_at, updated_at, deleted_at (datetime, hidden by default)

### 8.4 Halaman (Pages)

| Halaman | File |
|---|---|
| List | `app/Filament/Resources/PaymentMethodResource/Pages/ListPaymentMethods.php` |
| Create | `app/Filament/Resources/PaymentMethodResource/Pages/CreatePaymentMethod.php` |
| Edit | `app/Filament/Resources/PaymentMethodResource/Pages/EditPaymentMethod.php` |

### 8.5 Policy (FilamentShield)

**Lokasi:** `app/Policies/PaymentMethodPolicy.php`

Permission string menggunakan format `payment::method`:

```
view_any_payment::method
view_payment::method
create_payment::method
update_payment::method
delete_payment::method
delete_any_payment::method
force_delete_payment::method
force_delete_any_payment::method
restore_payment::method
restore_any_payment::method
replicate_payment::method
reorder_payment::method
```

Resource hanya menggunakan 4 prefix (`view_any`, `create`, `update`, `delete_any`) via `getPermissionPrefixes()`.

### 8.6 Contoh Data Payment Method

| name | gateway | gateway_config | is_cash |
|---|---|---|---|
| Tunai | `null` | `null` | `true` |
| BCA Virtual Account | `midtrans` | `{"payment_type": "bank_transfer", "bank": "bca"}` | `false` |
| GoPay | `midtrans` | `{"payment_type": "gopay"}` | `false` |
| Kartu Kredit | `midtrans` | `{"payment_type": "credit_card"}` | `false` |

---

## 9. Integrasi Checkout

**Lokasi:** `app/Http/Controllers/CheckoutController.php`

### 9.1 Alur Checkout dengan Gateway

```
1. User mengisi form checkout + pilih payment_method_id
2. CheckoutController::process() dipanggil
3. Order dibuat via DB::transaction() dengan Order::generateNextOrderNumber()
4. Jika paymentMethod->gateway tidak null:
   a. PaymentGatewayFactory::make(gateway) → dapatkan instance gateway
   b. Bangun $transactionParams (transaction_details, customer_details, item_details, callbacks)
   c. Tentukan payment_type berdasarkan request atau gateway_config
   d. MidtransGateway::createTransaction($transactionParams)
   e. Simpan payment_details & payment_url ke order
   f. Redirect user ke paymentUrl (redirect_url Midtrans atau thank-you page)
5. Jika tidak ada gateway (pembayaran manual/tunai):
   → Redirect langsung ke thank-you page
```

### 9.2 Payment Type yang Didukung (Midtrans)

| Payment Type | Keterangan |
|---|---|
| `bank_transfer` | Transfer bank (BCA, BNI, BRI, Mandiri, Permata) |
| `credit_card` | Kartu kredit (menggunakan `CoreApi::cardToken()`) |
| `gopay` | GoPay QR / deeplink |

### 9.3 Struktur Parameter Transaksi

```php
$transactionParams = [
    'transaction_details' => [
        'order_id'     => $order->no_order,    // Format: 000001
        'gross_amount' => $total,
    ],
    'customer_details' => [
        'first_name'     => $order->name,
        'phone'          => $order->whatsapp,
        'billing_address' => ['address' => $order->address],
    ],
    'item_details' => [
        ['id' => '1', 'price' => 50000, 'quantity' => 2, 'name' => 'Produk A'],
        ['id' => 'DISCOUNT', 'price' => -10000, 'quantity' => 1, 'name' => 'Discount'],
    ],
    'callbacks' => [
        'finish'   => route('payment.finish'),
        'unfinish' => route('payment.unfinish'),
        'error'    => route('payment.error'),
    ],
];

// Spesifik Midtrans:
$transactionParams['payment_type'] = 'bank_transfer';
$transactionParams['bank_transfer'] = ['bank' => 'bca'];
```

### 9.4 Get Card Token (Credit Card)

Method `getCardToken()` memanggil `CoreApi::cardToken()` untuk mendapatkan token kartu kredit:

```php
$response = \Midtrans\CoreApi::cardToken(
    $cardNumber,
    $cardExpMonth,
    $cardExpYear,
    $cardCvv
);
return $response->token_id;
```

> **TODO:** Logic ini masih berada di controller. Sebaiknya dipindahkan ke `MidtransGateway` dengan tambahan method `getCardToken()`.

---

## 10. Webhook / Notification Handler

### 10.1 WebhookController (Utama)

**Lokasi:** `app/Http/Controllers/Api/WebhookController.php`

**Route:** `POST /api/midtrans/notification`

### 10.2 Alur Webhook

```
1. Midtrans mengirim POST ke /api/midtrans/notification
2. WebhookController::handle() menerima payload
3. Validasi signature_key:
   hash('sha512', order_id + status_code + gross_amount + server_key)
4. Cari Order melalui 3 strategi lookup:
   → Exact match no_order
   → UUID match pada id
   → Split by hyphen (backward compat: 000001-123456)
5. Mapping transaction_status → order status:

   | transaction_status | fraud_status | order status |
   |-------------------|--------------|--------------|
   | settlement         | -            | completed    |
   | capture            | accept       | completed    |
   | expire             | -            | expired      |
   | cancel             | -            | cancelled    |
   | deny               | -            | cancelled    |

6. Simpan order status baru
7. Return response sesuai format Midtrans notification
```

### 10.3 CheckoutController::handleNotification (Deprecated)

Method `handleNotification()` juga ada di `CheckoutController` dan menggunakan `PaymentGatewayFactory` untuk mendapatkan notification handler. Namun route-nya sudah deprecated:

```php
// routes/api.php - baris 42-43
// DEPRECATED: Midtrans notification handler dalam CheckoutController
// Route::post('payment/notification/midtrans', [CheckoutController::class, 'handleNotification']);
```

Gunakan `WebhookController` sebagai handler utama.

### 10.4 Payment Finish & Error Redirect

| Route | Controller Method | Keterangan |
|---|---|---|
| `route('payment.finish')` | `CheckoutController::finishPayment()` | Redirect setelah pembayaran selesai |
| `route('payment.error')` | `CheckoutController::errorPayment()` | Redirect saat terjadi error |

`finishPayment()` menggunakan `PaymentGatewayFactory::make(gateway)` untuk cek status transaksi dan update order.

---

## 11. Route Structure

### 11.1 API Routes (`routes/api.php`)

| Method | Path | Handler | Auth |
|---|---|---|---|
| `GET` | `/api/payment-methods` | `PaymentMethodController@index` | Sanctum |
| `GET` | `/api/payment-methods-public` | `CheckoutUserController@getPaymentMethods` | None |
| `POST` | `/api/midtrans/notification` | `WebhookController@handle` | None |

### 11.2 Web Routes (`routes/web.php`)

Route untuk finish/unfinish/error payment didefinisikan di `routes/web.php`:

```php
Route::get('/payment/finish', [CheckoutController::class, 'finishPayment'])->name('payment.finish');
Route::get('/payment/unfinish', [CheckoutController::class, 'unfinishPayment'])->name('payment.unfinish');
Route::get('/payment/error', [CheckoutController::class, 'errorPayment'])->name('payment.error');
```

### 11.3 Filament Admin Routes

Otomatis oleh Filament:

| Path | Halaman |
|---|---|
| `/admin/payment-methods` | List Payment Methods |
| `/admin/payment-methods/create` | Create |
| `/admin/payment-methods/{record}/edit` | Edit |

---

## 12. Cara Menambahkan Gateway Baru

### Langkah 1: Buat Gateway Class

```php
// app/Services/Payment/DuitkuGateway.php

namespace App\Services\Payment;

use Illuminate\Support\Facades\Log;

class DuitkuGateway implements PaymentGatewayInterface
{
    protected $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function createTransaction(array $params)
    {
        // Implementasi Duitku API
    }

    public function getTransactionStatus($transactionId)
    {
        // Implementasi Duitku API
    }

    public function cancelTransaction($transactionId)
    {
        // Implementasi Duitku API
    }

    public function notificationHandler(array $payload)
    {
        // Handle webhook Duitku
    }
}
```

### Langkah 2: Tambahkan Konfigurasi di `config/services.php`

```php
'duitku' => [
    'merchant_code' => env('DUITKU_MERCHANT_CODE', ''),
    'api_key'       => env('DUITKU_API_KEY', ''),
    'is_production' => env('DUITKU_IS_PRODUCTION', false),
],
```

### Langkah 3: Daftarkan di `PaymentGatewayFactory`

```php
case 'duitku':
    $config = config('services.duitku');
    return new DuitkuGateway($config);
```

### Langkah 4: Tambahkan di Environment

```env
DUITKU_MERCHANT_CODE=Dxxxxx
DUITKU_API_KEY=xxxxxxxxxxxx
DUITKU_IS_PRODUCTION=false
```

### Langkah 5: Opsi gateway sudah ada di form Filament

Form `PaymentMethodResource` sudah memiliki opsi `duitku` di select gateway. Setelah factory mendukung, payment method dengan gateway `duitku` akan langsung berfungsi.

---

## Ringkasan File Terkait

| File | Peran |
|---|---|
| `app/Services/Payment/PaymentGatewayInterface.php` | Kontrak interface semua gateway |
| `app/Services/Payment/MidtransGateway.php` | Implementasi Midtrans |
| `app/Services/Payment/XenditGateway.php` | Implementasi Xendit (stub) |
| `app/Services/Payment/PaymentGatewayFactory.php` | Factory pembuat instance gateway |
| `app/Services/Payment/PaymentServiceProvider.php` | Service provider registrasi factory |
| `app/Models/PaymentMethod.php` | Model PaymentMethod |
| `app/Filament/Resources/PaymentMethodResource.php` | Filament resource CRUD |
| `app/Filament/Resources/PaymentMethodResource/Pages/*.php` | Halaman List/Create/Edit |
| `app/Policies/PaymentMethodPolicy.php` | Policy (FilamentShield) |
| `app/Http/Controllers/CheckoutController.php` | Proses checkout + integrasi gateway |
| `app/Http/Controllers/Api/WebhookController.php` | Webhook handler Midtrans |
| `config/services.php` | Konfigurasi credentials Midtrans |
| `routes/api.php` | API routes (payment-methods, webhook) |
| `routes/web.php` | Web routes (payment finish/error) |
