# Makan Bergizi Gratis - Documentation

## Overview

Fitur **Makan Bergizi Gratis** adalah sistem pencatatan pemberian makan bergizi gratis kepada nasabah berdasarkan nomor tabungan mereka. Sistem ini memastikan bahwa setiap nasabah hanya dapat tercatat **satu kali per hari** untuk nomor tabungan yang sama.

## Features

- ✅ Validasi 1 record per hari per nomor tabungan
- ✅ Relasi dengan data Tabungan dan Profile yang sudah ada
- ✅ Menyimpan snapshot data lengkap (rekening, nasabah, produk, transaksi terakhir)
- ✅ API endpoint untuk integrasi eksternal
- ✅ Admin panel untuk monitoring dan management
- ✅ Activity logging untuk audit trail

## Database Structure

### Table: `makan_bergizis_gratis`

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| tabungan_id | bigint | Foreign key ke tabel tabungans |
| profile_id | bigint | Foreign key ke tabel profiles |
| no_tabungan | string | Nomor rekening tabungan |
| tanggal_pemberian | date | Tanggal pemberian makan gratis |
| data_rekening | json | Snapshot data rekening |
| data_nasabah | json | Snapshot data nasabah |
| data_produk | json | Snapshot data produk tabungan |
| data_transaksi_terakhir | json | Snapshot transaksi terakhir |
| scanned_at | timestamp | Waktu scan/input data |
| created_at | timestamp | Waktu record dibuat |
| updated_at | timestamp | Waktu record diupdate |

### Unique Constraint
- `unique_daily_record`: Kombinasi `no_tabungan` + `tanggal_pemberian` harus unik

## API Endpoints

### 1. POST `/api/makan-bergizi-gratis`

Endpoint untuk mencatat pemberian makan bergizi gratis.

#### Request

```http
POST /api/makan-bergizi-gratis
Content-Type: application/json

{
  "no_tabungan": "TB001234"
}
```

#### Response Success (201 Created)

```json
{
  "success": true,
  "message": "Data Makan Bergizi Gratis berhasil dicatat",
  "data": {
    "id": 1,
    "no_tabungan": "TB001234",
    "tanggal_pemberian": "09/10/2025",
    "rekening": {
      "no_tabungan": "TB001234",
      "produk": "Tabungan Umum",
      "saldo": 5000000,
      "saldo_formatted": "Rp 5.000.000",
      "status": "aktif",
      "tanggal_buka": "01/01/2025",
      "tanggal_buka_iso": "2025-01-01T00:00:00.000000Z"
    },
    "nasabah": {
      "nama_lengkap": "John Doe",
      "first_name": "John",
      "last_name": "Doe",
      "phone": "081234567890",
      "email": "john@example.com",
      "whatsapp": "081234567890",
      "address": "Jl. Example No. 123"
    },
    "produk_detail": {
      "id": 1,
      "nama": "Tabungan Umum",
      "keterangan": "Produk tabungan untuk umum"
    },
    "transaksi_terakhir": {
      "kode_transaksi": "TRX001",
      "jenis_transaksi": "debit",
      "jenis_transaksi_label": "Setoran",
      "jumlah": 100000,
      "jumlah_formatted": "Rp 100.000",
      "tanggal_transaksi": "08/10/2025 10:30:00",
      "tanggal_transaksi_iso": "2025-10-08T10:30:00.000000Z",
      "keterangan": "Setoran tunai",
      "teller": "Admin User"
    },
    "metadata": {
      "scanned_at": "2025-10-09T10:15:30.000000Z",
      "scanned_at_formatted": "09/10/2025 10:15:30"
    }
  }
}
```

#### Response Error - Already Recorded (409 Conflict)

```json
{
  "success": false,
  "message": "Data untuk nomor tabungan ini sudah tercatat hari ini",
  "data": {
    "no_tabungan": "TB001234",
    "tanggal": "09/10/2025",
    "status": "already_recorded"
  }
}
```

#### Response Error - Validation (422 Unprocessable Entity)

```json
{
  "success": false,
  "message": "Validation error",
  "errors": {
    "no_tabungan": [
      "The no tabungan field is required."
    ]
  }
}
```

#### Response Error - Not Found (422)

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

#### Response Error - Server Error (500)

```json
{
  "success": false,
  "message": "Terjadi kesalahan saat menyimpan data",
  "error": "Error message details"
}
```

### 2. GET `/api/makan-bergizi-gratis`

Endpoint untuk mendapatkan list data dengan pagination dan filter.

#### Query Parameters

- `tanggal` (optional): Filter by specific date (YYYY-MM-DD)
- `dari` (optional): Start date for range filter (YYYY-MM-DD)
- `sampai` (optional): End date for range filter (YYYY-MM-DD)
- `no_tabungan` (optional): Filter by account number
- `per_page` (optional): Items per page (default: 15)
- `page` (optional): Page number

#### Example Request

```http
GET /api/makan-bergizi-gratis?tanggal=2025-10-09
GET /api/makan-bergizi-gratis?dari=2025-10-01&sampai=2025-10-31
GET /api/makan-bergizi-gratis?no_tabungan=TB001234&per_page=10
```

#### Response

```json
{
  "data": [
    {
      "id": 1,
      "no_tabungan": "TB001234",
      "tanggal_pemberian": "09/10/2025",
      "tanggal_pemberian_iso": "2025-10-09T00:00:00.000000Z",
      "rekening": {...},
      "nasabah": {...},
      "produk_detail": {...},
      "transaksi_terakhir": {...},
      "metadata": {...}
    }
  ],
  "links": {...},
  "meta": {...}
}
```

### 3. GET `/api/makan-bergizi-gratis/{id}`

Endpoint untuk mendapatkan detail single record.

#### Response

```json
{
  "data": {
    "id": 1,
    "no_tabungan": "TB001234",
    "tanggal_pemberian": "09/10/2025",
    "rekening": {...},
    "nasabah": {...},
    "produk_detail": {...},
    "transaksi_terakhir": {...},
    "metadata": {...}
  }
}
```

### 4. POST `/api/makan-bergizi-gratis/check-today`

Endpoint untuk mengecek apakah nomor tabungan sudah tercatat hari ini.

#### Request

```json
{
  "no_tabungan": "TB001234"
}
```

#### Response

```json
{
  "success": true,
  "data": {
    "no_tabungan": "TB001234",
    "tanggal": "09/10/2025",
    "exists": true,
    "status": "already_recorded"
  }
}
```

### Rate Limiting

Semua API endpoint memiliki rate limit **60 requests per menit**.

## Admin Panel

### Navigation

Menu **Makan Bergizi Gratis** tersedia di:
- **Group**: Program
- **Icon**: Gift icon
- **Sort Order**: 5

### Features

#### 1. List View
- Tabel dengan kolom:
  - No. Tabungan (searchable, sortable)
  - Nama Nasabah (searchable, sortable)
  - Tanggal Pemberian (sortable)
  - Saldo
  - Produk
  - Waktu Scan (sortable)
  - Dibuat (hidden by default)

#### 2. Filters
- **Filter Tanggal**: Range dari-sampai
- **Filter Hari Ini**: Quick filter untuk data hari ini

#### 3. Actions
- **View**: Melihat detail lengkap record
- **Delete**: Menghapus record
- **Bulk Delete**: Menghapus multiple records

#### 4. View Detail
Menampilkan informasi lengkap dalam sections:
- Informasi Rekening
- Data Nasabah
- Data Rekening
- Data Produk
- Transaksi Terakhir

## Model Methods

### `MakanBergizisGratis::existsForToday(string $noTabungan): bool`

Mengecek apakah sudah ada record untuk nomor tabungan tertentu pada hari ini.

```php
if (MakanBergizisGratis::existsForToday('TB001234')) {
    // Already recorded today
}
```

## Relationships

### `tabungan()`
Relasi belongsTo ke model `Tabungan`

```php
$record->tabungan; // Get Tabungan model
```

### `profile()`
Relasi belongsTo ke model `Profile`

```php
$record->profile; // Get Profile model
```

## Activity Logging

Semua operasi pada model ini akan tercatat dalam activity log dengan fields:
- tabungan_id
- profile_id
- no_tabungan
- tanggal_pemberian
- scanned_at

## Usage Examples

### Via API (cURL)

```bash
curl -X POST http://your-domain.com/api/makan-bergizi-gratis \
  -H "Content-Type: application/json" \
  -d '{"no_tabungan": "TB001234"}'
```

### Via API (JavaScript/Fetch)

```javascript
fetch('http://your-domain.com/api/makan-bergizi-gratis', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
  },
  body: JSON.stringify({
    no_tabungan: 'TB001234'
  })
})
.then(response => response.json())
.then(data => {
  if (data.success) {
    console.log('Berhasil dicatat:', data.data);
  } else {
    console.error('Error:', data.message);
  }
});
```

### Via Eloquent

```php
use App\Models\MakanBergizisGratis;
use App\Models\Tabungan;

// Check if already recorded today
if (MakanBergizisGratis::existsForToday('TB001234')) {
    return 'Sudah tercatat hari ini';
}

// Get tabungan data
$tabungan = Tabungan::with(['profile', 'produkTabungan'])
    ->where('no_tabungan', 'TB001234')
    ->first();

// Create record
$record = MakanBergizisGratis::create([
    'tabungan_id' => $tabungan->id,
    'profile_id' => $tabungan->id_profile,
    'no_tabungan' => $tabungan->no_tabungan,
    'tanggal_pemberian' => today(),
    'data_rekening' => [...],
    'data_nasabah' => [...],
    'data_produk' => [...],
    'data_transaksi_terakhir' => [...],
    'scanned_at' => now(),
]);
```

## Testing

### Test Scenario 1: First Record of the Day
```bash
# Should succeed
curl -X POST http://localhost/api/makan-bergizi-gratis \
  -H "Content-Type: application/json" \
  -d '{"no_tabungan": "TB001234"}'
```

### Test Scenario 2: Duplicate Record Same Day
```bash
# Should fail with 409 Conflict
curl -X POST http://localhost/api/makan-bergizi-gratis \
  -H "Content-Type: application/json" \
  -d '{"no_tabungan": "TB001234"}'
```

### Test Scenario 3: Invalid No Tabungan
```bash
# Should fail with 422 Validation Error
curl -X POST http://localhost/api/makan-bergizi-gratis \
  -H "Content-Type: application/json" \
  -d '{"no_tabungan": "INVALID"}'
```

## Security

- ✅ Rate limiting: 60 requests/minute
- ✅ Input validation
- ✅ SQL injection protection (via Eloquent)
- ✅ Foreign key constraints
- ✅ Unique constraint untuk prevent duplicates
- ✅ Activity logging untuk audit trail

## Maintenance

### Cleanup Old Records

Jika diperlukan, Anda bisa membuat command untuk cleanup data lama:

```php
// Delete records older than 90 days
MakanBergizisGratis::where('tanggal_pemberian', '<', now()->subDays(90))
    ->delete();
```

## Troubleshooting

### Issue: "Data sudah tercatat hari ini" padahal belum input

**Solusi**: Cek di admin panel apakah memang sudah ada record untuk no_tabungan tersebut pada tanggal hari ini.

### Issue: "The selected no tabungan is invalid"

**Solusi**: Pastikan nomor tabungan yang diinput benar-benar ada di database tabel `tabungans`.

### Issue: Foreign key constraint error

**Solusi**: Pastikan `tabungan_id` dan `profile_id` yang valid ada di database.

## Future Enhancements

Beberapa enhancement yang bisa ditambahkan:
- Export data ke Excel/PDF
- Dashboard widget untuk statistik harian/bulanan
- Notifikasi email/WhatsApp setelah pencatatan
- QR Code scanning untuk input lebih cepat
- Mobile app integration
- Foto bukti pemberian makanan
- Rating/feedback dari nasabah

---

**Created**: October 9, 2025  
**Version**: 1.0.0  
**Author**: Dash-Kospin Development Team
