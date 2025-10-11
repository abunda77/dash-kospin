# Makan Bergizi Gratis - Public Checkout Page

> Halaman public checkout berbasis Livewire untuk program Makan Bergizi Gratis dengan dual input method (manual entry & QR code scan)

## 🎯 Overview

Fitur ini memungkinkan checkout program Makan Bergizi Gratis melalui halaman public yang dapat diakses tanpa login. Mendukung dua metode input:
1. **Manual Entry** - Input nomor tabungan secara manual
2. **QR Code Scan** - Auto-load data dari URL dengan hash

## ✨ Features

- ✅ Public access (no authentication required)
- ✅ Dual input method (manual & QR scan)
- ✅ Real-time data display
- ✅ Checkout processing with validation
- ✅ Duplicate prevention (1x per day)
- ✅ Mobile responsive design
- ✅ Secure hash-based URLs
- ✅ Comprehensive logging
- ✅ Error handling & feedback

## 📁 File Structure

```
app/
├── Livewire/
│   └── MakanBergizisGratisCheckout.php          # Main component
resources/
├── views/
│   ├── layouts/
│   │   └── public.blade.php                      # Public layout (optional)
│   └── livewire/
│       └── makan-bergizis-gratis-checkout.blade.php  # View template
routes/
└── web.php                                        # Route definition
test-public-checkout.php                           # Test script
```

## 🚀 Quick Start

### 1. Test the Implementation

```bash
# Run test script
php test-public-checkout.php
```

This will show you:
- Sample tabungan data
- Generated URLs for testing
- Checkout status
- Test data

### 2. Start Development Server

```bash
# Terminal 1: Laravel server
php artisan serve

# Terminal 2: Vite dev server
npm run dev
```

### 3. Access the Page

**Manual Entry:**
```
http://localhost:8000/makan-bergizi-gratis
```

**QR Code Scan:**
```
http://localhost:8000/makan-bergizi-gratis/{hash}
```

## 📱 Usage

### Method 1: Manual Entry

1. Open `http://localhost:8000/makan-bergizi-gratis`
2. Input nomor tabungan
3. Click "Cari Data"
4. Verify displayed data
5. Click "Checkout Sekarang"
6. Success confirmation appears

### Method 2: QR Code Scan

1. Generate QR code from Filament:
   - Login to admin panel
   - Go to Tabungan > View detail
   - Click "Print Barcode"
   - PDF downloads with QR code

2. Scan QR code or visit URL directly
3. Data auto-loads
4. Click "Checkout Sekarang"
5. Success confirmation appears

## 🎨 UI Preview

### Desktop View
```
┌─────────────────────────────────────┐
│   Makan Bergizi Gratis              │
│   Program Pemberian Makan Bergizi   │
├─────────────────────────────────────┤
│   [Search Form]                     │
│   Nomor Tabungan: [________]        │
│   [Cari Data Button]                │
├─────────────────────────────────────┤
│   📋 Data Nasabah                   │
│   Nama: John Doe                    │
│   Telepon: 08123456789              │
├─────────────────────────────────────┤
│   💳 Informasi Rekening             │
│   No. Tabungan: TAB001              │
│   Saldo: Rp 1.000.000               │
├─────────────────────────────────────┤
│   📊 Transaksi Terakhir             │
│   Setoran: Rp 500.000               │
│   Tanggal: 09/10/2025               │
├─────────────────────────────────────┤
│   [✓ Checkout Sekarang]             │
└─────────────────────────────────────┘
```

### Mobile View
Fully responsive dengan single column layout

## 🔧 Configuration

### Route Configuration

File: `routes/web.php`

```php
Route::get('/makan-bergizi-gratis/{hash?}', App\Livewire\MakanBergizisGratisCheckout::class)
    ->name('makan-bergizi-gratis.checkout');
```

### Optional: Add Rate Limiting

```php
Route::get('/makan-bergizi-gratis/{hash?}', App\Livewire\MakanBergizisGratisCheckout::class)
    ->middleware('throttle:60,1') // 60 requests per minute
    ->name('makan-bergizi-gratis.checkout');
```

## 🧪 Testing

### Run Test Script

```bash
php test-public-checkout.php
```

### Manual Testing Checklist

**Manual Entry:**
- [ ] Input valid nomor tabungan
- [ ] Input invalid nomor tabungan
- [ ] Empty input validation
- [ ] Checkout success
- [ ] Duplicate checkout prevention

**QR Code Scan:**
- [ ] Valid hash auto-load
- [ ] Invalid hash error
- [ ] Checkout from scanned data

**UI/UX:**
- [ ] Loading states display
- [ ] Error messages clear
- [ ] Success messages clear
- [ ] Mobile responsive
- [ ] Desktop layout proper

### Test URLs

```bash
# Manual entry
http://localhost:8000/makan-bergizi-gratis

# QR scan (get hash from test script)
http://localhost:8000/makan-bergizi-gratis/{hash}

# Generate hash for testing
http://localhost:8000/test-qr/{tabungan_id}
```

## 📊 Database

### Table: makan_bergizis_gratis

Records are stored with complete snapshot data:

```sql
- id (bigint, PK)
- tabungan_id (bigint, FK)
- profile_id (bigint, FK)
- no_tabungan (string)
- tanggal_pemberian (date)
- data_rekening (json)
- data_nasabah (json)
- data_produk (json)
- data_transaksi_terakhir (json, nullable)
- scanned_at (timestamp)
- created_at, updated_at
```

### Check Records

```bash
php artisan tinker
```

```php
// Today's checkouts
MakanBergizisGratis::whereDate('tanggal_pemberian', today())->count();

// Latest record
MakanBergizisGratis::latest()->first();

// Check specific tabungan
MakanBergizisGratis::where('no_tabungan', 'TAB001')->get();
```

## 🔐 Security

### Implemented Security Features

✅ **Hash Encoding**: IDs encoded using Hashids  
✅ **Server-side Validation**: All inputs validated  
✅ **Duplicate Prevention**: Can't checkout twice per day  
✅ **Error Logging**: All errors logged  
✅ **No ID Exposure**: Primary keys never exposed  

### Optional Security Enhancements

- Add rate limiting
- Add CAPTCHA
- Add IP logging
- Add session tracking
- Add webhook notifications

## 📝 Logging

### View Logs

```bash
# Real-time monitoring
php artisan pail

# Or tail log file
tail -f storage/logs/laravel.log
```

### Log Events

Component logs:
- Successful checkouts
- Failed checkouts
- Hash decode errors
- Data loading errors

## 🐛 Troubleshooting

### Common Issues

**1. Livewire not working**
```bash
php artisan livewire:discover
php artisan optimize:clear
```

**2. Styles not loading**
```bash
npm run build
# or for development
npm run dev
```

**3. Hash decode fails**
```bash
php artisan tinker
>>> HashidsHelper::encode(1);
>>> HashidsHelper::decode('hash_here');
```

**4. Checkout fails**
```bash
# Check logs
tail -f storage/logs/laravel.log

# Check database
php artisan tinker
>>> MakanBergizisGratis::latest()->first();
```

## 🚀 Production Deployment

### Pre-deployment Checklist

- [ ] Run `npm run build`
- [ ] Set `APP_ENV=production`
- [ ] Set `APP_DEBUG=false`
- [ ] Configure `APP_URL`
- [ ] Run `php artisan optimize`
- [ ] Test QR codes with production URL
- [ ] Setup SSL (HTTPS)
- [ ] Configure rate limiting
- [ ] Setup error monitoring

### Deploy Commands

```bash
# Build assets
npm run build

# Optimize Laravel
php artisan optimize
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Set permissions
chmod -R 755 storage bootstrap/cache
```

### Post-deployment Testing

1. Test manual entry
2. Test QR code scan
3. Test duplicate prevention
4. Check mobile responsiveness
5. Verify data saves correctly
6. Monitor logs for errors

## 📚 Documentation

### Available Documentation

- **IMPLEMENTATION_SUMMARY.md** - Complete implementation summary
- **MAKAN_BERGIZI_GRATIS_PUBLIC_PAGE.md** - Detailed feature documentation
- **MAKAN_BERGIZI_GRATIS_QUICKSTART.md** - Quick start guide
- **MAKAN_BERGIZI_GRATIS_CHECKOUT_IMPLEMENTATION.md** - Technical implementation details

### Quick Reference

```bash
# Test
php test-public-checkout.php

# Dev Server
php artisan serve
npm run dev

# Clear Cache
php artisan optimize:clear

# View Logs
php artisan pail

# Check Routes
php artisan route:list | grep makan
```

## 🎯 Use Cases

### Use Case 1: Event Registration
- Setup tablet at entrance
- Staff inputs nomor tabungan
- System displays member info
- Staff verifies and clicks checkout
- Member receives meal

### Use Case 2: Self-Service Kiosk
- Setup kiosk with QR scanner
- Member scans QR code
- System auto-loads data
- Member clicks checkout
- System records attendance

### Use Case 3: Mobile Distribution
- Staff with mobile phones
- Opens URL on mobile
- Scans QR or inputs manually
- Checkouts on-the-go
- System syncs to database

## 🔄 Integration

### With Existing System

✅ Uses `HashidsHelper` for secure ID encoding  
✅ Uses `MakanBergizisGratis` model  
✅ Uses `Tabungan` model  
✅ Uses `TransaksiTabungan` model  
✅ Compatible with `TabunganBarcodeController`  
✅ Data viewable in Filament admin panel  

### Data Flow

```
User Input → Load Data → Validate → Display → Checkout → Save → Success
```

## 🎨 Customization

### Change Colors

Edit `resources/views/livewire/makan-bergizis-gratis-checkout.blade.php`:

```html
<!-- Change primary button color -->
<button class="bg-green-600 hover:bg-green-700">
<!-- To blue -->
<button class="bg-blue-600 hover:bg-blue-700">
```

### Change Title

Edit the blade template header section.

### Add Logo

Add logo image in the header section of the blade template.

## 📈 Future Enhancements

### Potential Features

- [ ] PDF receipt generation
- [ ] SMS notification
- [ ] Photo upload during checkout
- [ ] GPS location tracking
- [ ] Digital signature
- [ ] Offline PWA mode
- [ ] Multi-language support
- [ ] Public statistics dashboard

## 📞 Support

### Getting Help

1. Check logs: `storage/logs/laravel.log`
2. Run test: `php test-public-checkout.php`
3. Review documentation files
4. Check admin panel for records

### Report Issues

Include in your report:
- Error message
- Steps to reproduce
- Log entries
- Browser/device info
- Screenshots

## 📄 License

This feature is part of the Dash-Kospin application.

---

## ✅ Status

**Status**: ✅ COMPLETE & READY FOR TESTING  
**Version**: 1.0.0  
**Date**: 2025-10-09

## 🎉 Summary

You now have a fully functional public checkout page for the Makan Bergizi Gratis program with:

- ✅ Dual input method (manual & QR scan)
- ✅ Mobile responsive design
- ✅ Secure implementation
- ✅ Duplicate prevention
- ✅ Comprehensive logging
- ✅ Full documentation

**Next Steps:**
1. Run `php test-public-checkout.php`
2. Test in browser
3. Generate QR codes from Filament
4. Test QR scan flow
5. Deploy to production

Happy coding! 🚀
