# Payment API — QRIS Endpoints

Dokumentasi API endpoint Payment QRIS untuk aplikasi Dash-Kospin.  
Base URL: `{APP_URL}/api/payment`

---

## Daftar Endpoint

| Method | Endpoint | Auth | Keterangan |
|--------|----------|------|------------|
| GET | `/api/payment/qris` | Tidak | Daftar QRIS statis tersimpan |
| GET | `/api/payment/qris/{id}` | Tidak | Detail satu QRIS statis |
| POST | `/api/payment/qris/validate` | Tidak | Validasi string QRIS |
| POST | `/api/payment/qris/generate-dynamic` | Tidak | Generate QRIS dinamis |

> Semua endpoint bersifat **publik** (tidak memerlukan token Sanctum), namun dibatasi oleh rate limiter bawaan Laravel.

---

## Workflow Penggunaan

### Skenario 1 — Generate QRIS Dinamis dari Data Tersimpan

```
1. GET /api/payment/qris
   → Ambil daftar QRIS statis aktif, catat `id` yang diinginkan

2. POST /api/payment/qris/generate-dynamic
   Body: { "qris_id": <id>, "amount": 50000 }
   → Sistem mengambil qris_string dari DB, melakukan konversi ke format dinamis,
     menghitung CRC16, menyimpan gambar QR, dan mengembalikan string + URL gambar.
```

### Skenario 2 — Generate QRIS Dinamis dari String Manual

```
1. POST /api/payment/qris/validate
   Body: { "qris_string": "00020101021226..." }
   → Verifikasi bahwa string valid sebelum diproses

2. POST /api/payment/qris/generate-dynamic
   Body: { "qris_string": "00020101021226...", "amount": 75000,
           "fee_type": "Rupiah", "fee_value": 2500 }
   → Menghasilkan QRIS dinamis dengan jumlah dan biaya yang disertakan
```

### Skenario 3 — Tampilan Preview QRIS di Aplikasi Mobile

```
1. GET /api/payment/qris          → Daftar QRIS (dengan image_url)
2. GET /api/payment/qris/{id}     → Detail + qris_string lengkap
3. Tampilkan image_url sebagai gambar QR statis,
   atau gunakan qris_string untuk generate ulang secara lokal.
```

---

## Detail Endpoint

### GET `/api/payment/qris`

Mengambil daftar QRIS statis. Secara default hanya menampilkan yang aktif.

**Query Parameters:**

| Parameter | Tipe | Default | Keterangan |
|-----------|------|---------|------------|
| `active_only` | boolean | `true` | Set `false` untuk tampilkan semua termasuk nonaktif |

**Response 200:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "QRIS Utama Koperasi",
      "merchant_name": "KOSPIN JASA",
      "description": "QRIS untuk pembayaran umum",
      "is_active": true,
      "image_url": "http://example.com/storage/qris/kospin-utama.png",
      "created_at": "2025-10-27T09:00:00.000000Z"
    }
  ]
}
```

---

### GET `/api/payment/qris/{id}`

Mengambil detail satu QRIS statis termasuk `qris_string` lengkap.

**Path Parameters:**

| Parameter | Tipe | Keterangan |
|-----------|------|------------|
| `id` | integer | ID QRIS statis |

**Response 200:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "QRIS Utama Koperasi",
    "merchant_name": "KOSPIN JASA",
    "description": "QRIS untuk pembayaran umum",
    "qris_string": "00020101021226...",
    "is_active": true,
    "image_url": "http://example.com/storage/qris/kospin-utama.png",
    "created_at": "2025-10-27T09:00:00.000000Z",
    "updated_at": "2025-10-27T09:00:00.000000Z"
  }
}
```

**Response 404:**
```json
{
  "success": false,
  "message": "QRIS tidak ditemukan."
}
```

---

### POST `/api/payment/qris/validate`

Memvalidasi apakah string adalah QRIS yang sah, sekaligus mendeteksi tipe (statis/dinamis) dan nama merchant.

**Request Body (JSON):**

| Field | Tipe | Wajib | Keterangan |
|-------|------|-------|------------|
| `qris_string` | string | Ya | String QRIS yang akan divalidasi |

**Contoh Request:**
```json
{
  "qris_string": "00020101021226..."
}
```

**Response 200 — Valid:**
```json
{
  "success": true,
  "data": {
    "is_valid": true,
    "merchant_name": "KOSPIN JASA",
    "is_static": true,
    "is_dynamic": false
  }
}
```

**Response 200 — Tidak Valid:**
```json
{
  "success": true,
  "data": {
    "is_valid": false
  }
}
```

---

### POST `/api/payment/qris/generate-dynamic`

Mengkonversi QRIS statis menjadi QRIS dinamis dengan jumlah pembayaran dan biaya transaksi yang ditentukan, lalu menyimpan gambar QR ke storage.

**Request Body (JSON):**

| Field | Tipe | Wajib | Keterangan |
|-------|------|-------|------------|
| `qris_id` | integer | Kondisional* | ID QRIS statis dari database |
| `qris_string` | string | Kondisional* | String QRIS manual (min. 20 karakter) |
| `amount` | numeric | Ya | Jumlah pembayaran dalam Rupiah (min. 1) |
| `fee_type` | string | Tidak | `Rupiah` (default) atau `Persentase` |
| `fee_value` | numeric | Tidak | Nilai biaya; 0 berarti tanpa biaya |

> `*` Salah satu dari `qris_id` atau `qris_string` wajib diisi. Jika keduanya dikirim, `qris_id` diprioritaskan.

**Contoh Request — Pakai ID tersimpan:**
```json
{
  "qris_id": 1,
  "amount": 50000,
  "fee_type": "Rupiah",
  "fee_value": 2500
}
```

**Contoh Request — Pakai string manual dengan fee persentase:**
```json
{
  "qris_string": "00020101021226...",
  "amount": 100000,
  "fee_type": "Persentase",
  "fee_value": 1.5
}
```

**Contoh Request — Tanpa biaya:**
```json
{
  "qris_id": 2,
  "amount": 25000
}
```

**Response 200:**
```json
{
  "success": true,
  "message": "QRIS dinamis berhasil dibuat.",
  "data": {
    "merchant_name": "KOSPIN JASA",
    "amount": 50000,
    "fee_type": "Rupiah",
    "fee_value": 2500,
    "dynamic_qris": "00020101021212...",
    "image_url": "http://example.com/storage/qris-generated/qris-dynamic-20261001120000-abc123.png",
    "filename": "qris-dynamic-20261001120000-abc123.png"
  }
}
```

**Response 422 — Validasi gagal:**
```json
{
  "success": false,
  "message": "Validasi gagal.",
  "errors": {
    "amount": ["The amount field is required."]
  }
}
```

**Response 422 — QRIS tidak valid:**
```json
{
  "success": false,
  "message": "String QRIS tidak valid."
}
```

**Response 500 — Error proses:**
```json
{
  "success": false,
  "message": "Gagal membuat QRIS dinamis: Format QRIS tidak sesuai (tidak ditemukan '5802ID')."
}
```

---

## Algoritma Konversi QRIS Statis → Dinamis

Endpoint `generate-dynamic` mengimplementasikan standard QRIS Indonesia (ASPI) dengan langkah berikut:

1. **Strip CRC** — Hapus 4 karakter terakhir dari string statis (CRC16 lama tidak dipakai).
2. **Ubah tipe** — Ganti tag `010211` (statis) menjadi `010212` (dinamis).
3. **Sisipkan amount** — Tambahkan tag `54` diikuti panjang 2 digit dan nilai nominal setelah data akuisisi, sebelum `5802ID`.
4. **Sisipkan fee (opsional)**:
   - Fee Rupiah: tag `5502` + `02` + tag `56` + nilai
   - Fee Persentase: tag `5502` + `03` + tag `57` + nilai
5. **Hitung CRC16** — Hitung ulang CRC CCITT-16 (poly `0x1021`, init `0xFFFF`) atas seluruh payload dan lampirkan 4 karakter hex uppercase sebagai suffix.

---

## Rate Limiting

| Endpoint | Limit |
|----------|-------|
| GET `/qris` | 60 request / menit |
| GET `/qris/{id}` | 60 request / menit |
| POST `/qris/validate` | 30 request / menit |
| POST `/qris/generate-dynamic` | 30 request / menit |

---

## Lokasi File

| File | Path |
|------|------|
| Controller | `app/Http/Controllers/Api/PaymentController.php` |
| Routes | `routes/api.php` (prefix `payment`) |
| Model | `app/Models/QrisStatic.php` |
| Helper | `app/Helpers/QrisHelper.php` |
| Filament Resource | `app/Filament/Resources/QrisStaticResource.php` |
| Filament Page | `app/Filament/Pages/QrisDynamicGenerator.php` |
| Gambar Generated | `storage/app/public/qris-generated/` |
