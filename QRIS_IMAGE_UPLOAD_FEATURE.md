# QRIS Image Upload Feature

## Ringkasan Fitur

Fitur upload gambar QRIS yang otomatis membaca dan mengekstrak QRIS string dari gambar QR code.

## Cara Penggunaan

### 1. Upload Image QRIS

```
Payment > Static QRIS > New Static QRIS
```

1. Isi **Name** (contoh: "QRIS Toko Utama")
2. Klik **Upload QRIS Image**
3. Pilih file gambar QR code (PNG/JPG, max 2MB)
4. Tunggu proses upload dan ekstraksi
5. Field **Static QRIS String** dan **Merchant Name** akan terisi otomatis
6. Klik **Create**

### 2. Alternatif: Paste Manual

Jika tidak ingin upload image, bisa langsung paste QRIS string di field **Static QRIS String**.

## Teknologi

### QrisHelper Class

Location: `app/Helpers/QrisHelper.php`

**Methods:**

-   `readQrisFromImage(string $imagePath)`: Membaca QRIS dari image
-   `isValidQris(string $qrisString)`: Validasi QRIS string
-   `parseMerchantName(string $qrisData)`: Parse merchant name dari QRIS

### Library Integration

**khanamiryan/qrcode-detector-decoder**

-   Library PHP lokal untuk decode QR code
-   Tidak memerlukan koneksi internet
-   Support PNG, JPG, GIF

**Usage:**

```php
use Zxing\QrReader;

$qrcode = new QrReader($imagePath);
$qrisString = $qrcode->text();
```

**Advantages:**

-   ✅ Tidak bergantung API eksternal
-   ✅ Lebih cepat (proses lokal)
-   ✅ Lebih reliable (tidak ada network issue)
-   ✅ Privacy (data tidak keluar dari server)

## Validasi

QRIS dianggap valid jika memenuhi:

1. Panjang string > 50 karakter
2. Mengandung tag `5802ID` (Indonesia country code)
3. Mengandung tag `0002` (QRIS version)

## Notifikasi

### Success

-   **Title**: "QRIS Detected Successfully"
-   **Body**: "Merchant: {merchant_name}"
-   **Type**: Success (green)

### Warning

-   **Title**: "Failed to Read QRIS"
-   **Body**: "Could not extract QRIS string from image. Please paste manually."
-   **Type**: Warning (yellow)

## Storage

**Directory**: `storage/app/public/qris-images/`
**Disk**: `public`
**Access**: Via symlink `public/storage/qris-images/`

## Database Schema

```sql
ALTER TABLE qris_statics
ADD COLUMN qris_image VARCHAR(255) NULL AFTER qris_string;
```

## Error Handling

1. **File tidak ditemukan**: Return null
2. **API gagal**: Log error dan return null
3. **QR tidak terbaca**: Notifikasi warning ke user
4. **QRIS tidak valid**: Return null

## Tips

-   Gunakan gambar QR code yang jelas dan tidak blur
-   Format PNG lebih baik dari JPG untuk QR code
-   Pastikan QR code tidak terpotong
-   Jika gagal, coba crop gambar lebih dekat ke QR code
-   Alternatif: paste QRIS string manual jika upload gagal

## Troubleshooting

### Image upload tapi QRIS tidak terdeteksi

**Solusi:**

1. Pastikan gambar QR code jelas
2. Coba crop gambar lebih dekat
3. Gunakan format PNG
4. Paste QRIS string manual sebagai alternatif

### Error "Failed to Read QRIS"

**Penyebab:**

-   API QR Server tidak merespons
-   Gambar tidak mengandung QR code
-   QR code rusak atau tidak valid

**Solusi:**

-   Cek koneksi internet
-   Upload gambar yang berbeda
-   Paste QRIS string manual

## Future Improvements

-   [ ] Support multiple QR reader API (fallback)
-   [ ] Local QR reader (tidak bergantung API eksternal)
-   [ ] Batch upload multiple QRIS images
-   [ ] Image preprocessing (crop, enhance, rotate)
-   [ ] Cache API response
