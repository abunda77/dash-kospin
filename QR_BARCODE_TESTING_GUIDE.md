# 📱 Panduan Testing QR Barcode Tabungan

## 🎯 Overview

QR Code yang di-generate sekarang berisi **nomor tabungan** (bukan URL), yang dapat langsung digunakan untuk input data Makan Bergizi Gratis melalui API.

---

## 🔧 Cara Testing Tanpa Alat Scanner Fisik

### **Metode 1: Menggunakan Smartphone** ⭐ (Paling Mudah)

#### A. Kamera Bawaan (iOS/Android)
1. Buka aplikasi **Kamera** di smartphone
2. Arahkan ke QR code di layar komputer atau PDF yang sudah di-print
3. Akan muncul notifikasi dengan data yang terbaca (contoh: `TAB-001`)
4. Data ini bisa langsung dicopy untuk testing

#### B. Google Lens (Android/iOS)
1. Buka **Google Lens** atau tekan lama pada gambar
2. Pilih "Search with Google Lens"
3. Arahkan ke QR code
4. Data akan muncul dan bisa di-copy

---

### **Metode 2: Web-based QR Scanner** 🌐 (Tanpa Install)

Buka salah satu website ini di browser:

| Website | URL | Kelebihan |
|---------|-----|-----------|
| WebQR | https://webqr.com/ | Simple, cepat |
| QR Code Scan | https://qrcodescan.in/ | Support upload file |
| QR.io | https://qr.io/scan | Clean interface |

**Cara Pakai:**
1. Buka website
2. Izinkan akses kamera
3. Arahkan kamera ke QR code
4. Data akan muncul di layar

**Tips:** Jika QR code ada di layar komputer yang sama, bisa:
- Screenshot QR code → Upload ke website scanner
- Atau buka di smartphone, scan dari layar komputer

---

### **Metode 3: Browser Extension** 🔌

Install extension di Chrome/Edge:
- **QR Code Reader** - Chrome Web Store
- **The QR Code Extension** - Chrome Web Store

**Cara Pakai:**
1. Install extension
2. Klik icon extension di toolbar
3. Pilih "Scan QR Code from screen"
4. Select area QR code

---

### **Metode 4: Test Script PHP** 💻 (Untuk Developer)

Gunakan file `test-qr-barcode.php` yang sudah dibuat:

```bash
php test-qr-barcode.php
```

**Edit script untuk testing:**
```php
// Line 10 - Ganti dengan nomor tabungan yang valid
$qrCodeData = 'TAB-001'; // Sesuaikan dengan data Anda
```

**Output yang diharapkan:**
```
=== TEST QR BARCODE TABUNGAN ===

QR Code Data: TAB-001
API Endpoint: http://localhost:8000/api/makan-bergizi-gratis

--- Test 1: Check Today ---
HTTP Code: 200
Response:
Array
(
    [success] => 1
    [data] => Array
        (
            [no_tabungan] => TAB-001
            [tanggal] => 10/10/2025
            [exists] => 
            [status] => available
        )
)

--- Test 2: Store Record ---
HTTP Code: 201
Response:
Array
(
    [success] => 1
    [message] => Data Makan Bergizi Gratis berhasil dicatat
    ...
)
```

---

### **Metode 5: Web Interface Testing** 🖥️ (User-Friendly)

Buka di browser: **http://localhost:8000/test-qr-scanner.html**

**Fitur:**
- ✅ Input manual nomor tabungan
- ✅ Check apakah sudah tercatat hari ini
- ✅ Submit data baru
- ✅ Tampilan response yang jelas
- ✅ Error handling

**Cara Pakai:**
1. Buka URL di browser
2. Masukkan nomor tabungan (contoh: `TAB-001`)
3. Klik **"Check Today"** untuk cek status
4. Klik **"Submit"** untuk mencatat data baru
5. Lihat response di bagian bawah

---

## 🧪 Skenario Testing

### **Test Case 1: QR Code Generation**
```bash
# 1. Login ke Filament admin
# 2. Buka menu Tabungan
# 3. Pilih salah satu rekening
# 4. Klik tombol "Print Barcode"
# 5. PDF akan ter-download
# 6. Buka PDF, pastikan QR code muncul
```

**Expected Result:**
- ✅ PDF ter-download dengan nama `barcode_tabungan_TAB-XXX_YYYY-MM-DD_HH-mm-ss.pdf`
- ✅ QR code terlihat jelas di PDF
- ✅ Data rekening lengkap (nama, produk, status)
- ✅ Footer menampilkan "Scan barcode untuk input data Makan Bergizi Gratis"

---

### **Test Case 2: QR Code Scanning**
```bash
# 1. Scan QR code menggunakan salah satu metode di atas
# 2. Catat data yang terbaca (contoh: TAB-001)
```

**Expected Result:**
- ✅ Data yang terbaca adalah nomor tabungan (format: TAB-XXX)
- ✅ Tidak ada URL atau data lain
- ✅ Data bisa di-copy dengan mudah

---

### **Test Case 3: API Check Today**
```bash
curl -X POST http://localhost:8000/api/makan-bergizi-gratis/check-today \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"no_tabungan":"TAB-001"}'
```

**Expected Result (Belum Ada):**
```json
{
  "success": true,
  "data": {
    "no_tabungan": "TAB-001",
    "tanggal": "10/10/2025",
    "exists": false,
    "status": "available"
  }
}
```

**Expected Result (Sudah Ada):**
```json
{
  "success": true,
  "data": {
    "no_tabungan": "TAB-001",
    "tanggal": "10/10/2025",
    "exists": true,
    "status": "already_recorded"
  }
}
```

---

### **Test Case 4: API Store Record**
```bash
curl -X POST http://localhost:8000/api/makan-bergizi-gratis \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"no_tabungan":"TAB-001"}'
```

**Expected Result (Success):**
```json
{
  "success": true,
  "message": "Data Makan Bergizi Gratis berhasil dicatat",
  "data": {
    "id": 1,
    "no_tabungan": "TAB-001",
    "tanggal_pemberian": "10/10/2025",
    "rekening": { ... },
    "nasabah": { ... },
    "produk_detail": { ... },
    "transaksi_terakhir": { ... }
  }
}
```

**Expected Result (Already Exists):**
```json
{
  "success": false,
  "message": "Data untuk nomor tabungan ini sudah tercatat hari ini",
  "data": {
    "no_tabungan": "TAB-001",
    "tanggal": "10/10/2025",
    "status": "already_recorded"
  }
}
```

---

### **Test Case 5: Invalid Account**
```bash
curl -X POST http://localhost:8000/api/makan-bergizi-gratis \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"no_tabungan":"TAB-999"}'
```

**Expected Result:**
```json
{
  "success": false,
  "message": "Validation error",
  "errors": {
    "no_tabungan": [
      "The selected no tabungan is invalid."
    ]
  }
}
```

---

## 🔍 Troubleshooting

### **QR Code Tidak Muncul di PDF**

**Penyebab:**
- API QR Code server tidak bisa diakses
- Koneksi internet bermasalah
- Firewall memblokir akses

**Solusi:**
1. Cek koneksi internet
2. Lihat log di `storage/logs/laravel.log`
3. Coba generate ulang PDF
4. Jika masih gagal, akan muncul placeholder dengan pesan error

**Log yang dicari:**
```
QR Code generation failed
```

---

### **Scan QR Code Tidak Terbaca**

**Penyebab:**
- QR code terlalu kecil
- Kualitas print/layar buruk
- Scanner tidak support format

**Solusi:**
1. Zoom in PDF sebelum scan
2. Pastikan QR code tidak blur
3. Coba scanner yang berbeda
4. Gunakan metode manual input (test-qr-scanner.html)

---

### **API Return 404**

**Penyebab:**
- Route belum terdaftar
- Server belum running

**Solusi:**
```bash
# Cek route
php artisan route:list | grep makan-bergizi

# Expected output:
# POST api/makan-bergizi-gratis
# POST api/makan-bergizi-gratis/check-today
# GET  api/makan-bergizi-gratis
# GET  api/makan-bergizi-gratis/{id}

# Start server jika belum running
php artisan serve
```

---

### **API Return 500**

**Penyebab:**
- Database error
- Data tidak lengkap
- Bug di code

**Solusi:**
1. Cek log: `storage/logs/laravel.log`
2. Pastikan database sudah migrate
3. Pastikan data tabungan valid dan lengkap

```bash
# Check migration
php artisan migrate:status

# Check data
php artisan tinker
>>> \App\Models\Tabungan::where('no_tabungan', 'TAB-001')->first()
```

---

## 📊 Monitoring & Logging

### **Cek Log Real-time**
```bash
php artisan pail
```

### **Cek Log File**
```bash
# Windows
type storage\logs\laravel.log

# Linux/Mac
tail -f storage/logs/laravel.log
```

### **Cek Data di Database**
```bash
php artisan tinker

# Cek record hari ini
>>> \App\Models\MakanBergizisGratis::whereDate('tanggal_pemberian', today())->count()

# Cek record terakhir
>>> \App\Models\MakanBergizisGratis::latest()->first()

# Cek by no_tabungan
>>> \App\Models\MakanBergizisGratis::where('no_tabungan', 'TAB-001')->get()
```

---

## 🚀 Quick Start Testing

### **1. Generate QR Code**
```bash
# Login ke admin panel
# http://localhost:8000/admin

# Navigate: Tabungan → View → Print Barcode
```

### **2. Scan dengan Smartphone**
```bash
# Buka kamera smartphone
# Scan QR code dari PDF atau layar
# Catat nomor yang terbaca (contoh: TAB-001)
```

### **3. Test API**
```bash
# Buka browser
http://localhost:8000/test-qr-scanner.html

# Input nomor tabungan
# Klik "Check Today"
# Klik "Submit"
```

### **4. Verify di Admin**
```bash
# Login ke admin panel
# Navigate: Makan Bergizi Gratis
# Cek apakah data sudah masuk
```

---

## 📱 Rekomendasi Aplikasi Scanner

### **Android:**
1. **QR Code Reader** (Free, No Ads)
2. **Google Lens** (Built-in)
3. **QR & Barcode Scanner** by Gamma Play

### **iOS:**
1. **Camera App** (Built-in, iOS 11+)
2. **QR Code Reader** by Scan
3. **Google Lens**

### **Desktop:**
1. **WebQR** (https://webqr.com/)
2. **QR Code Scanner** (Chrome Extension)
3. **Test Interface** (test-qr-scanner.html)

---

## 🎓 Tips & Best Practices

### **Untuk Testing:**
- ✅ Gunakan data dummy terlebih dahulu
- ✅ Test di berbagai device (smartphone, tablet, desktop)
- ✅ Test dengan berbagai scanner apps
- ✅ Simpan screenshot hasil testing
- ✅ Dokumentasikan error yang ditemukan

### **Untuk Production:**
- ✅ Pastikan QR code size cukup besar (min 200x200px)
- ✅ Print dengan kualitas tinggi
- ✅ Gunakan kertas yang tidak mudah rusak
- ✅ Laminating QR code untuk durability
- ✅ Backup data secara berkala

### **Untuk User:**
- ✅ Berikan panduan cara scan yang jelas
- ✅ Sediakan alternatif input manual
- ✅ Tampilkan feedback yang jelas (success/error)
- ✅ Handle edge cases (duplikasi, invalid data, dll)

---

## 📞 Support

Jika menemukan masalah:
1. Cek dokumentasi ini terlebih dahulu
2. Lihat log di `storage/logs/laravel.log`
3. Test dengan script yang disediakan
4. Dokumentasikan error dengan screenshot

---

**Last Updated:** 10 Oktober 2025
**Version:** 1.0.0
