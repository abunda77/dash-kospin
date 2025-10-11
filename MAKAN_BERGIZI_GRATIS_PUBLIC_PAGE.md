# Makan Bergizi Gratis - Public Checkout Page

## Overview

Halaman public berbasis Livewire untuk checkout program Makan Bergizi Gratis. Halaman ini dapat diakses tanpa login dan mendukung dua metode input: manual entry dan QR code scan.

## Features

### 1. Dual Access Method
- **Manual Entry**: Input nomor tabungan secara manual
- **QR Code Scan**: Auto-load data dari URL dengan hash (hasil scan QR code)

### 2. Data Display
Menampilkan informasi lengkap:
- Data Nasabah (nama, telepon, email, alamat)
- Informasi Rekening (no. tabungan, produk, saldo, status)
- Transaksi Terakhir (kode, jenis, jumlah, tanggal)

### 3. Checkout System
- Tombol checkout untuk menyimpan record pemberian
- Validasi: tidak bisa checkout 2x di hari yang sama
- Real-time feedback (loading, success, error states)

### 4. Responsive Design
- Mobile-friendly interface
- Gradient background (green to blue)
- Card-based layout dengan icons
- TailwindCSS styling

## Routes

### Public Access
```php
// Manual entry
GET /makan-bergizi-gratis

// QR Code scan (with hash)
GET /makan-bergizi-gratis/{hash}
```

### Route Definition
```php
Route::get('/makan-bergizi-gratis/{hash?}', App\Livewire\MakanBergizisGratisCheckout::class)
    ->name('makan-bergizi-gratis.checkout');
```

## File Structure

```
app/
├── Livewire/
│   └── MakanBergizisGratisCheckout.php    # Livewire component
resources/
├── views/
│   ├── layouts/
│   │   └── public.blade.php                # Public layout
│   └── livewire/
│       └── makan-bergizis-gratis-checkout.blade.php  # View template
routes/
└── web.php                                  # Route definitions
```

## Component Details

### Livewire Component: `MakanBergizisGratisCheckout`

**Properties:**
- `$hash` - Hash dari QR code (optional)
- `$noTabungan` - Nomor tabungan input
- `$tabunganData` - Data tabungan yang dimuat
- `$loading` - Loading state
- `$error` - Error message
- `$success` - Success message
- `$checkoutLoading` - Checkout loading state
- `$alreadyCheckedOut` - Flag sudah checkout hari ini

**Methods:**
- `mount($hash)` - Initialize component, auto-load jika ada hash
- `loadFromHash()` - Load data dari hash (QR scan)
- `searchTabungan()` - Search data by nomor tabungan
- `loadTabunganData($tabungan)` - Load dan format data tabungan
- `checkout()` - Process checkout dan simpan record
- `resetForm()` - Reset form untuk input baru

## Usage Examples

### 1. Manual Entry Flow
```
1. User akses: https://yourdomain.com/makan-bergizi-gratis
2. Input nomor tabungan di form
3. Klik "Cari Data"
4. Data ditampilkan
5. Klik "Checkout Sekarang"
6. Success message muncul
```

### 2. QR Code Scan Flow
```
1. User scan QR code dari PDF barcode
2. Redirect ke: https://yourdomain.com/makan-bergizi-gratis/{hash}
3. Data auto-load dari hash
4. Data langsung ditampilkan
5. Klik "Checkout Sekarang"
6. Success message muncul
```

## Integration with Existing System

### 1. Hash Generation
Menggunakan `HashidsHelper` yang sama dengan `TabunganBarcodeController`:
```php
$encodedId = HashidsHelper::encode($tabungan->id);
$scanUrl = route('makan-bergizi-gratis.checkout', $encodedId);
```

### 2. Data Storage
Menggunakan model `MakanBergizisGratis` yang sudah ada:
```php
MakanBergizisGratis::create([
    'tabungan_id' => $tabungan->id,
    'profile_id' => $tabungan->profile->id_user,
    'no_tabungan' => $tabungan->no_tabungan,
    'tanggal_pemberian' => today(),
    'data_rekening' => $dataRekening,
    'data_nasabah' => $dataNasabah,
    'data_produk' => $dataProduk,
    'data_transaksi_terakhir' => $dataTransaksiTerakhir,
    'scanned_at' => now(),
]);
```

### 3. Validation
Menggunakan method `existsForToday()` dari model:
```php
$alreadyCheckedOut = MakanBergizisGratis::existsForToday($noTabungan);
```

## UI Components

### 1. Search Form
- Input field untuk nomor tabungan
- Search button dengan loading state
- Validation error display

### 2. Data Cards
- **Nasabah Card**: Green icon, personal info
- **Rekening Card**: Blue icon, account info
- **Transaksi Card**: Purple icon, last transaction

### 3. Checkout Button
- Large gradient button (green to blue)
- Loading spinner during process
- Disabled state after checkout

### 4. Status Messages
- **Error**: Red border-left, error icon
- **Success**: Green border-left, success icon
- **Already Checked Out**: Yellow warning icon

## Security Features

### 1. Hash Encoding
- ID tabungan di-encode menggunakan Hashids
- Tidak expose primary key secara langsung

### 2. Validation
- Server-side validation untuk nomor tabungan
- Check duplicate checkout per hari
- Error handling untuk invalid hash

### 3. Rate Limiting
Bisa ditambahkan di route:
```php
Route::get('/makan-bergizi-gratis/{hash?}', ...)
    ->middleware('throttle:60,1'); // 60 requests per minute
```

## Styling

### Color Scheme
- **Primary**: Green (#059669) - Success, checkout
- **Secondary**: Blue (#2563eb) - Info, account
- **Accent**: Purple (#9333ea) - Transaction
- **Warning**: Yellow (#eab308) - Already checked out
- **Error**: Red (#dc2626) - Errors

### Responsive Breakpoints
- Mobile: < 768px (single column)
- Desktop: ≥ 768px (two columns for data)

## Testing

### Manual Testing Checklist

1. **Manual Entry**
   - [ ] Input valid nomor tabungan
   - [ ] Input invalid nomor tabungan
   - [ ] Empty input validation
   - [ ] Checkout success
   - [ ] Duplicate checkout prevention

2. **QR Code Scan**
   - [ ] Valid hash auto-load
   - [ ] Invalid hash error
   - [ ] Expired hash handling
   - [ ] Checkout from scanned data

3. **UI/UX**
   - [ ] Loading states display correctly
   - [ ] Error messages clear
   - [ ] Success messages clear
   - [ ] Mobile responsive
   - [ ] Desktop layout proper

4. **Edge Cases**
   - [ ] Non-existent tabungan
   - [ ] Inactive account
   - [ ] Already checked out today
   - [ ] Network errors

### Test URLs

```bash
# Manual entry
http://localhost:8000/makan-bergizi-gratis

# QR scan (replace {hash} with actual hash)
http://localhost:8000/makan-bergizi-gratis/{hash}

# Get hash for testing
http://localhost:8000/test-qr/{tabungan_id}
```

## Logging

Component logs important events:
```php
// Success checkout
Log::info('Makan Bergizi Gratis checkout success', [
    'record_id' => $record->id,
    'no_tabungan' => $this->noTabungan,
    'tanggal' => today()->format('Y-m-d')
]);

// Error during checkout
Log::error('Error during checkout', [
    'no_tabungan' => $this->noTabungan,
    'error' => $e->getMessage()
]);
```

## Future Enhancements

### Potential Features
1. **Print Receipt**: Generate PDF receipt after checkout
2. **SMS Notification**: Send SMS confirmation to member
3. **Photo Upload**: Upload photo saat checkout
4. **Location Tracking**: Record GPS location
5. **Signature Capture**: Digital signature confirmation
6. **Multi-language**: Support English/Indonesian toggle
7. **Offline Mode**: PWA with offline capability
8. **Statistics Dashboard**: Public stats page

### Performance Optimization
1. Cache tabungan data for faster lookup
2. Lazy load transaction history
3. Image optimization for mobile
4. CDN for static assets

## Troubleshooting

### Common Issues

**1. Layout not found error**
```
Error: View [layouts.public] not found
```
Solution: Pastikan file `resources/views/layouts/public.blade.php` exists

**2. Livewire not working**
```
Error: Livewire component not found
```
Solution: Run `php artisan livewire:discover`

**3. Styles not loading**
```
Error: CSS not applied
```
Solution: Run `npm run dev` atau `npm run build`

**4. Hash decode fails**
```
Error: Invalid or expired barcode
```
Solution: Check HashidsHelper configuration dan salt

## Deployment Checklist

- [ ] Run `npm run build` untuk production assets
- [ ] Clear cache: `php artisan optimize:clear`
- [ ] Test on production URL
- [ ] Verify QR codes work with production domain
- [ ] Check mobile responsiveness
- [ ] Test rate limiting
- [ ] Monitor logs for errors
- [ ] Setup error tracking (Sentry, etc.)

## Support

Untuk pertanyaan atau issues:
1. Check logs: `storage/logs/laravel.log`
2. Review Livewire documentation
3. Check TailwindCSS classes
4. Verify database records in `makan_bergizis_gratis` table

---

**Created**: 2025-10-09
**Last Updated**: 2025-10-09
**Version**: 1.0.0
