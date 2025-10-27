# QRIS Generator Public - Dokumentasi Lengkap

## 📖 Ringkasan

Halaman public untuk mengubah QRIS static menjadi QRIS dinamis dengan nominal tertentu. Dapat diakses tanpa login dan siap digunakan.

## ✅ Status Implementasi

-   ✅ Livewire component dibuat
-   ✅ Blade view dengan UI modern
-   ✅ Route public terdaftar
-   ✅ Storage directory siap
-   ✅ QR code generation berfungsi
-   ✅ Semua test berhasil
-   ✅ Dokumentasi lengkap

## 🚀 Akses Cepat

### URL Development

```
http://localhost:8000/qris-generator
```

### URL Production

```
https://your-domain.com/qris-generator
```

## 📋 Fitur Lengkap

### Input Features

1. **Pilih QRIS Tersimpan**

    - Dropdown list QRIS yang sudah disimpan di database
    - Auto-fill QRIS string saat dipilih
    - Hanya menampilkan QRIS yang aktif

2. **Input Manual QRIS**

    - Textarea untuk paste QRIS static
    - Validasi format QRIS
    - Support QRIS panjang

3. **Nominal Transaksi**

    - Input numeric dengan prefix "Rp"
    - Validasi minimal Rp 1
    - Format otomatis

4. **Biaya Tambahan**
    - Pilihan tipe: Rupiah atau Persentase
    - Input nilai biaya (opsional)
    - Perhitungan otomatis

### Output Features

1. **QR Code Image**

    - Ukuran 400x400 pixels
    - Format PNG
    - High quality
    - Auto-generated

2. **Informasi Merchant**

    - Parse otomatis dari QRIS
    - Tampilan nama merchant
    - Fallback ke "Merchant" jika tidak ada

3. **QRIS String**

    - Textarea readonly
    - Click to select all
    - Easy copy

4. **Download**
    - Download QR code sebagai PNG
    - Filename dengan timestamp
    - Direct download

### UI/UX Features

1. **Responsive Design**

    - Mobile-friendly
    - Tablet optimized
    - Desktop layout

2. **Real-time Validation**

    - Instant error messages
    - Field-level validation
    - Clear error indicators

3. **Notifications**

    - Success messages
    - Error alerts
    - Info notifications

4. **Form Controls**
    - Generate button
    - Reset button
    - Clear form state

## 🛠️ Teknologi

### Backend

-   **Laravel 11**: Framework PHP
-   **Livewire**: Reactive components
-   **Eloquent ORM**: Database queries

### Frontend

-   **Tailwind CSS**: Styling
-   **Alpine.js**: JavaScript (via Livewire)
-   **Blade**: Templating

### Libraries

-   **Endroid QR Code**: QR generation
-   **Laravel Storage**: File management

## 📁 Struktur File

```
app/
├── Livewire/
│   └── QrisPublicGenerator.php          # Main component
│       ├── Properties (data binding)
│       ├── Validation rules
│       ├── Generate method
│       ├── QRIS parsing
│       ├── CRC16 calculation
│       └── QR image generation
│
└── Models/
    └── QrisStatic.php                   # QRIS database model

resources/
└── views/
    ├── layouts/
    │   └── public.blade.php             # Public layout
    │       ├── HTML structure
    │       ├── Tailwind CSS
    │       ├── Livewire scripts
    │       └── Vite assets
    │
    └── livewire/
        └── qris-public-generator.blade.php  # Component view
            ├── Header section
            ├── Flash messages
            ├── Input form
            ├── Result display
            └── Info section

routes/
└── web.php                              # Route definition
    └── GET /qris-generator

storage/
└── app/
    └── public/
        └── qris-generated/              # QR code storage
            └── qris-public-*.png

public/
└── storage/                             # Symlink to storage
    └── qris-generated/
```

## 🔧 Cara Kerja

### 1. User Input

```
User mengisi form:
- Static QRIS: 00020101021126...5802ID...6304XXXX
- Amount: 50000
- Fee Type: Rupiah
- Fee Value: 1000
```

### 2. Validation

```php
$rules = [
    'static_qris' => 'required|string|min:10',
    'amount' => 'required|numeric|min:1',
    'fee_type' => 'required|in:Rupiah,Persentase',
    'fee_value' => 'nullable|numeric|min:0',
];
```

### 3. QRIS Generation

```
Step 1: Remove CRC (last 4 chars)
Step 2: Change 010211 to 010212 (static to dynamic)
Step 3: Split by '5802ID'
Step 4: Build amount tag (54)
Step 5: Build fee tag (55) if applicable
Step 6: Reconstruct payload
Step 7: Calculate CRC16
Step 8: Append CRC
```

### 4. QR Code Generation

```php
$builder = new Builder(
    writer: new PngWriter,
    data: $dynamicQris,
    size: 400,
    margin: 10,
);
$result = $builder->build();
```

### 5. Storage

```
Save to: storage/app/public/qris-generated/
Filename: qris-public-YmdHis-uniqid.png
Public URL: /storage/qris-generated/filename.png
```

### 6. Display

```
- Show QR image
- Show merchant name
- Show QRIS string
- Enable download
```

## 📊 QRIS Format Detail

### Static QRIS Structure

```
00 02 01 01 02 11 26 ... 58 02 ID ... 63 04 XXXX
│  │  │  │  │  │  │      │  │  │      │  │  │
│  │  │  │  │  │  │      │  │  │      │  │  └─ CRC value
│  │  │  │  │  │  │      │  │  │      │  └─ CRC length
│  │  │  │  │  │  │      │  │  │      └─ CRC tag
│  │  │  │  │  │  │      │  │  └─ Country code
│  │  │  │  │  │  │      │  └─ Country length
│  │  │  │  │  │  │      └─ Country tag
│  │  │  │  │  │  └─ Merchant account info
│  │  │  │  │  └─ Static indicator (11)
│  │  │  │  └─ Point of initiation length
│  │  │  └─ Point of initiation tag
│  │  └─ Payload format length
│  └─ Payload format tag
└─ Start
```

### Dynamic QRIS Structure

```
00 02 01 01 02 12 26 ... 54 05 50000 55 ... 58 02 ID ... 63 04 YYYY
│  │  │  │  │  │  │      │  │  │     │       │  │  │      │  │  │
│  │  │  │  │  │  │      │  │  │     │       │  │  │      │  │  └─ New CRC
│  │  │  │  │  │  │      │  │  │     │       │  │  │      │  └─ CRC length
│  │  │  │  │  │  │      │  │  │     │       │  │  │      └─ CRC tag
│  │  │  │  │  │  │      │  │  │     │       │  │  └─ Country code
│  │  │  │  │  │  │      │  │  │     │       │  └─ Country length
│  │  │  │  │  │  │      │  │  │     │       └─ Country tag
│  │  │  │  │  │  │      │  │  │     └─ Fee info (optional)
│  │  │  │  │  │  │      │  │  └─ Amount value
│  │  │  │  │  │  │      │  └─ Amount length
│  │  │  │  │  │  │      └─ Amount tag
│  │  │  │  │  │  └─ Merchant account info
│  │  │  │  │  └─ Dynamic indicator (12)
│  │  │  │  └─ Point of initiation length
│  │  │  └─ Point of initiation tag
│  │  └─ Payload format length
│  └─ Payload format tag
└─ Start
```

### Tag Reference

| Tag   | Description                  | Example                        |
| ----- | ---------------------------- | ------------------------------ |
| 00    | Payload Format Indicator     | 0002                           |
| 01    | Point of Initiation Method   | 0211 (static) / 0212 (dynamic) |
| 26-51 | Merchant Account Information | Variable                       |
| 52    | Merchant Category Code       | 5204XXXX                       |
| 53    | Transaction Currency         | 5303360 (IDR)                  |
| 54    | Transaction Amount           | 540550000                      |
| 55    | Tip or Convenience Indicator | 55020256...                    |
| 58    | Country Code                 | 5802ID                         |
| 59    | Merchant Name                | 59XX...                        |
| 60    | Merchant City                | 60XX...                        |
| 61    | Postal Code                  | 61XX...                        |
| 62    | Additional Data Field        | 62XX...                        |
| 63    | CRC                          | 6304XXXX                       |

## 🧪 Testing

### Automated Test

```bash
php test-qris-public-generator.php
```

Output:

```
✓ Route registered
✓ Component exists
✓ View exists
✓ Layout exists
✓ Storage ready
✓ Model accessible
✓ CRC16 works
✓ QR library ready
```

### Manual Test Cases

#### Test 1: Basic Generation

```
Input:
- Static QRIS: [paste valid QRIS]
- Amount: 10000
- Fee: 0

Expected:
- ✓ Dynamic QRIS generated
- ✓ QR code displayed
- ✓ Merchant name shown
- ✓ Download available
```

#### Test 2: With Fixed Fee

```
Input:
- Static QRIS: [paste valid QRIS]
- Amount: 50000
- Fee Type: Rupiah
- Fee Value: 2500

Expected:
- ✓ Fee tag included
- ✓ Correct CRC
- ✓ QR scannable
```

#### Test 3: With Percentage Fee

```
Input:
- Static QRIS: [paste valid QRIS]
- Amount: 100000
- Fee Type: Persentase
- Fee Value: 2.5

Expected:
- ✓ Percentage fee tag
- ✓ Correct format
- ✓ Valid QRIS
```

#### Test 4: Validation

```
Input:
- Static QRIS: [empty]
- Amount: [empty]

Expected:
- ✗ Validation errors shown
- ✗ No generation
- ✓ Error messages clear
```

#### Test 5: Reset Form

```
Action:
- Fill form
- Click Reset

Expected:
- ✓ All fields cleared
- ✓ Result hidden
- ✓ Ready for new input
```

## 🐛 Troubleshooting

### Problem: Route Not Found

```bash
# Solution 1: Clear route cache
php artisan route:clear

# Solution 2: Check route list
php artisan route:list | findstr qris

# Solution 3: Restart server
php artisan serve
```

### Problem: QR Code Not Showing

```bash
# Solution 1: Check storage link
php artisan storage:link

# Solution 2: Check permissions
# Windows: Check folder properties
# Linux: chmod -R 775 storage/app/public/qris-generated

# Solution 3: Check logs
type storage\logs\laravel.log
```

### Problem: Component Error

```bash
# Solution 1: Clear all caches
php artisan optimize:clear

# Solution 2: Clear specific caches
php artisan view:clear
php artisan config:clear

# Solution 3: Check component class
php artisan livewire:list
```

### Problem: Invalid QRIS

```
Error: "Format QRIS tidak sesuai"

Solution:
- Pastikan QRIS mengandung '5802ID'
- Pastikan QRIS minimal 4 karakter
- Pastikan QRIS valid (cek dengan scanner)
```

### Problem: CRC Mismatch

```
Error: QR code tidak bisa di-scan

Solution:
- Periksa CRC16 calculation
- Pastikan payload lengkap
- Test dengan QRIS validator
```

## 📈 Monitoring

### Check Logs

```bash
# Real-time logs
tail -f storage/logs/laravel.log

# Windows
Get-Content storage\logs\laravel.log -Wait -Tail 50
```

### Log Entries

```
[timestamp] INFO: Public QR code generated: qris-public-20241027120000-abc123.png
[timestamp] ERROR: Error generating public QR image: [error message]
```

### Storage Usage

```bash
# Check storage size
du -sh storage/app/public/qris-generated

# Windows
Get-ChildItem storage\app\public\qris-generated | Measure-Object -Property Length -Sum
```

### Clean Old Files

```bash
# Delete files older than 7 days
find storage/app/public/qris-generated -name "*.png" -mtime +7 -delete

# Windows
Get-ChildItem storage\app\public\qris-generated -Filter *.png | Where-Object {$_.LastWriteTime -lt (Get-Date).AddDays(-7)} | Remove-Item
```

## 🔐 Security

### Current Security

-   ✅ Input validation
-   ✅ Error handling
-   ✅ Safe file storage
-   ✅ No SQL injection
-   ✅ XSS protection (Blade)

### Recommended Additions

```php
// 1. Rate limiting
Route::get('/qris-generator', ...)
    ->middleware('throttle:60,1');

// 2. IP logging
Log::info('QRIS access', ['ip' => request()->ip()]);

// 3. CAPTCHA (for production)
// Install: composer require anhskohbo/no-captcha

// 4. File cleanup job
// Schedule: php artisan schedule:work
```

## 📚 Dokumentasi Terkait

1. **QRIS_PUBLIC_GENERATOR.md** - Dokumentasi teknis lengkap
2. **QRIS_PUBLIC_QUICK_START.md** - Panduan cepat
3. **QRIS_PUBLIC_LINKS.md** - Link dan integrasi
4. **test-qris-public-generator.php** - Script testing

## 🎯 Next Steps

### Immediate

-   [x] Create component
-   [x] Create view
-   [x] Add route
-   [x] Test functionality
-   [x] Write documentation

### Short Term

-   [ ] Add rate limiting
-   [ ] Add usage analytics
-   [ ] Add transaction logging
-   [ ] Add email sharing

### Long Term

-   [ ] Create API endpoint
-   [ ] Add mobile app support
-   [ ] Add custom QR styling
-   [ ] Add QRIS validation
-   [ ] Add expiry time

## 💡 Tips & Tricks

### Tip 1: Batch Generation

Untuk generate multiple QRIS, gunakan API endpoint (coming soon)

### Tip 2: Custom Styling

Edit `resources/views/livewire/qris-public-generator.blade.php` untuk custom UI

### Tip 3: Branding

Tambahkan logo di header untuk white-label solution

### Tip 4: Analytics

Integrate Google Analytics untuk track usage

### Tip 5: Backup

Backup generated QR codes secara berkala

## 🎉 Kesimpulan

QRIS Public Generator sudah siap digunakan dengan fitur lengkap:

-   ✅ Generate QRIS dinamis
-   ✅ QR code image
-   ✅ Download functionality
-   ✅ Responsive design
-   ✅ Error handling
-   ✅ Documentation complete

**Akses sekarang:** http://localhost:8000/qris-generator

Happy generating! 🚀
