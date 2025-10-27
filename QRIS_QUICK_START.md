# QRIS Feature - Quick Start Guide

## 🚀 Mulai Menggunakan

### Step 1: Simpan Static QRIS

1. Login ke admin panel
2. Buka menu **Payment** → **Static QRIS**
3. Klik tombol **New Static QRIS**
4. Isi form:
    ```
    Name: QRIS Toko Utama
    Upload QRIS Image: [pilih file gambar QR code]
    ```
5. Tunggu beberapa detik, field akan terisi otomatis:
    - ✅ Static QRIS String
    - ✅ Merchant Name
6. Klik **Create**

**Alternatif tanpa upload:**

-   Skip upload image
-   Paste QRIS string langsung di field "Static QRIS String"

### Step 2: Generate Dynamic QRIS

1. Buka menu **Payment** → **QRIS Generator**
2. Pilih QRIS yang sudah disimpan di dropdown **Select Saved QRIS**
3. Isi amount:
    ```
    Amount: 50000
    Fee Type: Rupiah
    Fee Value: 0
    ```
4. Klik **Generate Dynamic QRIS**
5. QR code akan muncul dengan informasi:
    - Visual QR code
    - Merchant name
    - QRIS string

### Step 3: Gunakan QR Code

**Option 1: Download**

-   Klik tombol **Download QR Code**
-   File PNG akan tersimpan

**Option 2: Print**

-   Klik tombol **Print**
-   Print preview akan muncul

**Option 3: Copy String**

-   Klik tombol **Copy** di bawah QRIS string
-   Paste ke aplikasi lain

## 📱 Contoh Use Case

### Use Case 1: Kasir Toko

```
1. Customer belanja Rp 150.000
2. Kasir buka QRIS Generator
3. Select "QRIS Toko Utama"
4. Input amount: 150000
5. Generate
6. Customer scan QR code
7. Pembayaran selesai
```

### Use Case 2: Invoice Digital

```
1. Buat invoice untuk customer
2. Generate dynamic QRIS dengan nominal invoice
3. Download QR code
4. Attach ke PDF invoice
5. Kirim ke customer
```

### Use Case 3: Multiple Merchant

```
1. Simpan QRIS untuk setiap cabang:
   - QRIS Cabang Jakarta
   - QRIS Cabang Bandung
   - QRIS Cabang Surabaya
2. Saat transaksi, pilih QRIS sesuai cabang
3. Generate dengan nominal yang sesuai
```

## 🎯 Tips & Tricks

### Upload Image QRIS

✅ **DO:**

-   Gunakan gambar yang jelas dan fokus
-   Format PNG lebih baik dari JPG
-   Pastikan QR code tidak terpotong
-   Ukuran file < 2MB

❌ **DON'T:**

-   Gambar blur atau buram
-   QR code terlalu kecil
-   Ada watermark di atas QR code
-   Format selain PNG/JPG

### Generate Dynamic QRIS

✅ **DO:**

-   Gunakan nominal bulat (tanpa desimal)
-   Fee opsional, bisa dikosongkan
-   Save QRIS yang sering dipakai
-   Test scan dengan aplikasi e-wallet

❌ **DON'T:**

-   Nominal 0 atau negatif
-   Fee lebih besar dari nominal
-   Generate untuk nominal terlalu besar (> 10 juta)

## 🔍 Troubleshooting

### Problem: Upload image tapi QRIS tidak terdeteksi

**Solution:**

1. Cek apakah gambar jelas
2. Coba crop gambar lebih dekat ke QR code
3. Gunakan format PNG
4. Jika tetap gagal, paste QRIS string manual

### Problem: QR code tidak bisa di-scan

**Solution:**

1. Pastikan nominal valid (> 0)
2. Cek apakah static QRIS valid
3. Generate ulang
4. Test dengan aplikasi e-wallet berbeda

### Problem: Merchant name tidak muncul

**Solution:**

1. Merchant name auto-detect dari QRIS string
2. Jika tidak muncul, isi manual
3. Tidak wajib diisi

## 📞 Support

Jika ada masalah:

1. Cek dokumentasi lengkap di `QRIS_DYNAMIC_GENERATOR.md`
2. Cek log error di `storage/logs/laravel.log`
3. Pastikan koneksi internet aktif (untuk QR reader API)

## ✨ Fitur Tambahan

### View Saved QRIS

-   Klik icon **eye** di table Static QRIS
-   Lihat detail lengkap dengan preview image
-   Copy QRIS string langsung dari modal

### Edit QRIS

-   Klik icon **edit** di table
-   Update name, description, atau status
-   Image dan QRIS string bisa diupdate

### Quick Generate

-   Dari table Static QRIS
-   Klik tombol **Generate Dynamic**
-   Langsung ke halaman generator

## 🎉 Selamat Menggunakan!

Fitur QRIS sudah siap digunakan untuk mempermudah transaksi pembayaran digital di aplikasi Anda.
