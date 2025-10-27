# QRIS Public Generator - Quick Start

## 🚀 Akses Cepat

### URL

```
http://localhost:8000/qris-generator
```

Atau di production:

```
https://your-domain.com/qris-generator
```

## ✅ Status

-   ✓ Route registered
-   ✓ Livewire component ready
-   ✓ View created
-   ✓ Storage configured
-   ✓ QR Code library installed
-   ✓ All tests passed

## 📋 Cara Penggunaan

### 1. Akses Halaman

Buka browser dan kunjungi `/qris-generator`

### 2. Pilih QRIS

**Opsi A:** Pilih dari dropdown QRIS tersimpan
**Opsi B:** Paste manual QRIS static code

### 3. Isi Form

-   **Nominal**: Masukkan jumlah transaksi (contoh: 50000)
-   **Tipe Biaya**: Pilih Rupiah atau Persentase
-   **Nilai Biaya**: Masukkan biaya jika ada (opsional)

### 4. Generate

Klik tombol "Generate QRIS"

### 5. Hasil

-   QR Code akan muncul di sebelah kanan
-   Nama merchant ditampilkan
-   QRIS string dapat di-copy
-   Download QR code sebagai PNG

## 🎯 Fitur Utama

### Input

-   ✅ Dropdown QRIS tersimpan
-   ✅ Manual input QRIS static
-   ✅ Nominal transaksi
-   ✅ Biaya (Rupiah/Persentase)
-   ✅ Real-time validation

### Output

-   ✅ QR Code image (400x400px)
-   ✅ Merchant name
-   ✅ QRIS dynamic string
-   ✅ Download button
-   ✅ Copy to clipboard

### UI/UX

-   ✅ Responsive design
-   ✅ Modern interface
-   ✅ Error handling
-   ✅ Success notifications
-   ✅ Reset form

## 📁 File Structure

```
app/
├── Livewire/
│   └── QrisPublicGenerator.php          # Main component
└── Models/
    └── QrisStatic.php                   # QRIS data model

resources/
└── views/
    ├── layouts/
    │   └── public.blade.php             # Public layout
    └── livewire/
        └── qris-public-generator.blade.php  # Component view

routes/
└── web.php                              # Route definition

storage/
└── app/
    └── public/
        └── qris-generated/              # QR code storage
```

## 🔧 Technical Details

### Route

```php
Route::get('/qris-generator', App\Livewire\QrisPublicGenerator::class)
    ->name('qris.public-generator');
```

### Component Properties

```php
public $saved_qris = '';      // Selected QRIS ID
public $static_qris = '';     // QRIS static string
public $amount = '';          // Transaction amount
public $fee_type = 'Rupiah';  // Fee type
public $fee_value = '0';      // Fee value
public $dynamicQris = null;   // Generated QRIS
public $merchantName = null;  // Parsed merchant name
public $qrImageUrl = null;    // QR image URL
```

### Validation Rules

```php
'static_qris' => 'required|string|min:10'
'amount' => 'required|numeric|min:1'
'fee_type' => 'required|in:Rupiah,Persentase'
'fee_value' => 'nullable|numeric|min:0'
```

## 🧪 Testing

### Run Test Script

```bash
php test-qris-public-generator.php
```

### Manual Testing

1. Visit `/qris-generator`
2. Select or paste QRIS
3. Enter amount: 10000
4. Click Generate
5. Verify QR code displayed
6. Test download
7. Test reset

## 🐛 Troubleshooting

### QR Code Not Showing

```bash
# Check storage link
php artisan storage:link

# Check permissions
chmod -R 775 storage/app/public/qris-generated
```

### Route Not Found

```bash
# Clear route cache
php artisan route:clear

# Check route list
php artisan route:list | grep qris
```

### Component Error

```bash
# Clear view cache
php artisan view:clear

# Clear config cache
php artisan config:clear
```

## 📊 Example Usage

### Example 1: Basic Transaction

```
Static QRIS: 00020101021126...5802ID...6304XXXX
Amount: 50000
Fee Type: Rupiah
Fee Value: 0

Result: Dynamic QRIS with Rp 50,000
```

### Example 2: With Fixed Fee

```
Static QRIS: 00020101021126...5802ID...6304XXXX
Amount: 100000
Fee Type: Rupiah
Fee Value: 2500

Result: Dynamic QRIS with Rp 100,000 + Rp 2,500 fee
```

### Example 3: With Percentage Fee

```
Static QRIS: 00020101021126...5802ID...6304XXXX
Amount: 200000
Fee Type: Persentase
Fee Value: 2.5

Result: Dynamic QRIS with Rp 200,000 + 2.5% fee
```

## 🔐 Security Notes

-   ✅ Input validation
-   ✅ Error handling
-   ✅ Safe file storage
-   ✅ No sensitive data exposure
-   ⚠️ Public access (no auth required)
-   💡 Consider adding rate limiting for production

## 📝 Next Steps

### Optional Enhancements

1. Add rate limiting middleware
2. Add transaction logging
3. Add email/WhatsApp sharing
4. Add custom QR styling
5. Add QRIS expiry time
6. Add API endpoint

### Rate Limiting Example

```php
Route::get('/qris-generator', App\Livewire\QrisPublicGenerator::class)
    ->middleware('throttle:60,1') // 60 requests per minute
    ->name('qris.public-generator');
```

## 📞 Support

### Check Logs

```bash
tail -f storage/logs/laravel.log
```

### Debug Mode

Set in `.env`:

```
APP_DEBUG=true
```

### Clear All Caches

```bash
php artisan optimize:clear
```

## ✨ Features Comparison

| Feature             | Admin Panel | Public Page  |
| ------------------- | ----------- | ------------ |
| Authentication      | Required    | Not Required |
| QRIS Management     | Full CRUD   | Read Only    |
| Generate Dynamic    | ✓           | ✓            |
| Download QR         | ✓           | ✓            |
| Save to Database    | ✓           | ✗            |
| Transaction History | ✓           | ✗            |
| User Management     | ✓           | ✗            |

## 🎉 Success!

Halaman QRIS Public Generator sudah siap digunakan!

Akses di: **http://localhost:8000/qris-generator**

Happy generating! 🚀
