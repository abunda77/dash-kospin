# Makan Bergizi Gratis - Quick Reference

## 🚀 Quick Start

### API Endpoint
```
POST /api/makan-bergizi-gratis
```

### Request
```json
{
  "no_tabungan": "TB001234"
}
```

### Response Codes
- `201` - Success (data tercatat)
- `409` - Already recorded today
- `422` - Validation error
- `500` - Server error

## 📁 Files Created

```
database/migrations/
  └── 2025_10_09_174831_create_makan_bergizis_gratis_table.php

app/Models/
  └── MakanBergizisGratis.php

app/Http/Controllers/Api/
  └── MakanBergizisGratisController.php

app/Filament/Resources/
  ├── MakanBergizisGratisResource.php
  └── MakanBergizisGratisResource/Pages/
      ├── ListMakanBergizisGratis.php
      ├── ViewMakanBergizisGratis.php
      ├── CreateMakanBergizisGratis.php
      └── EditMakanBergizisGratis.php

routes/
  └── api.php (updated)

Documentation/
  ├── MAKAN_BERGIZI_GRATIS.md
  ├── MAKAN_BERGIZI_GRATIS_SUMMARY.md
  ├── MAKAN_BERGIZI_GRATIS_QUICK_REFERENCE.md
  └── test-makan-bergizi-gratis.php
```

## 🔑 Key Features

| Feature | Description |
|---------|-------------|
| **Unique Daily Record** | 1 record per day per no_tabungan |
| **Data Snapshot** | Saves complete data (rekening, nasabah, produk, transaksi) |
| **Relations** | Connected to Tabungan & Profile models |
| **Admin Panel** | Full CRUD with filters & search |
| **Activity Log** | All operations logged |
| **Rate Limit** | 60 requests/minute |
| **Webhook Integration** | External notification on checkout |

## 💻 Usage Examples

### cURL
```bash
curl -X POST http://localhost:8000/api/makan-bergizi-gratis \
  -H "Content-Type: application/json" \
  -d '{"no_tabungan": "TB001234"}'
```

### JavaScript
```javascript
fetch('/api/makan-bergizi-gratis', {
  method: 'POST',
  headers: {'Content-Type': 'application/json'},
  body: JSON.stringify({no_tabungan: 'TB001234'})
})
.then(res => res.json())
.then(data => console.log(data));
```

### PHP
```php
use App\Models\MakanBergizisGratis;

// Check if exists today
if (MakanBergizisGratis::existsForToday('TB001234')) {
    // Already recorded
}
```

## 🗄️ Database

### Table: `makan_bergizis_gratis`

| Column | Type | Key |
|--------|------|-----|
| id | bigint | PK |
| tabungan_id | bigint | FK → tabungans |
| profile_id | bigint | FK → profiles |
| no_tabungan | string | Unique (with date) |
| tanggal_pemberian | date | Unique (with no_tabungan) |
| data_rekening | json | - |
| data_nasabah | json | - |
| data_produk | json | - |
| data_transaksi_terakhir | json | - |
| scanned_at | timestamp | - |

## 🎯 Admin Panel

**Location**: Program → Makan Bergizi Gratis

**Features**:
- 📋 List with search & filters
- 👁️ View details
- 🗑️ Delete records
- 📅 Filter by date range
- ⚡ Quick filter "Hari Ini"

## ✅ Testing Checklist

- [ ] Test valid request (first time)
- [ ] Test duplicate request (should fail)
- [ ] Test invalid no_tabungan
- [ ] Test missing no_tabungan
- [ ] Check admin panel display
- [ ] Verify data in database
- [ ] Test filters in admin panel
- [ ] Check activity log

## 🐛 Common Issues

| Issue | Solution |
|-------|----------|
| "Already recorded" | Check if record exists for today |
| "Invalid no_tabungan" | Verify no_tabungan exists in database |
| Foreign key error | Ensure tabungan_id & profile_id are valid |
| 500 error | Check Laravel logs |

## 📊 Response Structure

```json
{
  "success": true/false,
  "message": "...",
  "data": {
    "id": 1,
    "no_tabungan": "...",
    "tanggal_pemberian": "...",
    "rekening": {
      "no_tabungan": "...",
      "produk": "...",
      "saldo": 0,
      "saldo_formatted": "...",
      "status": "...",
      "tanggal_buka": "...",
      "tanggal_buka_iso": "..."
    },
    "nasabah": {
      "nama_lengkap": "...",
      "first_name": "...",
      "last_name": "...",
      "phone": "...",
      "email": "...",
      "whatsapp": "...",
      "address": "..."
    },
    "produk_detail": {
      "id": 0,
      "nama": "...",
      "keterangan": "..."
    },
    "transaksi_terakhir": {
      "kode_transaksi": "...",
      "jenis_transaksi": "...",
      "jenis_transaksi_label": "...",
      "jumlah": 0,
      "jumlah_formatted": "...",
      "tanggal_transaksi": "...",
      "tanggal_transaksi_iso": "...",
      "keterangan": "...",
      "teller": "..."
    },
    "metadata": {
      "scanned_at": "...",
      "scanned_at_formatted": "..."
    }
  }
}
```

## 🔐 Security

- ✅ Rate limiting (60/min)
- ✅ Input validation
- ✅ SQL injection protection
- ✅ Foreign key constraints
- ✅ Unique constraint
- ✅ Activity logging

## 🔗 Webhook Integration

### Configuration
```env
WEBHOOK_URL_BARCODE_TABUNGAN=https://your-endpoint.com/webhook
```

### Features
- ✅ Automatic notification on checkout
- ✅ 10-second timeout with 2 retries
- ✅ Non-blocking (doesn't affect checkout)
- ✅ Comprehensive logging
- ✅ Graceful failure handling

### Testing
```bash
# Test webhook endpoint
php test-webhook-barcode.php
```

**Documentation**: See `WEBHOOK_BARCODE_TABUNGAN.md` for complete details.

## 📞 Quick Commands

```bash
# Run migration
php artisan migrate

# Test API
php test-makan-bergizi-gratis.php

# Test webhook
php test-webhook-barcode.php

# Check routes
php artisan route:list | grep makan-bergizi

# Clear cache
php artisan optimize:clear

# View logs
php artisan pail
```

---

**Quick Reference v1.0** | Last Updated: Oct 9, 2025
