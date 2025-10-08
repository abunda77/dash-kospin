# Fitur Barcode Tabungan

## Overview
Fitur ini memungkinkan admin untuk mencetak barcode QR Code untuk setiap rekening tabungan. Ketika barcode di-scan, akan menampilkan informasi detail rekening tabungan secara public (tanpa autentikasi).

## Komponen yang Dibuat

### 1. Controller
- **File**: `app/Http/Controllers/TabunganBarcodeController.php`
- **Methods**:
  - `printBarcode($id)`: Generate dan download PDF barcode
  - `scan($id)`: Menampilkan halaman detail tabungan ketika barcode di-scan
  - `testQrCode($id)`: Debug endpoint untuk testing QR code generation

### 2. Routes
- **Print Barcode**: `GET /tabungan/{id}/print-barcode` (route name: `tabungan.print-barcode`)
- **Scan Barcode**: `GET /tabungan/{id}/scan` (route name: `tabungan.scan`)
- **Test QR Code**: `GET /test-qr/{id}` (route name: `tabungan.test-qr`) - Debug only

### 3. Views
- **PDF Barcode**: `resources/views/pdf/tabungan-barcode.blade.php`
- **Scan Page**: `resources/views/tabungan/scan.blade.php`

### 4. Action di Filament
- **File**: `app/Filament/Resources/TabunganResource.php`
- **Action**: "Cetak Barcode" di table actions

## Cara Penggunaan

### 1. Admin Panel
1. Masuk ke halaman Rekening Tabungan
2. Klik action "Cetak Barcode" pada rekening yang diinginkan
3. PDF barcode akan otomatis ter-download

### 2. Scan Barcode
1. Scan QR Code menggunakan aplikasi scanner
2. Akan redirect ke halaman public yang menampilkan detail rekening

## Informasi yang Ditampilkan

### PDF Barcode
- No. Rekening
- Nama Nasabah
- Produk Tabungan
- Saldo
- Status Rekening
- QR Code (200x200 pixels)
- URL scan
- Timestamp cetak

### Halaman Scan
- Informasi Rekening (No. Rekening, Nama, Produk, Saldo, Tanggal Buka, Status)
- Informasi Kontak (Telepon, Email jika ada)
- Timestamp akses
- Responsive design dengan TailwindCSS

## Implementasi Teknis

### QR Code Generation
Aplikasi menggunakan **QRServer API** (https://api.qrserver.com) untuk generate QR code:

```php
$qrCodeUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=' . urlencode($scanUrl);
```

### Temporary File Approach
Untuk mengatasi keterbatasan DOMPDF dalam merender base64 images, kami menggunakan pendekatan temporary file:

**Proses:**
1. Download QR code dari API QRServer
2. Save ke temporary file di `storage/app/temp/`
3. Pass file path ke DOMPDF (bukan base64)
4. Auto cleanup file setelah PDF generated

**Kode Implementation:**
```php
// Save QR code to temporary file
$tempDir = storage_path('app/temp');
if (!file_exists($tempDir)) {
    mkdir($tempDir, 0755, true);
}

$qrCodePath = $tempDir . '/qr_' . $id . '_' . time() . '.png';
file_put_contents($qrCodePath, $qrCodeData);

// Generate PDF
$pdf = Pdf::loadView('pdf.tabungan-barcode', [
    'tabungan' => $tabungan,
    'qrCodePath' => $qrCodePath,
    'scanUrl' => $scanUrl,
    'hasQrCode' => true
]);

// Cleanup after download
$response = $pdf->download($filename);
if (file_exists($qrCodePath)) {
    @unlink($qrCodePath);
}
```

### Keuntungan Temporary File Approach

1. **Lebih Reliable**: DOMPDF lebih baik dalam merender file images daripada base64
2. **Lebih Cepat**: Tidak perlu encode/decode base64
3. **Auto Cleanup**: File temporary otomatis dihapus setelah PDF di-generate
4. **Tetap Menggunakan QRServer API**: Sesuai requirement untuk tetap menggunakan api.qrserver.com

## Testing

### 1. Test QR Code Download
```bash
# Visit debug endpoint
http://localhost:8000/test-qr/3
```

**Expected response:**
```json
{
  "tabungan_id": "3",
  "scan_url": "http://localhost:8000/tabungan/3/scan",
  "qr_api_url": "https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=...",
  "qr_data_fetched": true,
  "qr_data_size": 492,
  "base64_preview": "data:image/png;base64,iVBORw0KG..."
}
```

### 2. Test PDF Generation
1. Login ke Filament admin
2. Buka halaman Rekening Tabungan
3. Klik action "Cetak Barcode" pada rekening
4. PDF akan ter-download dengan QR Code yang tampil

### 3. Check Logs
```bash
# Check Laravel logs
tail -f storage/logs/laravel.log
```

**Expected log:**
```
[2025-10-08 10:09:23] local.INFO: QR Code downloaded successfully 
{"tabungan_id":"4","data_size":492,"temp_path":"C:\\laragon\\www\\dash-kospin\\storage\\app\\temp\\qr_4_1728371363.png"}
```

## File Structure

```
app/
├── Http/
│   └── Controllers/
│       └── TabunganBarcodeController.php
resources/
├── views/
│   ├── pdf/
│   │   └── tabungan-barcode.blade.php
│   └── tabungan/
│       └── scan.blade.php
routes/
└── web.php
storage/
└── app/
    └── temp/              # Temporary QR code files
        └── qr_*.png       # Auto-deleted after PDF generation
```

## Dependencies
- **QRServer API**: External API untuk generate QR Code (https://api.qrserver.com)
- **barryvdh/laravel-dompdf**: Package untuk generate PDF
- **TailwindCSS**: Untuk styling halaman scan

## Security Notes

### ID Obfuscation dengan Hashids
Untuk meningkatkan security, aplikasi menggunakan **Hashids** untuk menyamarkan ID rekening pada URL public:

**Sebelum:**
```
http://localhost:8000/tabungan/1/scan  ❌ ID mudah ditebak
```

**Sesudah:**
```
http://localhost:8000/tabungan/vgoYvMz14k/scan  ✅ ID ter-obfuscate
```

**Keuntungan:**
- ✅ ID tidak sequential, sulit ditebak
- ✅ Minimum 10 karakter
- ✅ Menggunakan APP_KEY sebagai salt (unique per aplikasi)
- ✅ URL-safe characters only
- ✅ Reversible (dapat di-decode kembali)

**Dokumentasi Lengkap:** Lihat [SECURITY_HASHIDS.md](SECURITY_HASHIDS.md)

### General Security
- Halaman scan bersifat **public** (tidak memerlukan autentikasi)
- Hanya menampilkan informasi dasar rekening
- Tidak ada aksi yang dapat dilakukan dari halaman scan
- File PDF barcode dapat di-download langsung
- Temporary files otomatis dihapus setelah PDF generated
- ID rekening di-obfuscate menggunakan Hashids

## Troubleshooting

### QR Code tidak muncul di PDF?
1. **Check logs**: `storage/logs/laravel.log`
2. **Verify temp directory exists**: `storage/app/temp`
3. **Check file permissions**: temp directory harus writable
4. **Test QR API**: `http://localhost:8000/test-qr/{id}`

### Error "Failed to load image"?
- Pastikan `isRemoteEnabled` = true di `config/dompdf.php`
- Check path separator (Windows vs Linux)
- Verify file exists sebelum PDF generation

### QR Code API tidak respond?
- Check internet connection
- Verify QRServer API status: https://api.qrserver.com
- Check firewall/proxy settings

### Temporary files tidak terhapus?
- Check file permissions di `storage/app/temp`
- Manual cleanup: hapus file `qr_*.png` yang lebih dari 1 jam
- Bisa tambahkan scheduled task untuk cleanup otomatis

## Configuration

### QR Code Size
Default size: 200x200 pixels. Untuk mengubah size, edit di controller:

```php
$qrCodeUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=' . urlencode($scanUrl);
```

### PDF Paper Size
Default: A4 Portrait. Untuk mengubah, edit di controller:

```php
$pdf->setPaper('a4', 'landscape'); // atau 'letter', 'legal', dll
```

### Temporary File Location
Default: `storage/app/temp`. Untuk mengubah, edit di controller:

```php
$tempDir = storage_path('app/custom-temp-dir');
```

## Future Development

Controller `TabunganBarcodeController` dapat dikembangkan lebih lanjut untuk:

- **Logging akses scan**: Catat siapa dan kapan QR code di-scan
- **Autentikasi opsional**: Tambahkan opsi untuk require login sebelum scan
- **Informasi transaksi terbaru**: Tampilkan 5 transaksi terakhir di halaman scan
- **Integrasi notifikasi**: Kirim notifikasi ke anggota saat QR code di-scan
- **Analytics scan barcode**: Dashboard untuk melihat statistik scan
- **Batch print**: Cetak multiple barcode sekaligus
- **Custom branding**: Tambahkan logo koperasi di QR code
- **Expiry date**: QR code dengan masa berlaku tertentu
- **Password protection**: QR code yang memerlukan password untuk akses

## Notes

- Temporary files otomatis dihapus setelah PDF di-generate
- Jika ada error, file temporary mungkin tertinggal (bisa di-cleanup manual)
- QR Code size: 200x200 pixels (bisa disesuaikan di URL API)
- API QRServer: https://api.qrserver.com/v1/create-qr-code/
- Scan URL format: `http://your-domain.com/tabungan/{id}/scan`
