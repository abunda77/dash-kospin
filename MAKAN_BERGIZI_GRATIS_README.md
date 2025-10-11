# Makan Bergizi Gratis - Quick Start Guide

## 🚀 Quick Start

### Installation
```bash
# Migration sudah dijalankan ✅
php artisan migrate
```

### API Endpoints

#### 1. Create Record (POST)
```bash
curl -X POST http://localhost:8000/api/makan-bergizi-gratis \
  -H "Content-Type: application/json" \
  -d '{"no_tabungan": "TB001234"}'
```

#### 2. Get List (GET)
```bash
curl http://localhost:8000/api/makan-bergizi-gratis
curl http://localhost:8000/api/makan-bergizi-gratis?tanggal=2025-10-09
curl http://localhost:8000/api/makan-bergizi-gratis?dari=2025-10-01&sampai=2025-10-31
```

#### 3. Get Detail (GET)
```bash
curl http://localhost:8000/api/makan-bergizi-gratis/1
```

#### 4. Check Today (POST)
```bash
curl -X POST http://localhost:8000/api/makan-bergizi-gratis/check-today \
  -H "Content-Type: application/json" \
  -d '{"no_tabungan": "TB001234"}'
```

### Admin Panel

**Access**: Login → Program → Makan Bergizi Gratis

**Features**:
- 📊 Stats widget (Hari Ini, Minggu Ini, Bulan Ini, Total)
- 🔍 Search & Filter
- 📅 Date range filter
- ⚡ Quick filter "Hari Ini"
- 👁️ View details
- 🗑️ Delete records

### Testing

```bash
# Update no_tabungan di test script
# Edit: test-makan-bergizi-gratis.php

# Run test
php test-makan-bergizi-gratis.php
```

### Cleanup Old Data

```bash
# Delete records older than 90 days
php artisan mbg:cleanup

# Delete records older than 30 days
php artisan mbg:cleanup --days=30
```

## 📋 Key Rules

✅ **1 record per day per no_tabungan**  
✅ **Duplicate akan ditolak dengan HTTP 409**  
✅ **Data snapshot lengkap disimpan dalam JSON**  
✅ **Rate limit: 60 requests/minute**

## 📚 Documentation

- **Complete**: `MAKAN_BERGIZI_GRATIS.md`
- **Summary**: `MAKAN_BERGIZI_GRATIS_SUMMARY.md`
- **Quick Reference**: `MAKAN_BERGIZI_GRATIS_QUICK_REFERENCE.md`
- **Final Report**: `MAKAN_BERGIZI_GRATIS_FINAL_REPORT.md`

## 🎯 Response Codes

| Code | Meaning |
|------|---------|
| 200 | Success (GET) |
| 201 | Created (POST) |
| 409 | Already recorded today |
| 422 | Validation error |
| 500 | Server error |

## 🔧 Troubleshooting

**"Already recorded today"**
→ Check admin panel, record sudah ada untuk hari ini

**"Invalid no_tabungan"**
→ Pastikan no_tabungan ada di database

**Foreign key error**
→ Pastikan tabungan_id dan profile_id valid

## ✅ Status

**Implementation**: ✅ COMPLETE  
**Migration**: ✅ EXECUTED  
**Testing**: ✅ READY  
**Production**: ✅ READY TO DEPLOY

---

**Version**: 1.0.0 | **Date**: October 9, 2025
