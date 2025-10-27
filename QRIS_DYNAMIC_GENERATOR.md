# QRIS Dynamic Generator

## Overview

Fitur untuk menyimpan QRIS statis dan mengkonversinya menjadi QRIS dinamis dengan nominal dan biaya custom.

## Features

### 1. Static QRIS Management

-   Menyimpan multiple QRIS statis
-   Mengelola merchant name dan deskripsi
-   Status aktif/non-aktif
-   Resource lengkap dengan CRUD operations

### 2. Dynamic QRIS Generator

-   Konversi QRIS statis ke dinamis
-   Input nominal transaksi
-   Pilihan tipe fee (Rupiah atau Persentase)
-   Generate QR Code visual
-   Download QR Code sebagai PNG
-   Print QR Code
-   Copy QRIS string ke clipboard

## Usage

### Menyimpan Static QRIS

1. Buka menu **Payment > Static QRIS**
2. Klik **New Static QRIS**
3. Isi form:
    - **Name**: Nama identifikasi (contoh: "Main Account QRIS")
    - **Upload QRIS Image**: Upload gambar QR code (PNG/JPG, max 2MB)
        - QRIS string akan otomatis ter-extract dari gambar
        - Merchant name akan otomatis terdeteksi
    - **Static QRIS String**: Atau paste QRIS string manual jika tidak upload image
    - **Merchant Name**: (Optional) Akan auto-detect dari QRIS
    - **Description**: (Optional) Deskripsi tambahan
    - **Active**: Toggle status aktif
4. Klik **Create**

**Note**: Saat upload image, sistem akan otomatis membaca QR code dan mengisi field QRIS string serta merchant name.

### Generate Dynamic QRIS

1. Buka menu **Payment > QRIS Generator**
2. Pilih salah satu opsi:
    - **Select Saved QRIS**: Pilih dari QRIS yang sudah disimpan
    - **Static QRIS Code**: Atau paste langsung QRIS string
3. Isi form:
    - **Amount**: Nominal transaksi (Rp)
    - **Fee Type**: Pilih Rupiah atau Persentase
    - **Fee Value**: Nilai fee (opsional)
4. Klik **Generate Dynamic QRIS**
5. Hasil akan menampilkan:
    - QR Code visual
    - Merchant name
    - QRIS string dinamis
    - Tombol Download, Print, dan Copy

## Technical Details

### Algorithm

Konversi menggunakan algoritma standar QRIS:

1. Remove CRC (4 karakter terakhir)
2. Ubah indicator dari static (010211) ke dynamic (010212)
3. Split berdasarkan country code (5802ID)
4. Tambahkan tag amount (54)
5. Tambahkan tag fee jika ada (55)
6. Hitung ulang CRC16 checksum
7. Append CRC ke payload

### CRC16 Calculation

Menggunakan polynomial 0x1021 sesuai standar QRIS/EMV.

### Fee Types

-   **Rupiah (Fixed)**: Tag 55020256 + nilai
-   **Persentase**: Tag 55020357 + nilai

## Navigation

-   **Static QRIS Resource**: Payment > Static QRIS
-   **Dynamic Generator**: Payment > QRIS Generator

## Database

### Table: qris_statics

| Column        | Type      | Description         |
| ------------- | --------- | ------------------- |
| id            | bigint    | Primary key         |
| name          | varchar   | Nama identifikasi   |
| qris_string   | text      | QRIS string statis  |
| qris_image    | varchar   | Path gambar QR code |
| merchant_name | varchar   | Nama merchant       |
| description   | text      | Deskripsi           |
| is_active     | boolean   | Status aktif        |
| created_at    | timestamp | Waktu dibuat        |
| updated_at    | timestamp | Waktu diupdate      |

## Dependencies

-   **QRCode.js**: Library untuk generate QR Code visual
-   **khanamiryan/qrcode-detector-decoder**: Library PHP untuk membaca QR code dari image (local, tidak perlu internet)
-   **Filament 3**: Framework admin panel
-   **Livewire**: Reactive components

## Example QRIS String

```
00020101021226670016ID.CO.QRIS.WWW0118IDKU20230000000010303UMI51440014ID.CO.TELKOM.WWW02180000000000000000000303UMI520454995303360540410005802ID5914MERCHANT NAME6015JAKARTA SELATAN61051234062070703A016304XXXX
```

## Image Upload Feature

### Cara Kerja

1. User upload gambar QR code QRIS (PNG/JPG)
2. Gambar disimpan di `storage/app/public/qris-images/`
3. Sistem mengirim gambar ke QR Server API untuk di-decode
4. API mengembalikan QRIS string yang terbaca
5. Sistem validasi apakah string valid (mengandung tag QRIS)
6. Merchant name otomatis di-parse dari QRIS string
7. Field form otomatis terisi

### QR Reader Library

Menggunakan **khanamiryan/qrcode-detector-decoder**

-   Library PHP lokal (tidak perlu koneksi internet)
-   Support PNG, JPG, GIF
-   Lebih cepat dan reliable
-   Tidak bergantung pada API eksternal

### Validasi QRIS

QRIS dianggap valid jika:

-   Panjang string > 50 karakter
-   Mengandung tag `5802ID` (country code Indonesia)
-   Mengandung tag `0002` (version)

## Notes

-   QRIS string harus valid dan mengandung tag 5802ID
-   Merchant name akan di-parse otomatis dari tag 59
-   Amount akan di-format tanpa leading zeros
-   CRC akan dihitung ulang otomatis
-   Upload image akan otomatis extract QRIS string
-   Jika gagal membaca QR dari image, user bisa paste manual
