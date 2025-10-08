# Fitur Barcode Tabungan

## Overview
Fitur ini memungkinkan admin untuk mencetak barcode QR Code untuk setiap rekening tabungan. Ketika barcode di-scan, akan menampilkan informasi detail rekening tabungan secara public (tanpa autentikasi).

## Komponen yang Dibuat

### 1. Controller
- **File**: `app/Http/Controllers/TabunganBarcodeController.php`
- **Methods**:
  - `printBarcode($id)`: Generate dan download PDF barcode
  - `scan($id)`: Menampilkan halaman detail tabungan ketika barcode di-scan

### 2. Routes
- **Print Barcode**: `GET /tabungan/{id}/print-barcode` (route name: `tabungan.print-barcode`)
- **Scan Barcode**: `GET /tabungan/{id}/scan` (route name: `tabungan.scan`)

### 3. Views
- **PDF Barcode**: `resources/views/pdf/tabungan-barcode.blade.php`
- **Scan Page**: `resources/views/tabungan/scan.blade.php`

### 4. Action di Filament
- **File**: `app/Filament/Resources/TabunganResource.php`
- **Action**: "Cetak Barcode" di table actions

## Cara Penggunaan

1. **Admin Panel**:
   - Masuk ke halaman Rekening Tabungan
   - Klik action "Cetak Barcode" pada rekening yang diinginkan
   - PDF barcode akan otomatis ter-download

2. **Scan Barcode**:
   - Scan QR Code menggunakan aplikasi scanner
   - Akan redirect ke halaman public yang menampilkan detail rekening

## Informasi yang Ditampilkan

### PDF Barcode
- No. Rekening
- Nama Nasabah
- Produk Tabungan
- Saldo
- Status Rekening
- QR Code
- URL scan
- Timestamp cetak

### Halaman Scan
- Informasi Rekening (No. Rekening, Nama, Produk, Saldo, Tanggal Buka, Status)
- Informasi Kontak (Telepon, Email jika ada)
- Timestamp akses
- Responsive design dengan TailwindCSS

## Dependencies
- `simplesoftwareio/simple-qrcode`: Package untuk generate QR Code
- `barryvdh/laravel-dompdf`: Untuk generate PDF
- TailwindCSS: Untuk styling halaman scan

## Security Notes
- Halaman scan bersifat **public** (tidak memerlukan autentikasi)
- Hanya menampilkan informasi dasar rekening
- Tidak ada aksi yang dapat dilakukan dari halaman scan
- File PDF barcode dapat di-download langsung

## Future Development
Controller `TabunganBarcodeController` dapat dikembangkan lebih lanjut untuk:
- Menambah logging akses scan
- Menambah fitur autentikasi opsional
- Menambah informasi transaksi terbaru
- Integrasi dengan sistem notifikasi
- Analytics scan barcode