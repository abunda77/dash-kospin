# Makan Bergizi Gratis - Quick Start Guide

## 🚀 Quick Setup

### 1. Verify Installation

Pastikan semua file sudah ada:

```bash
# Check Livewire component
php artisan livewire:discover

# Check routes
php artisan route:list | grep "makan-bergizi-gratis"
```

### 2. Build Assets

```bash
# Development
npm run dev

# Production
npm run build
```

### 3. Clear Cache

```bash
php artisan optimize:clear
```

## 📱 Testing

### Run Test Script

```bash
php test-public-checkout.php
```

Output akan menampilkan:
- Sample tabungan untuk testing
- Generated URLs (manual & QR)
- Checkout status
- Test data

### Manual Testing

#### A. Test Manual Entry

1. Buka browser: `http://localhost:8000/makan-bergizi-gratis`
2. Input nomor tabungan (dari test script)
3. Klik "Cari Data"
4. Verify data ditampilkan dengan benar
5. Klik "Checkout Sekarang"
6. Verify success message muncul

#### B. Test QR Code Scan

1. Generate QR code dari Filament:
   - Login ke admin panel
   - Buka Tabungan > View detail
   - Klik "Print Barcode"
   - PDF akan ter-download dengan QR code

2. Scan QR code atau copy URL dari test script
3. Data harus auto-load
4. Klik "Checkout Sekarang"
5. Verify success message

#### C. Test Duplicate Prevention

1. Checkout dengan nomor tabungan
2. Coba checkout lagi dengan nomor yang sama
3. Harus muncul warning "Sudah Checkout Hari Ini"

## 🔗 URLs

### Public Access

```
Manual Entry:
http://localhost:8000/makan-bergizi-gratis

QR Code Scan:
http://localhost:8000/makan-bergizi-gratis/{hash}
```

### Admin Panel

```
View Records:
http://localhost:8000/admin/makan-bergizis-gratis

Statistics:
http://localhost:8000/admin (see widget)
```

## 📊 Verify Data

### Check Database

```bash
php artisan tinker
```

```php
// Get today's checkouts
MakanBergizisGratis::whereDate('tanggal_pemberian', today())->count();

// Get latest record
MakanBergizisGratis::latest()->first();

// Check specific tabungan
MakanBergizisGratis::where('no_tabungan', 'TAB001')->get();
```

### View Logs

```bash
# Real-time log monitoring
php artisan pail

# Or view log file
tail -f storage/logs/laravel.log
```

## 🎨 Customization

### Change Colors

Edit `resources/views/livewire/makan-bergizis-gratis-checkout.blade.php`:

```html
<!-- Primary button color -->
<button class="bg-green-600 hover:bg-green-700">

<!-- Change to blue -->
<button class="bg-blue-600 hover:bg-blue-700">
```

### Change Title

Edit Livewire component `app/Livewire/MakanBergizisGratisCheckout.php`:

```php
public function render()
{
    return view('livewire.makan-bergizis-gratis-checkout')
        ->layout('layouts.public', ['title' => 'Your Custom Title']);
}
```

### Add Logo

Edit `resources/views/layouts/public.blade.php`:

```html
<body>
    <div class="container">
        <img src="{{ asset('images/logo.png') }}" alt="Logo">
        {{ $slot }}
    </div>
</body>
```

## 🔧 Troubleshooting

### Issue: "View [layouts.public] not found"

```bash
# Check file exists
ls -la resources/views/layouts/public.blade.php

# Clear view cache
php artisan view:clear
```

### Issue: "Livewire component not found"

```bash
# Discover Livewire components
php artisan livewire:discover

# Clear cache
php artisan optimize:clear
```

### Issue: Styles not loading

```bash
# Rebuild assets
npm run build

# Check Vite is running (dev)
npm run dev
```

### Issue: Hash decode fails

```bash
# Check HashidsHelper configuration
php artisan tinker

# Test encode/decode
HashidsHelper::encode(1);
HashidsHelper::decode('generated_hash');
```

### Issue: Checkout fails

Check logs:
```bash
tail -f storage/logs/laravel.log
```

Common causes:
- Database connection issue
- Missing profile data
- Invalid tabungan status

## 📱 Mobile Testing

### Test on Mobile Device

1. Get your local IP:
```bash
# Windows
ipconfig

# Linux/Mac
ifconfig
```

2. Access from mobile:
```
http://YOUR_IP:8000/makan-bergizi-gratis
```

3. Test QR scanner apps:
   - iOS: Camera app (built-in)
   - Android: Google Lens, QR Scanner apps

## 🚀 Production Deployment

### Pre-deployment Checklist

- [ ] Run `npm run build`
- [ ] Set `APP_ENV=production` in `.env`
- [ ] Set `APP_DEBUG=false` in `.env`
- [ ] Configure proper `APP_URL` in `.env`
- [ ] Run `php artisan optimize`
- [ ] Test QR codes with production URL
- [ ] Setup SSL certificate (HTTPS)
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
6. Check admin panel access

## 📈 Monitoring

### Daily Checks

```bash
# Today's checkout count
php artisan tinker
>>> MakanBergizisGratis::whereDate('tanggal_pemberian', today())->count();

# View recent checkouts
>>> MakanBergizisGratis::latest()->take(10)->get(['no_tabungan', 'scanned_at']);
```

### Weekly Cleanup

```bash
# Clean old records (if needed)
php artisan makan-bergizi-gratis:cleanup --days=90
```

## 🎯 Common Use Cases

### Use Case 1: Event Registration

Setup tablet/phone at entrance:
1. Open `/makan-bergizi-gratis`
2. Staff inputs nomor tabungan
3. Verify member info
4. Click checkout
5. Member receives meal

### Use Case 2: Self-Service Kiosk

Setup kiosk with QR scanner:
1. Member scans their QR code
2. Data auto-loads
3. Member clicks checkout
4. System records attendance

### Use Case 3: Mobile Distribution

Staff with mobile phones:
1. Open URL on mobile
2. Input nomor tabungan manually
3. Or scan QR code
4. Checkout on-the-go

## 📞 Support

### Get Help

1. Check logs: `storage/logs/laravel.log`
2. Run test script: `php test-public-checkout.php`
3. Review documentation: `MAKAN_BERGIZI_GRATIS_PUBLIC_PAGE.md`
4. Check database records in admin panel

### Report Issues

Include in your report:
- Error message
- Steps to reproduce
- Log entries
- Browser/device info
- Screenshots

---

**Quick Reference:**

```bash
# Test
php test-public-checkout.php

# Dev server
php artisan serve
npm run dev

# Clear cache
php artisan optimize:clear

# View logs
php artisan pail

# Check routes
php artisan route:list | grep makan
```

Happy coding! 🎉
