# Payment Notification: Settlement → Completed

Dokumentasi krusial setup notifikasi pembayaran Midtrans (GoPay, QRIS, Bank Transfer, dll.) dengan pemetaan otomatis status `settlement` menjadi `completed` di dashboard **Penjualan**.

---

## 1. Rute Notifikasi

Notifikasi dari Midtrans masuk melalui endpoint:

```
POST /api/midtrans/notification
```

Didefinisikan di `routes/api.php` baris 46:

```php
Route::post('/midtrans/notification', [WebhookController::class, 'handle'])
    ->name('api.midtrans.notification');
```

**Pastikan** endpoint ini didaftarkan di **Midtrans Dashboard** → Settings → Payment Notification URL.

---

## 2. Handler Notifikasi — `WebhookController`

**File:** `app/Http/Controllers/Api/WebhookController.php`

### Alur pemrosesan:

```
Midtrans POST notification
    │
    ├── 1. Log payload masuk (untuk debugging)
    │
    ├── 2. Verifikasi signature_key (SHA-512)
    │      formula: hash('sha512', order_id + status_code + gross_amount + server_key)
    │      → jika invalid: 403 Forbidden + log warning
    │
    ├── 3. Cari order dengan 3 strategi lookup:
    │      a. Exact match pada kolom no_order (format: 000001)
    │      b. Jika UUID → cari by primary key id
    │      c. Split by hyphen → backward compatibility (ORDER-123456)
    │      → jika tidak ditemukan: 404 + log warning
    │
    ├── 4. Update status berdasarkan transaction_status:
    │      settlement                       → completed
    │      capture + fraud_status accept    → completed
    │      expire                           → expired
    │      cancel / deny                    → cancelled
    │
    └── 5. Return response format Midtrans (wajib)
```

### Poin krusial:

| Item | Keterangan |
|---|---|
| Signature verification | Manual SHA-512 hash, **bukan** mengandalkan Midtrans SDK Notification class |
| Lookup multi-strategi | Mengakomodasi `no_order` numerik (`000001`), UUID, dan format `ORDER-timestamp` |
| `settlement` → `completed` | Dana sudah masuk merchant, transaksi final — langsung completed tanpa perlu approval manual |
| Logging | Setiap titik kritis tercatat di `storage/logs/laravel.log` dengan prefix `Midtrans webhook:` |

---

## 3. Checkout Flow — `CheckoutController`

**File:** `app/Http/Controllers/CheckoutController.php`

### Tiga perbaikan krusial:

#### a. `getTransactionStatus` menggunakan `order_id`, bukan UUID (baris 651)

```php
// SEBELUM (salah):
$status = $gateway->getTransactionStatus($request->input('gopay_transaction_id'));
//                                      ^^^^^^^^^^^^^^^^ ini UUID, Midtrans butuh order_id

// SESUDAH (benar):
$status = $gateway->getTransactionStatus($request->input('gopay_order_id'));
//                                      ^^^^^^^^^^^^^^ ORDER-1785670208833 → valid
```

#### b. Update `transaction_id` bersamaan dengan `no_order` di GoPay flow (baris 648)

```php
// SEBELUM:
$order->update(['no_order' => $request->input('gopay_order_id')]);
// transaction_id tetap nilai lama (000001) → polling gagal 404

// SESUDAH:
$order->update([
    'no_order' => $request->input('gopay_order_id'),
    'transaction_id' => $request->input('gopay_order_id'),
]);
```

#### c. `mapMidtransStatus` — `settlement` → `completed` (baris 475)

```php
private function mapMidtransStatus($transactionStatus)
{
    switch ($transactionStatus) {
        case 'capture':
        case 'settlement':
            return 'completed'; // sebelumnya: 'processing'
        case 'pending':
            return 'pending';
        case 'deny':
        case 'cancel':
        case 'expire':
            return 'failed';
        default:
            return 'pending';
    }
}
```

---

## 4. Sinkronisasi Status

Semua titik yang memetakan status Midtrans ke status order:

| Lokasi | `settlement` → | Digunakan oleh |
|---|---|---|
| `WebhookController::handle` | `completed` | Notifikasi dari Midtrans |
| `CheckoutController::mapMidtransStatus` | `completed` | Polling status & checkout flow |
| `CheckoutController::handleNotification` | `failed` | **DEPRECATED** — tidak digunakan |

### Status order yang tampil di dashboard (`OrderResource`):

| Status di DB | Label di Dashboard |
|---|---|
| `pending` | Pending |
| `processing` | Processing |
| `completed` | Completed |
| `cancelled` | Cancelled |

> **Catatan:** `processing` sekarang hanya tercapai lewat update manual admin. `settlement` dari Midtrans langsung menghasilkan `completed`.

---

## 5. Verifikasi & Debugging

### Cek log real-time:

```bash
tail -f storage/logs/laravel.log | grep -E "Midtrans webhook|Payment request|GoPay"
```

### Entri log yang diharapkan saat sukses:

```
INFO: Midtrans webhook received {...full payload...}
INFO: Midtrans webhook: Order found {order_uuid, no_order, current_status}
INFO: Midtrans webhook: Order status updated {old_status, new_status: completed}
```

### Entri log saat gagal:

| Log | Arti |
|---|---|
| `Invalid signature` | `server_key` di `.env` tidak cocok dengan Midtrans Dashboard |
| `Order not found` | `order_id` dari Midtrans tidak match dengan `no_order` / `id` di DB |
| `No status change needed` | Status order sudah `completed` sebelumnya |

---

## 6. Konfigurasi Environment

Pastikan di `.env`:

```env
MIDTRANS_SERVER_KEY=SB-Mid-server-xxxxxx
MIDTRANS_CLIENT_KEY=SB-Mid-client-xxxxxx
MIDTRANS_IS_PRODUCTION=false
MIDTRANS_MERCHANT_ID=Gxxxxxx
```

Untuk production, ubah `MIDTRANS_IS_PRODUCTION=true` dan gunakan server key production.

---

## 7. Ringkasan Perubahan File

| File | Perubahan |
|---|---|
| `app/Http/Controllers/Api/WebhookController.php` | +Logging, +3 strategi lookup, `settlement`→`completed` |
| `app/Http/Controllers/CheckoutController.php` baris 475 | `mapMidtransStatus`: `settlement`→`completed` |
| `app/Http/Controllers/CheckoutController.php` baris 648 | Update `transaction_id` bersamaan `no_order` |
| `app/Http/Controllers/CheckoutController.php` baris 651 | `gopay_transaction_id` → `gopay_order_id` |
