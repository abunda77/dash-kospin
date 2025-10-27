# QRIS Image Generation - Fix

## ❌ Error

```
Call to undefined method Endroid\QrCode\QrCode::create()
```

## 🔍 Root Cause

Syntax yang digunakan adalah untuk versi lama endroid/qr-code. Versi 6.x menggunakan `Builder` pattern, bukan static `create()` method.

## ✅ Solution

### Before (Wrong - v4/v5 syntax):

```php
$qrCode = \Endroid\QrCode\QrCode::create($this->dynamicQris)
    ->setSize(400)
    ->setMargin(10);

$writer = new \Endroid\QrCode\Writer\PngWriter;
$result = $writer->write($qrCode);
```

### After (Correct - v6 syntax):

```php
$builder = new \Endroid\QrCode\Builder\Builder(
    writer: new \Endroid\QrCode\Writer\PngWriter(),
    writerOptions: [],
    validateResult: false,
    data: $this->dynamicQris,
    encoding: new \Endroid\QrCode\Encoding\Encoding('UTF-8'),
    size: 400,
    margin: 10,
);
$result = $builder->build();
```

## 📦 Library Version

**Installed:** endroid/qr-code v6.0.9

**Documentation:** https://github.com/endroid/qr-code

## 🔧 Changes Made

**File:** `app/Filament/Pages/QrisDynamicGenerator.php`

```php
protected function generateQrImage(): void
{
    if (! $this->dynamicQris) {
        return;
    }

    try {
        // Use Builder with named parameters for v6
        $builder = new \Endroid\QrCode\Builder\Builder(
            writer: new \Endroid\QrCode\Writer\PngWriter(),
            writerOptions: [],
            validateResult: false,
            data: $this->dynamicQris,
            encoding: new \Endroid\QrCode\Encoding\Encoding('UTF-8'),
            size: 400,
            margin: 10,
        );

        $result = $builder->build();

        // Save to storage
        $filename = 'qris-dynamic-'.now()->format('YmdHis').'-'.uniqid().'.png';
        \Storage::disk('public')->put('qris-generated/'.$filename, $result->getString());

        // Store filename in session for download
        session(['last_generated_qr' => $filename]);

        \Log::info('QR code image generated: '.$filename);
    } catch (\Exception $e) {
        \Log::error('Error generating QR image: '.$e->getMessage());
        \Log::error('Stack trace: '.$e->getTraceAsString());
    }
}
```

## ✨ Key Differences v6

| Feature  | v4/v5              | v6                  |
| -------- | ------------------ | ------------------- |
| Creation | `QrCode::create()` | `Builder::create()` |
| Data     | Constructor param  | `->data()`          |
| Size     | `->setSize()`      | `->size()`          |
| Margin   | `->setMargin()`    | `->margin()`        |
| Writer   | Separate class     | Built-in            |
| Build    | `$writer->write()` | `->build()`         |

## 🎯 Builder Pattern Benefits

✅ **Fluent API** - Chain methods easily  
✅ **Type Safety** - Better IDE support  
✅ **Flexibility** - Easy to customize  
✅ **Modern** - Latest PHP standards

## 📝 Common Builder Options

```php
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Logo\Logo;

$builder = new Builder(
    writer: new PngWriter(),
    data: $qrisString,
    encoding: new Encoding('UTF-8'),
    errorCorrectionLevel: ErrorCorrectionLevel::High,
    size: 400,
    margin: 10,
    roundBlockSizeMode: RoundBlockSizeMode::Margin,
    foregroundColor: new Color(0, 0, 0),
    backgroundColor: new Color(255, 255, 255),
    logo: new Logo(path: '/path/to/logo.png', resizeToWidth: 50),
);

$result = $builder->build();
```

## 🧪 Testing

### Test Generation:

1. Navigate to: Payment > QRIS Generator
2. Fill form and generate
3. Check log: `tail -f storage/logs/laravel.log`

### Expected Log:

```
[2025-10-27 XX:XX:XX] local.INFO: QR code image generated: qris-dynamic-20251027XXXXXX-abc123.png
```

### Expected Result:

-   ✅ QR code image displayed
-   ✅ Image saved in `storage/app/public/qris-generated/`
-   ✅ Download button works
-   ✅ No errors

## 📚 Documentation Updated

-   ✅ `QRIS_IMAGE_GENERATION.md` - Updated with v6 syntax
-   ✅ `QRIS_IMAGE_FIX.md` - This file

## ✅ Status

**Fix Applied:** ✅ **Complete**

-   ✅ Code updated to v6 syntax
-   ✅ No diagnostic errors
-   ✅ Documentation updated
-   ✅ Ready to test

**Test now:** Generate dynamic QRIS and download as PNG image!
