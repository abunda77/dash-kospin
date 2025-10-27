# Test QRIS Upload Feature

## 🧪 Testing Checklist

### Pre-requisites

-   ✅ Library installed: `khanamiryan/qrcode-detector-decoder`
-   ✅ GD extension enabled
-   ✅ Storage link created: `php artisan storage:link`
-   ✅ Cache cleared

### Test 1: Upload Valid QRIS Image

**Steps:**

1. Login ke admin panel
2. Navigate to: Payment > Static QRIS
3. Click: New Static QRIS
4. Fill Name: "Test QRIS Upload"
5. Upload QRIS image (PNG/JPG)
6. Wait for processing

**Expected Result:**

-   ✅ Success notification: "QRIS Detected Successfully"
-   ✅ Field "Static QRIS String" auto-filled
-   ✅ Field "Merchant Name" auto-filled
-   ✅ No error in log

**Log Check:**

```bash
tail -f storage/logs/laravel.log
```

Should see:

```
Processing uploaded file from: C:\Users\...\Temp\phpXXXX.tmp
QRIS successfully extracted: 00020101...
```

### Test 2: Upload Invalid Image (Not QR Code)

**Steps:**

1. Upload regular image (not QR code)

**Expected Result:**

-   ⚠️ Warning notification: "Failed to Read QRIS"
-   ⚠️ Message: "Could not extract QRIS string from image. Please paste manually."
-   ✅ Form still usable (can paste manually)

### Test 3: Manual Paste (Fallback)

**Steps:**

1. Skip image upload
2. Paste QRIS string directly in "Static QRIS String" field
3. Tab out or click elsewhere

**Expected Result:**

-   ✅ Field "Merchant Name" auto-filled
-   ✅ No errors

### Test 4: Edit Existing QRIS

**Steps:**

1. Edit existing QRIS record
2. Upload new image
3. Save

**Expected Result:**

-   ✅ Old image replaced
-   ✅ QRIS string updated
-   ✅ Merchant name updated

### Test 5: View QRIS Detail

**Steps:**

1. Click eye icon on QRIS record
2. View modal opens

**Expected Result:**

-   ✅ Image displayed
-   ✅ QRIS string shown
-   ✅ Copy button works

### Test 6: Generate Dynamic from Static

**Steps:**

1. From Static QRIS table
2. Click "Generate Dynamic" button
3. Should redirect to QRIS Generator page

**Expected Result:**

-   ✅ Redirect to generator page
-   ✅ Can select saved QRIS
-   ✅ Generate works

## 📊 Test Results Template

| Test                 | Status            | Notes |
| -------------------- | ----------------- | ----- |
| Upload Valid QRIS    | ⬜ Pass / ⬜ Fail |       |
| Upload Invalid Image | ⬜ Pass / ⬜ Fail |       |
| Manual Paste         | ⬜ Pass / ⬜ Fail |       |
| Edit Existing        | ⬜ Pass / ⬜ Fail |       |
| View Detail          | ⬜ Pass / ⬜ Fail |       |
| Generate Dynamic     | ⬜ Pass / ⬜ Fail |       |

## 🐛 Common Issues During Testing

### Issue: "Upload Error"

**Check:**

1. File size < 2MB?
2. Format PNG/JPG?
3. GD extension enabled?

**Fix:**

```bash
php -m | findstr -i gd
```

### Issue: "Failed to Read QRIS"

**Check:**

1. Is it actually a QR code?
2. Is QR code clear and not blurry?
3. Is QR code complete (not cropped)?

**Fix:**

-   Use better quality image
-   Crop closer to QR code
-   Use PNG format

### Issue: Form not auto-filling

**Check:**

1. Check browser console for JS errors
2. Check Laravel log for PHP errors
3. Clear cache

**Fix:**

```bash
php artisan view:clear
php artisan cache:clear
```

## 📝 Test Sample QRIS

For testing, you can use sample QRIS string:

```
00020101021226670016ID.CO.QRIS.WWW0118IDKU20230000000010303UMI51440014ID.CO.TELKOM.WWW02180000000000000000000303UMI520454995303360540410005802ID5914MERCHANT NAME6015JAKARTA SELATAN61051234062070703A016304XXXX
```

Generate QR code from this string at: https://www.qr-code-generator.com/

## ✅ Success Criteria

All tests should pass with:

-   ✅ No errors in log
-   ✅ Notifications working correctly
-   ✅ Auto-fill working
-   ✅ Manual fallback working
-   ✅ Image stored correctly
-   ✅ View modal working

## 🎯 Performance Benchmark

Expected processing time:

-   Upload & extract: < 1 second
-   Generate dynamic: < 0.5 second
-   View modal: Instant

If slower, check:

-   Server resources
-   Image size (should be < 2MB)
-   GD extension performance
