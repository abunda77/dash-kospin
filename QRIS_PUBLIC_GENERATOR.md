# QRIS Public Generator

## Overview

Halaman public untuk generate QRIS dinamis dari QRIS static. Dapat diakses tanpa login.

## URL Access

```
http://your-domain.com/qris-generator
```

## Features

-   ✅ Pilih QRIS tersimpan dari database
-   ✅ Input manual QRIS static code
-   ✅ Set nominal transaksi
-   ✅ Tambah biaya (Rupiah atau Persentase)
-   ✅ Generate QR code image
-   ✅ Download QR code
-   ✅ Copy QRIS string
-   ✅ Responsive design
-   ✅ Real-time validation

## Files Created

### 1. Livewire Component

**File:** `app/Livewire/QrisPublicGenerator.php`

-   Handle form input dan validation
-   Generate QRIS dinamis dari static
-   Generate QR code image menggunakan Endroid QR Code
-   Parse merchant name dari QRIS string
-   Calculate CRC16 checksum

### 2. Blade View

**File:** `resources/views/livewire/qris-public-generator.blade.php`

-   Modern UI dengan Tailwind CSS
-   Responsive layout (mobile-friendly)
-   Form input dengan validation feedback
-   QR code preview
-   Download button
-   Copy QRIS string functionality

### 3. Route

**File:** `routes/web.php`

```php
Route::get('/qris-generator', App\Livewire\QrisPublicGenerator::class)
    ->name('qris.public-generator');
```

## How It Works

### 1. Input QRIS Static

User dapat:

-   Pilih dari QRIS tersimpan (dari database `qris_statics`)
-   Atau paste manual QRIS static code

### 2. Set Amount & Fee

-   **Amount**: Nominal transaksi (required, min: 1)
-   **Fee Type**: Rupiah atau Persentase
-   **Fee Value**: Nilai biaya (optional)

### 3. Generate Process

1. Validate input
2. Parse merchant name dari QRIS string
3. Convert QRIS static (01) ke dynamic (12)
4. Inject amount tag (54)
5. Inject fee tag (55) jika ada
6. Calculate CRC16 checksum
7. Generate QR code image
8. Save to `storage/app/public/qris-generated/`

### 4. Result Display

-   QR code image (400x400px)
-   Merchant name
-   QRIS dynamic string (copyable)
-   Download button

## Technical Details

### QRIS Format

```
[Payload Indicator][Point of Initiation][Merchant Account][...][Amount][Fee][Country Code][...][CRC]
```

### Tags Used

-   `01`: Payload Format Indicator (11=static, 12=dynamic)
-   `54`: Transaction Amount
-   `55`: Fee (02=fixed, 03=percentage)
-   `58`: Country Code (ID)
-   `59`: Merchant Name
-   `63`: CRC16

### CRC16 Calculation

-   Polynomial: 0x1021 (CCITT)
-   Initial value: 0xFFFF
-   Final XOR: None

## Storage

QR code images disimpan di:

```
storage/app/public/qris-generated/
```

Format filename:

```
qris-public-YmdHis-{uniqid}.png
```

## Dependencies

-   **Endroid QR Code**: Generate QR code image
-   **Livewire**: Reactive components
-   **Tailwind CSS**: Styling
-   **Laravel Storage**: File management

## Usage Example

### 1. Access Page

```
http://localhost:8000/qris-generator
```

### 2. Fill Form

-   Static QRIS: `00020101021126...` (paste atau pilih)
-   Amount: `50000`
-   Fee Type: `Rupiah`
-   Fee Value: `1000`

### 3. Generate

Click "Generate QRIS" button

### 4. Result

-   QR code displayed
-   QRIS string: `00020101021254...`
-   Download available

## Security

-   ✅ Input validation
-   ✅ Error handling
-   ✅ Safe file storage
-   ✅ No authentication required (public access)
-   ✅ Rate limiting (via middleware if needed)

## Future Enhancements

-   [ ] Add rate limiting
-   [ ] Add QRIS validation
-   [ ] Add transaction history
-   [ ] Add email/WhatsApp sharing
-   [ ] Add custom QR code styling
-   [ ] Add expiry time for dynamic QRIS

## Testing

### Manual Test

1. Visit `/qris-generator`
2. Select saved QRIS or paste static code
3. Enter amount (e.g., 10000)
4. Click Generate
5. Verify QR code displayed
6. Test download
7. Test copy string

### Test Cases

-   ✅ Valid QRIS static input
-   ✅ Invalid QRIS format
-   ✅ Empty amount
-   ✅ Negative amount
-   ✅ Fee calculation (Rupiah)
-   ✅ Fee calculation (Percentage)
-   ✅ QR code generation
-   ✅ Download functionality
-   ✅ Reset form

## Troubleshooting

### QR Code Not Generated

-   Check storage permissions: `storage/app/public/qris-generated/`
-   Run: `php artisan storage:link`
-   Check logs: `storage/logs/laravel.log`

### Invalid QRIS Format

-   Ensure QRIS string contains `5802ID`
-   Check QRIS length (minimum 4 characters)
-   Verify CRC checksum

### Image Not Displaying

-   Check storage link: `php artisan storage:link`
-   Verify file exists in `storage/app/public/qris-generated/`
-   Check file permissions

## Commands

### Create Storage Link

```bash
php artisan storage:link
```

### Clear Generated QR Codes

```bash
# Windows
del /Q storage\app\public\qris-generated\*

# Linux/Mac
rm -rf storage/app/public/qris-generated/*
```

### Check Logs

```bash
tail -f storage/logs/laravel.log
```

## API Integration (Optional)

Jika ingin membuat API endpoint:

```php
// routes/api.php
Route::post('/qris/generate', [QrisController::class, 'generate']);
```

```php
// app/Http/Controllers/QrisController.php
public function generate(Request $request)
{
    $validated = $request->validate([
        'static_qris' => 'required|string',
        'amount' => 'required|numeric|min:1',
        'fee_type' => 'nullable|in:Rupiah,Persentase',
        'fee_value' => 'nullable|numeric|min:0',
    ]);

    // Use QrisHelper or service class
    $dynamicQris = QrisHelper::generateDynamic(
        $validated['static_qris'],
        $validated['amount'],
        $validated['fee_type'] ?? 'Rupiah',
        $validated['fee_value'] ?? 0
    );

    return response()->json([
        'success' => true,
        'data' => [
            'dynamic_qris' => $dynamicQris,
            'qr_image_url' => asset('storage/qris-generated/...')
        ]
    ]);
}
```

## Notes

-   Public access (no authentication)
-   Responsive design
-   Real-time validation
-   Auto-save QR images
-   Clean UI/UX
